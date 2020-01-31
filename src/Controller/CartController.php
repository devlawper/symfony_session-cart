<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/panier", name="cart_index")
     * @param SessionInterface $session
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function index(SessionInterface $session, ProductRepository $productRepository)
    {
        // On récupère le contenu du panier dans la session ou on l'initialise à vide si non existant
        $panier = $session->get('panier', []);

        // On crée un panier avec des valeurs (vide pour le moment)...
        $panierWithData = [];

        // ... Et on le rempli !
        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'product' => $productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        $total = 0;

        foreach ($panierWithData as $item) {
            $totalItem = $item['product']->getPrice() * $item['quantity'];
            $total += $totalItem;
        }

        return $this->render('cart/index.html.twig', [
            'items' => $panierWithData,
            'total' => $total
        ]);
    }

    /**
     * @Route("/panier/add/{id}", name="cart_add")
     * @param $id
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    public function add($id, SessionInterface $session)
    {
        // On regarde dans la sessiion si un panier existe, sinon on l'initie avec un tableau vide
        $panier = $session->get('panier', []);

        // Si un produit avec le même id existe déjà alors on incrémente
        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        // Ajout du produit au panier de la session
        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }

    /**
     * @Route("/panier/remove/{id}", name="cart_remove")
     * @param $id
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    public function remove($id, SessionInterface $session)
    {
        // On récupère le contenu du panier dans la session ou on l'initialise à vide si non existant
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('cart_index');
    }
}
