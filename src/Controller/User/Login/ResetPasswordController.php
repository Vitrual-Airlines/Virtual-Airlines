<?php


namespace App\Controller\User\Login;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    /**
     * @Route("/resetpassword", name="app_forgotten_password")
     */
    public function index(Request $request, \Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator
    ){
        $users = $this->getDoctrine()->getRepository(User::class);
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            if(!empty($email)){
                $user = $users->findOneByEmail($email);


                if ($user === null) {
                    $this->addFlash('danger', 'Cette adresse e-mail est inconnue');
                    return $this->redirectToRoute('app_login');
                }
                $token = $tokenGenerator->generateToken();
                try{
                    $user->setRememberToken($token);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                } catch (\Exception $e) {
                    $this->addFlash('warning', $e->getMessage());
                    return $this->redirectToRoute('app_login');
                }
                $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

                // On génère l'e-mail
                $message = (new \Swift_Message('Mot de passe oublié'))
                    ->setFrom('samsvr75@gmail.com')
                    ->setTo($user->getEmail())
                    ->setBody(
                        "Bonjour,<br><br>Une demande de réinitialisation de mot de passe a été effectuée pour le site Nouvelle-Techno.fr. Veuillez cliquer sur le lien suivant : " . $url,
                        'text/html'
                    );

                // On envoie l'e-mail
                $mailer->send($message);

                // On crée le message flash de confirmation
                 $this->addFlash('message', 'E-mail de réinitialisation du mot de passe envoyé !');

                // On redirige vers la page de login
                return $this->redirectToRoute('app_login');
            }
            $this->addFlash('warning', "l'email est incorrect");
        }
        return $this->render('user/login/resetpassword.html.twig');
    }



    /**
     * @Route("/reset_pass/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        // On cherche un utilisateur avec le token donné
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['remember_token' => $token]);

        // Si l'utilisateur n'existe pas
        if ($user === null) {
            // On affiche une erreur
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('app_login');
        }

        // Si le formulaire est envoyé en méthode post
        if ($request->isMethod('POST')) {
            // On supprime le token
            $user->setRememberToken('');

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
            return $this->render('user/register/confirme.html.twig', ['token' => $token]);
        }

    }
}