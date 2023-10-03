<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session/{reference}', name: 'stripe_create_session')]
    public function index(OrderRepository $orderRepository, ProductRepository $productRepository, $reference): JsonResponse
    {
        
        $productsForStripe = [];
        $YOUR_DOMAIN = 'http://127.0.0.1:8000';

        $order = $orderRepository->findOneByReference($reference);

        if (!$order) {
            new JsonResponse(['error' => 'order']);
        }

        
        foreach ($order->getOrderDetails()->getValues() as $product) {

            $productObject = $productRepository->findOneByName($product->getProduct());

            $productsForStripe[] = [
                'price_data' => [
                    'currency' => 'eur' ,
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getProduct(),
                        'images' => [$YOUR_DOMAIN."/uploads/".$productObject->getIllustration()],
                    ],
                ],
                'quantity' => $product->getQuantity(),
            ];
        }

        $productsForStripe[] = [
            'price_data' => [
                'currency' => 'eur' ,
                'unit_amount' => $order->getCarrierPrice(),
                'product_data' => [
                    'name' => $order->getCarrierName(),
                    'images' => [$YOUR_DOMAIN],
                ],
            ],
            'quantity' => 1,
        ];


        Stripe::setApikey('sk_test_51Nx251CUBslNEfANQIYKCdqANiRGTr74Ayg7ev9CafGPPmBvzzAUQ9JxS2Ez7BpPENCiftOuGPz8DNFxQIU3FpXx00KuPHrlW2');

        $checkout_session = Session::create([
            'customer_email' =>$this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [
                $productsForStripe
            ],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',

        ]);

        $order->setStripeSessionId($checkout_session->id);
        $orderRepository->onFlush(true);
       
        return new JsonResponse(['id' => $checkout_session->id]);
    }
}
