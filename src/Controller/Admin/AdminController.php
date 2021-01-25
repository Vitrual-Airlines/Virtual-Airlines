<?php


namespace App\Controller\Admin;




use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdminController extends AbstractController
{


    /**
     * @Route("/admin", name="app_admin_index")
     */
    public function index(): Response
    {
        return $this->render('app/index.html.twig');





    }
        /**
         * @Route("/admin/bla", name="app_admin_bla")
         */
        public function delete(): Response
    {
        return $this->render('register/register.html.twig',[

        ]);


    }
}