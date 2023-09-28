<?php

namespace App\Controller;

Use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'register')]
    public function index(UserRepository $userRepository, Request $request, UserPasswordHasherInterface $encoder): Response
    {

        $notification = null;

        $user = new User();

        $form =$this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isSubmitted()) {

            //Pour encoder un mot de passe, j'ai besoin d'injecter une dépendence. J'utilise alors UserPasswordHasherInterface
            //je déclare un variable $password, j'utilise mon injection de dépandence $encode et jutilise un fonction qui lui est associer hashPassword
            //Cette fonction a besoin de deux paramètre l'utilisateur($user) et le mot de passe qui lui est associer($user->getPassword)
            $password = $encoder->hashPassword($user, $user->getPassword());


            //Une fois que le mot de passe et encoder je le réenvoi à mon utilisateur
            $user->setPassword($password);

            //je savegarde tous en bdd
            $userRepository->save($user);
        }

        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
    
}
