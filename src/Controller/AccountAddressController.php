<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountAddressController extends AbstractController
{
    #[Route('/compte/addresses', name: 'account_address')]
    public function index(): Response
    {
        return $this->render('account/address.html.twig',
        );
    }

    #[Route('/compte/ajouter-une-adresse', name: 'account_address_add')]
    public function add(AddressRepository $addressRepository, Cart $cart, Request $request): Response
    {
        $address = new Address();

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $address->setUser($this->getUser());

            $addressRepository->save($address, true);

            // if($cart->get()) {
            //     // return $this->redirectToRoute('order');
            // } else {
                return $this->redirectToRoute('account_address');

            // }
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    
    #[Route('/compte/modifier-une-adresse/{id}', name: 'account_address_edit')]
    public function edit(AddressRepository $addressRepository, Request $request, $id): Response
    {
        $address = $addressRepository->findOneById($id);

 
        if(!$address || $address->getUser() != $this->getUser() ) {
            return $this->redirectToRoute('account_address');
        }

        $form = $this->createForm(AddressType::class, $address);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $addressRepository->save($address, true);

            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/compte/supprimer-une-adresse/{id}', name: 'account_address_delete')]
    public function delete(AddressRepository $addressRepository, $id): Response
    {
        $address = $addressRepository->findOneById($id);

        if($address && $address->getUser() == $this->getUser() ) {

            $addressRepository->remove($address, true);
        }
            return $this->redirectToRoute('account_address');

    }
}
