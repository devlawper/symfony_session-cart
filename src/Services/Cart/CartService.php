<?php


namespace App\Services\Cart;


use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    protected $session;
    protected $productRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    public function add(int $id)
    {
        // On regarde dans la sessiion si un panier existe, sinon on l'initie avec un tableau vide
        $panier = $this->session->get('panier', []);

        // Si un produit avec le même id existe déjà alors on incrémente
        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        // Ajout du produit au panier de la session
        $this->session->set('panier', $panier);
    }

    public function remove(int $id)
    {
        // On récupère le contenu du panier dans la session ou on l'initialise à vide si non existant
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $this->session->set('panier', $panier);
    }

    public function getFullCart() :array
    {
        // On récupère le contenu du panier dans la session ou on l'initialise à vide si non existant
        $panier = $this->session->get('panier', []);

        // On crée un panier avec des valeurs (vide pour le moment)...
        $panierWithData = [];

        // ... Et on le rempli !
        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        return $panierWithData;
    }

    public function getTotal() : float
    {
        $total = 0;

        foreach ($this->getFullCart() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }

        return $total;
    }
}