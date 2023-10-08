<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use App\Service\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'register')]
    public function index(UserRepository $userRepository, Request $request, UserPasswordHasherInterface $encoder, ParameterBagInterface $params): Response
    {

        $notification = null;

        $user = new User();

        $form =$this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isSubmitted()) {

            $searchEmail = $userRepository->findOneByEmail($user->getEmail());
            //Pour encoder un mot de passe, j'ai besoin d'injecter une dépendence. J'utilise alors UserPasswordHasherInterface
            //je déclare un variable $password, j'utilise mon injection de dépandence $encode et jutilise un fonction qui lui est associer hashPassword
            //Cette fonction a besoin de deux paramètre l'utilisateur($user) et le mot de passe qui lui est associer($user->getPassword)
            if(!$searchEmail){

                $password = $encoder->hashPassword($user, $user->getPassword());
                //Une fois que le mot de passe et encoder je le réenvoi à mon utilisateur
                $user->setPassword($password);

                //je savegarde tous en bdd
                $userRepository->save($user, true);
                $mail = new Mail($params);
                $content= "Bonjour ".$user->getFirstname()."<br/> Bienvenue Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
                $mail->send($user->getEmail(), $user->getFirstname(), "Bienvenue sur E-commerce", $content);

                $notification = "Votre inscription c'est correctemt déroulée. Vous pouvez dès à présent vous connecter à votre compte.";
            } else {
                $notification = "L'email que vous avez renseigné existe déjà.";
            }
            
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
    
}
