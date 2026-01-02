<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('ROLE_CLIENT')]
class CartController extends AbstractController
{
    private const COOKIE_NAME = 'eco_cart';
    private const COOKIE_LIFETIME = 3600*24*30; // 30 days

    private function getCart(Request $request): array
    {
        $cart = $request->cookies->get(self::COOKIE_NAME);
        return $cart ? json_decode($cart, true) : [];
    }

    private function saveCart(Response $response, array $cart): void
    {
        $cookie = Cookie::create(self::COOKIE_NAME)
            ->withValue(json_encode($cart))
            ->withExpires(time() + self::COOKIE_LIFETIME)
            ->withPath('/')
            ->withHttpOnly(true);

        $response->headers->setCookie($cookie);
    }

    #[Route('/', name: 'cart_index')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $cartData = $this->getCart($request);

        $cartItems = [];
        $total = 0;
        $totalItems = 0;

        foreach ($cartData as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if (!$product) continue;
            $subtotal = $product->getPrix() * $quantity;
            $cartItems[] = [
                'id' => $product->getId(),
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ];
            $total += $subtotal;
            $totalItems += $quantity;
        }

        // Build a "cart object" like your Twig expects
        $cart = (object)[
            'cartItems' => $cartItems,
            'total' => $total,
            'totalItems' => $totalItems
        ];

        return $this->render('cart/index.html.twig', [
            'cart' => $cart
        ]);
    }

    #[Route('/add/{id}', name: 'cart_add')]
    public function add(int $id, Request $request, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $cart = $this->getCart($request);
        $cart[$id] = ($cart[$id] ?? 0) + 1;

        $response = $this->redirectToRoute('cart_index');
        $this->saveCart($response, $cart);
        $this->addFlash('success', 'Product added to cart!');
        return $response;
    }

    #[Route('/remove/{id}', name: 'cart_remove')]
    public function remove(int $id, Request $request): Response
    {
        $cart = $this->getCart($request);
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        $response = $this->redirectToRoute('cart_index');
        $this->saveCart($response, $cart);
        $this->addFlash('success', 'Item removed from cart!');
        return $response;
    }

    #[Route('/update/{id}/{action}', name: 'cart_update')]
    public function update(int $id, string $action, Request $request): Response
    {
        $cart = $this->getCart($request);
        if (!isset($cart[$id])) {
            return $this->redirectToRoute('cart_index');
        }

        if ($action === 'increase') {
            $cart[$id]++;
        } elseif ($action === 'decrease') {
            $cart[$id] = max(1, $cart[$id] - 1);
        }

        $response = $this->redirectToRoute('cart_index');
        $this->saveCart($response, $cart);
        return $response;
    }

    #[Route('/clear', name: 'cart_clear')]
    public function clear(Request $request): Response
    {
        $response = $this->redirectToRoute('cart_index');
        $this->saveCart($response, []);
        $this->addFlash('success', 'Cart cleared!');
        return $response;
    }
}
