<?php
/**
 * Created by PhpStorm.
 * User: presley
 * Date: 10/09/2018
 */

namespace App\Controller;


use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CompaniesController extends FOSRestController
{
    private $companyRepository;
    private $em;
    private $validationErrors;

    public function __construct(CompanyRepository $companyRepository, EntityManagerInterface $em)
    {
        $this->companyRepository = $companyRepository;
        $this->em = $em;
    }

    //Liste toute les banques seulement et seulement si on est admin
    /**
     * @Rest\View(serializerGroups={"company"})
     * @SWG\Response(response=200,description="Liste toute les banques")
     * @SWG\Tag(name="company")
     */
    public function getCompaniesAction(){
        if($this->getUser()){
            if ($this->isGranted('ROLE_ADMIN')) {
                $companies = $this->companyRepository->findAll();
                return $this->view($companies, 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

    //Liste une banque par son id seulement si on est admin ou le propiétaire de la banque
    /**
     * @Rest\View(serializerGroups={"company"})
     * @SWG\Response(response=200,description="Liste une banque par son id")
     * @SWG\Tag(name="company")
     */
    public function getCompanyAction($id){
        if($this->getUser()) {
            $company = $this->companyRepository->find($id);
            if ($company->getMaster() == $this->getUser() or $this->isGranted('ROLE_ADMIN')) {
                return $this->view($company, 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

    //Creation d'une banque ! a la cration si un master est loggé la banque sera automatiquement attaché a lui sinon elle sera mise a null
    /**
     * @Rest\Post("/companies")
     * @ParamConverter("company", converter="fos_rest.request_body")
     * @Rest\View(serializerGroups={"company"})
     * @SWG\Response(response=200,description="Creation d'une banque")
     * @SWG\Tag(name="company")
     */
    public function postCompaniesAction(Company $company, ConstraintViolationListInterface $validationErrors){
        if($this->getUser()){
            $company->setMaster($this->getUser());
        }
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
            $this->em->persist($company);
            $this->em->flush();
            return $this->view($company, 201);
        }

    }

    //Modification d'une banque seulement si le master propriétaire est loggé ou si c'est un admin
    /**
     * @Rest\View(serializerGroups={"company"})
     * @SWG\Response(response=200,description="Modification d'une banque")
     * @SWG\Tag(name="company")
     */
    public function putCompanyAction(Request $request, $id, ValidatorInterface $validator){
        if($this->getUser()){
            $company = $this->companyRepository->find($id);

            $name = $request->get('name');
            $slogan = $request->get('slogan');
            $phoneNumber = $request->get('phoneNumber');
            $adress = $request->get('adress');
            $websiteUrl = $request->get('webSiteUrl');
            $pictureUrl = $request->get('pictureUrl');

            if ($company->getMaster() == $this->getUser() or $this->isGranted('ROLE_ADMIN')) {
                if (isset($name)) {
                    $company->setName($name);
                }
                if (isset($slogan)) {
                    $company->setSlogan($slogan);
                }
                if (isset($phoneNumber)) {
                    $company->setPhoneNumber($phoneNumber);
                }
                if (isset($adress)) {
                    $company->setAddress($adress);
                }
                if (isset($websiteUrl)) {
                    $company->setWebSiteUrl($websiteUrl);
                }
                if (isset($pictureUrl)) {
                    $company->setPictureUrl($pictureUrl);
                }

                $validationErrors = $validator->validate($company);
                $this->em->persist($company);
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
                return $this->view($company, 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);

    }

    //suppression d'une banque par son propriétaire ou par l'admin
    /**
     * @Rest\View(serializerGroups={"company"})
     * @SWG\Response(response=200,description="suppression d'une banque")
     * @SWG\Tag(name="company")
     */
    public function deleteCompanyAction($id){
        if($this->getUser()){

            $company = $this->companyRepository->find($id);

            if ($company->getMaster() == $this->getUser() or $this->isGranted('ROLE_ADMIN')) {

                $this->em->remove($company);
                $this->em->flush();
                return $this->view('suprimé', 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }
}