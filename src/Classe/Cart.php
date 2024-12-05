<?php

namespace App\Classe;

 use Symfony\Component\HttpFoundation\RequestStack;

 class Cart {

     public function __construct(private RequestStack $requestStack)
     {

     }
     /*
      * add()
      * Fonction permettant l'ajout d'un produit au panier
      */
     public function add($product)
     {
         //appeler la session de symfony

         $cart = $this->getCart();

         // Ajouté un entity +1 à mon produit
         if (isset($cart[$product->getId()])) {
             $cart[$product->getId()] = [
                 'object' => $product,
                 'qty' => $cart[$product->getId()] ['qty'] + 1
             ];
         }else
         {
             $cart[$product->getId()] = [
                 'object' => $product,
                 'qty' => 1
             ];
         }
         //créer ma session cart
         $this->requestStack->getSession()->set('cart',$cart);

     }

     /*
      * decrease()
      * Fonction permettant la suppression  d'une quantity d'un produit au  panier
       */
     public function decrease($id)
     {
         $cart = $this->getCart();
         if ($cart[$id]['qty']>1) {
             $cart[$id]['qty'] = $cart[$id]['qty'] - 1;
         } else {
             unset($cart[$id]);
         }
         $this->requestStack->getSession()->set('cart', $cart);
     }
     /*
      * fullQuantity()
      * Fonction retournant le nombre totale des produit au panier
      */
     public function fullQuantity()
     {
         $cart = $this->getCart();
         $quantity = 0;

         if (!isset($cart)){
             return $quantity;
         }
         foreach ($cart as $product)
         {
            $quantity = $quantity + $product['qty'];
         }

         return $quantity;
     }
     /*
       * totalWT()
       * Fonction retournant le prix totale des produit au panier
       */
     public function totalWT()
     {
         $cart = $this->getCart();
         $price = 0;
         if (!isset($cart)){
             return $price;
         }
         foreach ($cart as $product) {
             $price = $price + ($product['object']->getPriceWT() * $product['qty']);
         }
         return $price;
     }
     /*
      * remove()
      * Fonction retournant de supprimer  totaleement le panier
       */
     public function remove()
     {
         return $this->requestStack->getSession()->remove('cart');
     }
     /*
           * getCart()
           * Fonction retournant le panier
            */
     public function getCart()
     {
         return $this->requestStack->getSession()->get('cart');
     }
}