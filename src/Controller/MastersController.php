<?php
/**
 * Created by PhpStorm.
 * User: presley
 * Date: 10/09/2018
 */
namespace App\Controller;

use App\Entity\Master;
use App\Repository\MasterRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MastersController extends FOSRestController
{
    private $masterRepository;
    private $em;
    private $validationErrors;

    public function __construct(MasterRepository $masterRepository, EntityManagerInterface $em)
    {
        $this->masterRepository = $masterRepository;
        $this->em = $em;
    }

    // Liste tous les master
    /**
     * @Rest\View(serializerGroups={"master"})
     * @SWG\Response(response=200,description=" Liste tous les master")
     * @SWG\Tag(name="master")
     */
    function getMastersAction(){
        if($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                $masters = $this->masterRepository->findAll();
                return $this->view($masters, 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

    //Affiche un master après avoir renseigné son ID et que l'utilisateur loggé soit un addmin ou le master avec le meme ID
    /**
     * @Rest\View(serializerGroups={"master"})
     * @SWG\Response(response=200,description="Affiche un master ")
     * @SWG\Tag(name="master")
     */
    public function getMasterAction ($id){

        if($this->getUser()) {
            $master = $this->masterRepository->find($id);
            if ($master == $this->getUser() or $this->isGranted('ROLE_ADMIN')) {
                return $this->view($master, 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

    //crée un master sans etre loggé (ce qui semble logique mdr)
    /**
     * @Rest\Post("/masters")
     * @ParamConverter("master", converter="fos_rest.request_body")
     * @Rest\View(serializerGroups={"master"})
     * @SWG\Response(response=200,description="crée un master")
     * @SWG\Tag(name="master")
     */
    public function postMastersAction(Master $master, ConstraintViolationListInterface $validationErrors){
        if ($validationErrors->count() > 0 ){
            $error = [];
            /** @var  ConstraintViolation $constraintViolation */
            foreach ($validationErrors as $constraintViolation) {
                $message = $constraintViolation->getMessage();
                $propertyPath = $constraintViolation->getPropertyPath();
                array_push($error, $message, $propertyPath);

            }
            return json_encode($error);

        }
        else{
            $this->em->persist($master);
            $this->em->flush();
            return $this->view($master, 201);
        }
    }

    //Modification d'un master en étant admin ou le master en question

    /**
     * @param $id
     * @Rest\View(serializerGroups={"master"})
     * @SWG\Response(response=200,description="Modification d'un master")
     * @SWG\Tag(name="master")
     * @return \FOS\RestBundle\View\View|string
     */
    public function putMasterAction(Request $request, int $id, ValidatorInterface $validator){
        if($this->getUser()){
            $master = $this->masterRepository->find($id);
            $firstname = $request->get('firstname');
            $lastname = $request->get('lastname');
            $email = $request->get('email');

            if ($this->getUser() === $master or $this->isGranted('ROLE_ADMIN')) {
                if (isset($firstname)) {
                    $master->setFirstname($firstname);
                }
                if (isset($lastname)) {
                    $master->setLastname($lastname);
                }
                if (isset($email)) {
                    $master->setEmail($email);
                }
                $validationErrors = $validator->validate($master);
                $this->em->persist($master);
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
                return $this->view($master, 200);
            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

    //suppression d'un master selon son ID en etant admin ou le master en question

    /**
     * @param $id
     * @Rest\View(serializerGroups={"master"})
     * @SWG\Response(response=200,description="suppression d'un master")
     * @SWG\Tag(name="master")
     * @return \FOS\RestBundle\View\View
     */
    public function deleteMasterAction($id)
    {
        if($this->getUser()){

            $master = $this->masterRepository->find($id);

            if ($this->getUser() === $master or $this->isGranted('ROLE_ADMIN')) {

                $company = $master->getCompany();
                if ($company) {
                    $company->setMaster(null);
                }
                $this->em->remove($master);
                $this->em->flush();
                return $this->view('Suprimé!', 200);

            }
            return $this->view('Interdit mon petit !', 403);
        }
        return $this->view('tu n\'est pas inscrit mec', 401);
    }

}