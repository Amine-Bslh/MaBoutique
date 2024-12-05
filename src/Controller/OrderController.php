<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Form\OrderType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    /*
     * 1 er etape du tunnel d'achat
     * Choix du transporteur et de l'adresse de livraison et du transporteur
     */
    #[Route('/commande/livraison', name: 'app_order')]
    public function index(): Response
    {
        $addresses = $this->getUser()->getAddresses();

        if (count($addresses) == 0) {
            return $this->redirectToRoute('app_account_addresses_form');
        }
        $form = $this->createForm(OrderType::class, null, [
            'addresses' => $addresses,
            'action' => $this->generateUrl('app_order_summary')
        ]);
        return $this->render('order/index.html.twig', [
            'deliveryForm' => $form->createView(),

        ]);
    }

    /*
     * 2em  etape du tunnel d'achat
     * recap de la commande  de l'utilisateur
     * Insertion en base de donnée
     * Prépation du paiement vers Stripe
     */
    #[Route('/commande/recap', name: 'app_order_summary')]
    public function add(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        $products = $cart->getCart();
        if ($request->getMethod() != 'POST') {
            return $this->redirectToRoute('app_cart');
        }

        $form = $this->createForm(OrderType::class, null, [
            'addresses' => $this->getUser()->getAddresses(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // création de la chaine adresse
            $addressObj = $form->get('addresses')->getData();
            $adresse = $addressObj->getFirstname().' '.$addressObj->getLastname().'<br/>';
            $adresse .=$addressObj->getAddress().'<br/>';
            $adresse .=$addressObj->getPostal().' '. $addressObj->getCity().'<br/>';
            $adresse .=$addressObj->getCountry().'<br/>';
            $adresse .=$addressObj->getPhone();



            $order = new Order();
            $order->setCreatedAt(new \DateTime());
            $order->setState(1);
            $order->setCarrierName($form->get('carriers')->getData()->getName());
            $order->setCarrierPrice($form->get('carriers')->getData()->getPrice());
            $order->setDelivery($adresse);

            foreach ($products as $product)
            {

                $orderDetail = new OrderDetail();
                $orderDetail->setProductName($product['object']->getName());
                $orderDetail->setProductIllustration($product['object']->getIllustration());
                $orderDetail->setProductPrice($product['object']->getPrice());
                $orderDetail->setProductTva($product['object']->getTva());
                $orderDetail->setProductQuantity($product['qty']);
                $order->addOrderDetail($orderDetail);
            }
            $entityManager->persist($order);
            $entityManager->flush();

        }
        return $this->render('order/summary.html.twig', [
            'choices' => $form->getData(),
            'cart' => $products,
            'totalWT' => $cart->totalWT()
        ]);
    }
}
