<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    #[Route('/checkout', name: 'order_checkout')]
    public function checkout(
        Request $request,
        CartRepository $cartRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            $this->addFlash('warning', 'Your cart is empty!');
            return $this->redirectToRoute('cart_index');
        }

        if ($request->isMethod('POST')) {
            // Create new order
            $order = new Order();
            $order->setUser($user);
            $order->setShippingAddress($request->request->get('address') ?? $user->getAdresse());
            $order->setPhone($request->request->get('phone') ?? $user->getTelephone());
            $order->setPaymentMethod($request->request->get('payment_method'));
            
            $total = 0;

            // Convert cart items to order items
            foreach ($cart->getCartItems() as $cartItem) {
                $orderItem = new OrderItem();
                $orderItem->setProduct($cartItem->getProduct());
                $orderItem->setQuantity($cartItem->getQuantity());
                $orderItem->setUnitPrice($cartItem->getProduct()->getPrix());
                $subtotal = (float)$cartItem->getProduct()->getPrix() * $cartItem->getQuantity();
                $orderItem->setSubtotal((string)$subtotal);
                
                $order->addOrderItem($orderItem);
                $total += $subtotal;

                // Update product stock
                $product = $cartItem->getProduct();
                $newStock = $product->getStock() - $cartItem->getQuantity();
                if ($newStock < 0) {
                    $this->addFlash('error', 'Not enough stock for ' . $product->getNom());
                    return $this->redirectToRoute('cart_index');
                }
                $product->setStock($newStock);
            }

            $order->setTotalAmount((string)$total);
            
            // Set payment status based on method
            if ($request->request->get('payment_method') === 'card') {
                $order->setPaymentStatus('paid');
                $order->setStatus('processing');
            } else {
                $order->setPaymentStatus('pending');
                $order->setStatus('pending');
            }

            $em->persist($order);

            // Clear cart after order
            foreach ($cart->getCartItems() as $item) {
                $em->remove($item);
            }

            $em->flush();

            $this->addFlash('success', 'Order placed successfully! Order #' . $order->getOrderNumber());
            return $this->redirectToRoute('order_show', ['id' => $order->getId()]);
        }

        return $this->render('order/checkout.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/my-orders', name: 'order_my_orders')]
    public function myOrders(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $orders = $orderRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('order/my_orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/{id}', name: 'order_show', requirements: ['id' => '\d+'])]
    public function show(Order $order): Response
    {
        // Check if user owns this order
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot access this order.');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }
}