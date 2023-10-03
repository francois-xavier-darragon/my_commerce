<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\OrderDetailsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/commande', name: 'order')]
    public function index(Cart $cart): Response
    {

        if(!$this->getUser()->getAddresses()->getValues())
        {
            return $this->redirectToRoute('account_address_add');
        }
         
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        return $this->render('order/index.html.twig',[
            'form' => $form->createView(),
            'cart' => $cart->getFull()
        ]);
    }

    
    #[Route('/commande/recapitulatif', name: 'order_recap', methods:'POST')]
    public function add(Cart $cart, OrderRepository $orderRepository, OrderDetailsRepository $OrderDetailsRepository, Request $request): Response
    {

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $date = new \DateTime();
            $carrier = $form->get('carrier')->getData();
            $delivery = $form->get('addresse')->getData();
            $deliveryContent = $delivery->getFirstname().' '.$delivery->getLastname();
            $deliveryContent .= '<br/>'.$delivery->getPhone();

            if($delivery->getCompany())
            {
                $deliveryContent .= '<br/>'.$delivery->getCompany();
            }

            $deliveryContent .= '<br/>'.$delivery->getAddress();
            $deliveryContent .= '<br/>'.$delivery->getPostal().' '.$delivery->getCity();
            $deliveryContent .= '<br/>'.$delivery->getCountry();
            

            //Enregistrer ma commande Order
            $order = new Order();
            $reference = $date->format('dmy').'-'.uniqid();
            $order->setReference($reference);
            $order->setUser($this->getUser());
            $order->setCreateAt($date);
            $order->setCarrierName($carrier->getName());
            // On converti les valeurs en centimes pour les stocker en bdd
            $order->setCarrierPrice($carrier->getPrice());
            $order->setDelivery($deliveryContent);
            $order->setIsPaid(0);

            $orderRepository->onPersit($order);
            
            //Enregistrer mes  produits OrderDetails
           
            foreach ($cart->getFull() as $product){
                $orderDetails = new OrderDetails(); 
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
                $OrderDetailsRepository->onPersit($orderDetails);
            }

            $orderRepository->onFlush(true);
            $OrderDetailsRepository->onFlush(true);

            return $this->render('order/add.html.twig',[
                'cart' => $cart->getFull(),
                'carrier' => $carrier,
                'delivry' => $deliveryContent,
                'reference' => $order->getReference()
            ]);

        }

        return $this->redirectToRoute('cart');
    }
}
