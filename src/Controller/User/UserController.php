<?php


namespace App\Controller\User;



use App\Roles\CheckRolesUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $checkroles ;
    public function __construct(CheckRolesUser $checkRoles){
        $this->checkroles = $checkRoles ;
    }
    /**
     * @Route("/user", name="app_user_index")
     */
    public function index(): Response
    {
        $routectonroller = 'app_user_index';
        $route = $this->checkroles->checkroles();
        if ($routectonroller != $route){
            $this->addFlash('danger', 'vous n avez pas le droit a acedder a cette page');
            return $this->redirectToRoute($route);
        }
        return $this->render('app/index.html.twig');
    }
}