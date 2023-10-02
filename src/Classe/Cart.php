<?php

namespace App\Classe;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;


class Cart
{

    private $session;
    private $productRepository;

    public function __construct(ProductRepository $productRepository, RequestStack $session)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    public function get()
    {
        return $this->session->getSession()->get('cart');
    }

    public function getFull()
    {
        $cartComplet = [];
        if($this->get()) {
            foreach ($this->get() as $id => $quantity) {
                $productObject = $this->productRepository->findOneById($id);

                if(!$productObject) {
                    $this->delete($id);
                    continue;
                }

                $cartComplet[] = [
                    'product' => $productObject,
                    'quantity' => $quantity,
                ];
            }
        }

        return $cartComplet;
    }

    public function add($id)
    {
        $cart = $this->session->getSession()->get('cart', []);

        if(!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->getSession()->set('cart', $cart);
    }

    public function decrease($id)
    {
        $cart = $this->session->getSession()->get('cart', []);

        if($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }
        return $this->session->getSession()->set('cart', $cart);
    }


    public function remove()
    {
        return $this->session->getSession()->remove('cart');
    }

    public function delete($id)
    {
        $cart = $this->session->getSession()->get('cart', []);

        unset($cart[$id]);

        return $this->session->getSession()->set('cart', $cart);
    }

}
