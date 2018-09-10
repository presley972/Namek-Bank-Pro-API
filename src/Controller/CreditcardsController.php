<?php
/**
 * Created by PhpStorm.
 * User: presley
 * Date: 10/09/2018
 */

namespace App\Controller;

use App\Entity\Creditcard;
use App\Repository\CompanyRepository;
use App\Repository\CreditcardRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CreditcardsController extends FOSRestController
{
    private $creditcardRepository;
    private $companyRepository;
    private $em;
    private $validationErrors;

    public function __construct(CreditcardRepository $creditcardRepository, CompanyRepository $companyRepository,
                                EntityManagerInterface $em)
    {
        $this->companyRepository = $companyRepository;
        $this->creditcardRepository = $creditcardRepository;
        $this->em = $em;
    }

    //Liste toute les cartes de credit si loggé en admin
    /**
     * @Rest\View(serializerGroups={"creditcard"})
     * @SWG\Response(response=200,description="Liste toute les cartes de credit"
     * )
     * @SWG\Tag(name="creditcard")
     */
    public function getCreditcardsAction(){
        if($this->getUser()){
            if ($this->isGranted('ROLE_ADMIN')) {
                $creditcards = $this->creditcardRepository->findAll();
                return $this->view($creditcards, 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

    //Liste une carte de crédit via son id si le bon master est loggé ou si il est admin (sa remonte assez haut dans la relation :D)
    /**
     * @Rest\View(serializerGroups={"creditcard"})
     * @SWG\Response(response=200,description="Liste une carte de crédit")
     * @SWG\Tag(name="creditcard")
     */
    public function getCreditcardAction($id){
        if($this->getUser()) {
            $creditcard = $this->creditcardRepository->find($id);
            if ($creditcard->getCompany()->getMaster() == $this->getUser() or $this->isGranted('ROLE_ADMIN')) {
                return $this->view($creditcard, 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

    /**
     * @Rest\View(serializerGroups={"creditcard"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns the creditcard list of a company based on it Id"
     * )
     * @SWG\Tag(name="creditcard")
     */
    public function getCompanyCreditcardsAction(int $id){
        if($this->getUser()) {
            $company = $this->companyRepository->find($id);

            if ($this->getUser() === $company->getMaster() or in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                $creditcard = $company->getCreditcards();
                return $this->view($creditcard, 200);
            }
            return $this->view('Vous n\'avez pas les droits', 403);
        }
        return $this->view('Non logué', 401);
    }


    //Creation d'une carte de crédit qui set le master et la company automatiquement via les donné du master loggé
    /**
     * @Rest\Post("/creditcards")
     * @ParamConverter("creditcard", converter="fos_rest.request_body")
     * @Rest\View(serializerGroups={"credicard"})
     * @SWG\Response(response=200,description="Creation d'une carte de crédit")
     * @SWG\Tag(name="creditcard")
     */
    public function postCreditcardsAction(Creditcard $creditcard, ConstraintViolationListInterface $validationErrors){
        if($this->getUser()){
            $master = $this->getUser();
            $creditcard->setCompany($master->getCompany());

            if ($validationErrors->count() > 0) {
                $error = [];
                /** @var  ConstraintViolation $constraintViolation */
                foreach ($validationErrors as $constraintViolation) {
                    $message = $constraintViolation->getMessage();
                    $propertyPath = $constraintViolation->getPropertyPath();
                    array_push($error, $message, $propertyPath);
                }
                return json_encode($error);
            } else {
                $this->em->persist($creditcard);
                $this->em->flush();
                return $this->view($creditcard, 201);
            }
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

    //Modifie une carte de crédit comme d'hab si le master propriétaire est loggé ou l'admin

    /**
     * @param $id
     * @Rest\View(serializerGroups={"creditcard"})
     * @SWG\Response(response=200, description="Modifie une carte de crédit ")
     * @SWG\Tag(name="creditcard")
     * @return \FOS\RestBundle\View\View|string
     */
    public function putCreditcardAction(Request $request, $id, ValidatorInterface $validator){
        if($this->getUser()){
            $creditcard = $this->creditcardRepository->find($id);
            $name = $request->get('name');
            $creditcardNumber = $request->get('creditcardNumber');
            $creditcardType = $request->get('creditcardType');

            if ($creditcard->getCompany()->getMaster() == $this->getUser() or $this->isGranted('ROLE_ADMIN')) {
                if (isset($name)) {
                    $creditcard->setName($name);
                }
                if (isset($creditcardNumber)) {
                    $creditcard->setCreditCardNumber($creditcardNumber);
                }
                if (isset($creditcardType)) {
                    $creditcard->setCreditCardType($creditcardType);
                }

                $validationErrors = $validator->validate($creditcard);
                $this->em->persist($creditcard);
                $error = [];
                foreach ($validationErrors as $constraintViolation) {
                    $message = $constraintViolation->getMessage();
                    $propertyPath = $constraintViolation->getPropertyPath();
                    array_push($error, $message, $propertyPath);
                }
                if (sizeof($error) > 0) {
                    return json_encode($error);
                }
                $this->em->flush();
                return $this->view($creditcard, 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

    //supprimer une carte de crédit via son Id

    /**
     * @param $id
     * @Rest\View(serializerGroups={"creditcard"})
     * @SWG\Response(response=200, description="supprimer une carte de crédit via son Id")
     * @SWG\Tag(name="creditcard")
     * @return \FOS\RestBundle\View\View
     */
    public function deleteCreditcardAction($id){
        if($this->getUser()){
            $creditcard = $this->creditcardRepository->find($id);

            if ($creditcard->getCompany()->getMaster() == $this->getUser() or $this->isGranted('ROLE_ADMIN')) {

                $this->em->remove($creditcard);
                $this->em->flush();
                //Bug des tests lors du renvoi de code 204 => envoi 204
                return $this->view('suprimé', 200);

            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }
}