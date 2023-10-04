<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderValidateController extends AbstractController
{
    #[Route('/commande/merci/{stripeSessionId}', name: 'order_validate')]
    public function index(OrderRepository $orderRepository, Cart $cart, $stripeSessionId): Response
    {

        $order = $orderRepository->findOneByStripeSessionId($stripeSessionId);


        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if(!$order->getIsPaid()) {
            $cart->remove();

            //Modifier le status isPaid de la commande
            $order->setIsPaid(1);
            $orderRepository->onFlush(true);

            //TODO Enoyer un un email de confirmation de payment
            $mail = new Mail();
            $content= "Bonjour ".$order->getUser()->getFirstname()."<br/> Merci Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), "Votre commande sue E-commerce est bien validé", $content);
        }


        return $this->render('order_success/index.html.twig',[
            'order' => $order,
        ]);
    }
}
