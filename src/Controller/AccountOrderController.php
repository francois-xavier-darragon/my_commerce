<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountOrderController extends AbstractController
{
    #[Route('/compte/mes-commandes', name: 'account_order')]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findSuccessOrders($this->getUser());
        
        return $this->render('account/order.html.twig',[
            'orders' => $orders
        ]);
    }

    #[Route('/compte/mes-commandes{reference}', name: 'account_order_show')]
    public function show($reference, OrderRepository $orderRepository, ProductRepository $productRepository): Response
    {

        $order = $orderRepository->findOneByReference($reference);

        
        $productName = '';
        $illustration = '';

        //TODO géré les erreurs
        foreach ($order->getOrderDetails() as $value) {
            $productName = $value->getProduct();  
        }

        $prodcut = $productRepository->findByName($productName);
       
        foreach($prodcut as $value ){
            $illustration = $value->getIllustration();
        }
    
        $pathName = "/uploads/$illustration";
      

        if(!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('account_order');
        }

        return $this->render('account/show.html.twig',[
            'order' => $order,
            'illustration' => $pathName
        ]);
    }
}
