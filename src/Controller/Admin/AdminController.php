<?php


namespace App\Controller\Admin;


use App\Roles\CheckRolesAdmin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    private $checkroles ;
    public function __construct(CheckRolesAdmin $checkRoles){
        $this->checkroles = $checkRoles ;
    }
    /**
     * @Route("/admin", name="app_admin_index")
     */
    public function index(): Response
    {
        $routectonroller = 'app_admin_index';
        $route = $this->checkroles->checkroles();

        if ($routectonroller != $route){
            $this->addFlash('danger', 'vous n avez pas le droit a acedder a cette page');
          return $this->redirectToRoute($route);
        }
        return $this->render('app/index.html.twig',[
            'route'=>$route
        ]);
    }
}