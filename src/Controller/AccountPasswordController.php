<?php

namespace App\Controller;


use App\Form\ChangePasswordType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPasswordController extends AbstractController
{
    #[Route('/compte/password', name: 'account_password')]
    public function index(UserPasswordHasherInterface $encoder, UserRepository $userRepository, Request $request): Response
    {
        $notification = null;
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $oldPassword = $form->get('old_password')->getData();

            if($encoder->isPasswordValid($user, $oldPassword)){

                $newPassword = $form->get('new_password')->getData();
                $password = $encoder->hashPassword($user, $newPassword);

                $user->setPassword($password);
                $userRepository->save($user);
                $notification = "Votre mot de passe a bien été mis à jour";

            } else {
                $notification = "Votre mot de passe actuelle n'est pas le bon";
            }

        }
        return $this->render('account/password.html.twig',[
            'form' =>$form->createView(),
            'notification' => $notification,
        
        ]);
    }
}
