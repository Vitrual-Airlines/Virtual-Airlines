<?php

namespace App\Controller\Register;

use App\Entity\User;
use App\Form\PasswordType;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function index(Request  $request ,  \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator ,  UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $users = $this->getDoctrine()->getRepository(User::class);
        if ($request->isMethod('POST')) {
           $email = $request->request->get('email');
           $name = $request->request->get('name');
           $lastname = $request->request->get('lastname');
           $password = $request->request->get('password');
            if(!empty($email && $name && $lastname && $password)){
               if (filter_var($email,FILTER_VALIDATE_EMAIL)){
                   $userresult = $users->findOneByEmail($email);
                   if ($userresult === null) {
                       $token = $tokenGenerator->generateToken();
                       try{
                           $user->setRememberToken($token);
                           $user->setEmail($email);
                           $user->setName($name);
                           $user->setLastname($lastname);
                           $user->setPassword($passwordEncoder->encodePassword($user,$password));
                           $user->setCreatedAt(New \DateTime());
                           $entityManager = $this->getDoctrine()->getManager();
                           $entityManager->persist($user);
                           $entityManager->flush();
                       } catch (\Exception $e) {
                           $this->addFlash('warning', $e->getMessage());
                           return $this->redirectToRoute('app');
                       }
                       $url = $this->generateUrl('app_confirme', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

                       // On génère l'e-mail
                       $message = (new \Swift_Message('Confirmation'))
                           ->setFrom('samsvr75@gmail.com')
                           ->setTo($user->getEmail())
                           ->setBody(
                               "Bonjour,<br><br>Une demande de creation de compte a été effectuée . Veuillez cliquer sur le lien suivant : " . $url,
                               'text/html'
                           );
                       // On envoie l'e-mail
                       $mailer->send($message);
                       return  $this->redirectToRoute('app');
                   }else{
                       $this->addFlash('warning', "l'utilisateur existe deja");
                   }
               }else{
                   $this->addFlash('warning', "l'email n est pas valide");
               }
           }else{
               $this->addFlash('warning', "tout les champs ne sont pas completer");
           }
        }
        return $this->render('register/register.html.twig');
    }

    /**
     * @Route("/confirme/{token}", name="app_confirme")
     */
    public function resetPassword(Request $request, string $token)
    {
        // On cherche un utilisateur avec le token donné
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['remember_token' => $token]);

        // Si l'utilisateur n'existe pas
        if ($user === null) {
            // On affiche une erreur
            $this->addFlash('danger', "L'utilisateur n existe pas ");
            return $this->redirectToRoute('app');
        }

               // On supprime le token
               $user->setRememberToken('');

               $user->setRoles(["ROLE_USER"]);
               // On stocke
               $entityManager = $this->getDoctrine()->getManager();
               $entityManager->persist($user);
               $entityManager->flush();

               // On crée le message flash
               $this->addFlash('message', 'le compte a bien eté confirmer ');

               // On redirige vers la page de connexion

            return $this->render('register/confirme.html.twig', [
                'token' => $token,
            ]);
    }
}
