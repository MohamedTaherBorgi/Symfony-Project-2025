<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_CLIENT')]
class OrderController extends AbstractController
{
    private const CART_COOKIE = 'eco_cart';

    private function getCartFromCookie(Request $request): array
    {
        $cart = $request->cookies->get(self::CART_COOKIE);
        return $cart ? json_decode($cart, true) : [];
    }

    private function clearCartCookie(Response $response): void
    {
        $cookie = Cookie::create(self::CART_COOKIE)
            ->withValue('')
            ->withExpires(time() - 3600)
            ->withPath('/')
            ->withHttpOnly(true);

        $response->headers->setCookie($cookie);
    }

    #[Route('/checkout', name: 'order_checkout')]
    public function checkout(
        Request                $request,
        ProductRepository      $productRepository,
        EntityManagerInterface $em
    ): Response
    {
        $cartData = $this->getCartFromCookie($request);

        if (empty($cartData)) {
            $this->addFlash('warning', 'Your cart is empty!');
            return $this->redirectToRoute('cart_index');
        }

        $user = $this->getUser();

        if ($request->isMethod('POST')) {

            $order = new Order();
            $order->setUser($user);

            $street = $request->request->get('street') ?? $user->getStreet();
            $city = $request->request->get('city') ?? $user->getCity();
            $postalCode = $request->request->get('postalCode') ?? $user->getPostalCode();
            $shippingAddress = $street . ', ' . $city . ' ' . $postalCode;

            $order->setShippingAddress($shippingAddress);
            $order->setPhone($request->request->get('phone') ?? $user->getTelephone());
            $order->setPaymentMethod($request->request->get('payment_method') ?? 'cash');
            $order->setStatus('pending');
            $order->setCreatedAt(new \DateTimeImmutable());

            $total = 0;

            foreach ($cartData as $productId => $quantity) {
                $product = $productRepository->find($productId);
                if (!$product) continue;

                $subtotal = $product->getPrix() * $quantity;

                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setQuantity($quantity);
                $orderItem->setUnitPrice($product->getPrix());
                $orderItem->setSubtotal($subtotal);
                $orderItem->setOrderRef($order);

                $em->persist($orderItem);

                $total += $subtotal;

                // Decrease stock
                $product->setStock($product->getStock() - $quantity);
                $em->persist($product);
            }

            $order->setTotalAmount($total);
            $em->persist($order);
            $em->flush();

            $response = $this->redirectToRoute('order_show', ['id' => $order->getId()]);
            $this->clearCartCookie($response);

            $this->addFlash('success', 'Order placed successfully! Order #' . $order->getOrderNumber());
            return $response;
        }

        // Show checkout page
        $cartItems = [];
        $total = 0;
        foreach ($cartData as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if (!$product) continue;

            $subtotal = $product->getPrix() * $quantity;
            $cartItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
            $total += $subtotal;
        }

        return $this->render('order/checkout.html.twig', [
            'cart' => [
                'cartItems' => $cartItems,
                'total' => $total,
                'totalItems' => array_sum(array_values($cartData)),
            ],
            'user' => $user,
        ]);
    }

    #[Route('/my-orders', name: 'order_my_orders')]
    public function myOrders(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $orders = $em->getRepository(Order::class)->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('order/my_orders.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/{id}', name: 'order_show')]
    public function show(Order $order): Response
    {
        // Ensure the current user owns the order
        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot view this order.');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }
}
