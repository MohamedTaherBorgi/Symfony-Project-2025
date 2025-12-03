<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('ROLE_USER')]
class CartController extends AbstractController
{
    #[Route('/', name: 'cart_index')]
    public function index(CartRepository $cartRepository): Response
    {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/add/{id}', name: 'cart_add')]
    public function add(
        Product $product,
        CartRepository $cartRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $em->persist($cart);
        }

        // Check if product already in cart
        $existingItem = null;
        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem) {
            // Increase quantity if already exists
            $existingItem->setQuantity($existingItem->getQuantity() + 1);
        } else {
            // Add new item to cart
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);
            $cart->addCartItem($cartItem);
            $em->persist($cartItem);
        }

        $cart->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        $this->addFlash('success', 'Product added to cart!');
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove/{id}', name: 'cart_remove')]
    public function remove(
        CartItem $cartItem,
        EntityManagerInterface $em
    ): Response {
        $cart = $cartItem->getCart();
        $cart->removeCartItem($cartItem);
        $em->remove($cartItem);
        $em->flush();

        $this->addFlash('success', 'Item removed from cart!');
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/update/{id}/{action}', name: 'cart_update')]
    public function update(
        CartItem $cartItem,
        string $action,
        EntityManagerInterface $em
    ): Response {
        if ($action === 'increase') {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        } elseif ($action === 'decrease' && $cartItem->getQuantity() > 1) {
            $cartItem->setQuantity($cartItem->getQuantity() - 1);
        }

        $cartItem->getCart()->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/clear', name: 'cart_clear')]
    public function clear(
        CartRepository $cartRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if ($cart) {
            foreach ($cart->getCartItems() as $item) {
                $em->remove($item);
            }
            $em->flush();
        }

        $this->addFlash('success', 'Cart cleared!');
        return $this->redirectToRoute('cart_index');
    }
}