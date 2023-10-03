<?php

namespace App\Controller;

use App\Classe\Cart;
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
        }


        return $this->render('order_success/index.html.twig',[
            'order' => $order,
        ]);
    }
}
