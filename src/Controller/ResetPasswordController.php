<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use App\Repository\ResetPasswordRepository;
use App\Repository\UserRepository;
use App\Service\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class ResetPasswordController extends AbstractController
{
    #[Route('/mot-de-passe-oublié', name: 'reset_password')]
    public function index(Request $request, UserRepository $userRepository, ResetPasswordRepository $resetPasswordRepository, ParameterBagInterface $params): Response
    {
        if($this->getUser()){
           return $this->redirectToRoute('home');
        }

        if($request->get('email')){
           $user = $userRepository->findOneByEmail($request->get('email'));
            if($user) {
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user);
                $resetPassword->setToken(uniqid());
                $resetPassword->setCreatedAt(new \DateTime());
                $resetPasswordRepository->save($resetPassword, true);

                $url = $this->generateUrl('update_password', [
                    'token' =>$resetPassword->getToken()
                ]);

                $content = " Bonjour ".$user->getFirstName(). "<br/> Vous avez demandé de réinitialier votre mot de passe" ;
                $content .= " Merci de bien voulir cliquer sur le lien suivant pour <a href='". $url ."'>mettre à jour votre mot de passe </a>";
            
                $mail = new Mail($params);
                $mail->send($user->getEmail(), $user->getFirstName().' '.$user->getLastName(), 'Réinitialisez votre mot de passe', $content);
                
                $this->addFlash('notice','Vous allez recevoir un email de réinitialisation.');
            } else {
                $this->addFlash('notice','Cette adresse email est inconnu.');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    #[Route('/mot-de-passe-oublié/{token}', name: 'update_password')]
    public function update($token, ResetPasswordRepository $ResetPasswordRepository, UserRepository $userRepository, UserPasswordHasherInterface $encoder, Request $request)
    {
        $resetPassword = $ResetPasswordRepository->findOneByToken($token);

        if(!$resetPassword){
            return $this->redirectToRoute('reset_password');
        }

        $now = new \DateTime();
        if($now > $resetPassword->getCreatedAt()->modify('+ 3 hour')){
            $this->addFlash('notice','Votre demande de mot de passe à expiré. Merci de l renouveller');
            return $this->redirectToRoute('reset_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        
        if($form->isSubmitted() && $form->isValid()){
           $newPassword = $form->get('new_password')->getData();
           $password = $encoder->hashPassword($resetPassword->getUser(),$newPassword);
           $resetPassword->getUser()->setPassword($password); 
           $userRepository->save($resetPassword->getUser(), true);

           $this->addFlash('notice','votre mot de passe a bien été mis à jour.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form,
        ]);
      
    }
}
