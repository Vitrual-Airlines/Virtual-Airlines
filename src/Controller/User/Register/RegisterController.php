<?php

namespace App\Controller\User\Register;

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

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function index(Request  $request ,  \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){

            $token = $tokenGenerator->generateToken();
            try{
                $user->setFptoken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('app');
            }
            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            // On génère l'e-mail
            $message = (new \Swift_Message('Mot de passe oublié'))
                ->setFrom('samsvr75@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    "Bonjour,<br><br>Une demande de réinitialisation de mot de passe a été effectuée pour le site Nouvelle-Techno.fr. Veuillez cliquer sur le lien suivant : " . $url,
                    'text/html'
                )
            ;

            // On envoie l'e-mail
            $mailer->send($message);

            return  $this->redirectToRoute('app');
        }
        return $this->render('user/register/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/reset_pass/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        // On cherche un utilisateur avec le token donné
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['fptoken' => $token]);

        // Si l'utilisateur n'existe pas
        if ($user === null) {
            // On affiche une erreur
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(PasswordType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            // On supprime le token
            $user->setResetToken('');

            // On chiffre le mot de passe
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));

            // On stocke
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // On crée le message flash
            $this->addFlash('message', 'Mot de passe mis à jour');

            // On redirige vers la page de connexion
            return $this->redirectToRoute('app_login');
        }else {
            // Si on n'a pas reçu les données, on affiche le formulaire
            return $this->render('user/register/confirme.html.twig', [
                'token' => $token,
                'form' => $form->createView()
            ]);
        }
    }
}
