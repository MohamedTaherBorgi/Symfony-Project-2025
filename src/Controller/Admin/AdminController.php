<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    /* =============================
       DASHBOARD
    ==============================*/
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(
        OrderRepository    $orderRepository,
        ProductRepository  $productRepository,
        UserRepository     $userRepository,
        CategoryRepository $categoryRepository
    ): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'totalOrders' => $orderRepository->count([]),
            'totalProducts' => $productRepository->count([]),
            'totalUsers' => $userRepository->count([]),
            'totalCategories' => $categoryRepository->count([]),
            'recentOrders' => $orderRepository->findBy([], ['createdAt' => 'DESC'], 10),
        ]);
    }


    /* =============================
       PRODUCTS
    ==============================*/
    #[Route('/products', name: 'admin_products')]
    public function products(ProductRepository $productRepository): Response
    {
        return $this->render('admin/products/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/products/new', name: 'admin_product_new')]
    public function newProduct(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $filename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                $newFilename = $filename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/products', $newFilename);
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Could not upload image');
                }
            }

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('admin_products');
        }

        return $this->render('admin/products/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/products/{id}/edit', name: 'admin_product_edit')]
    public function editProduct(Product $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $filename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                $newFilename = $filename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/products', $newFilename);
                    $product->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Could not upload image');
                }
            }

            $em->flush();
            return $this->redirectToRoute('admin_products');
        }

        return $this->render('admin/products/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/products/{id}/delete', name: 'admin_product_delete', methods: ['POST'])]
    public function deleteProduct(Product $product, EntityManagerInterface $em): Response
    {
        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('admin_products');
    }


    /* =============================
       CATEGORIES (FIXED)
    ==============================*/
    #[Route('/categories', name: 'admin_categories')]
    public function categories(CategoryRepository $repo): Response
    {
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $repo->findAll(),
        ]);
    }

    #[Route('/categories/new', name: 'admin_category_new')]
    public function newCategory(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categories/{id}/edit', name: 'admin_category_edit')]
    public function editCategory(Category $category, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    #[Route('/categories/{id}/delete', name: 'admin_category_delete', methods: ['POST'])]
    public function deleteCategory(Category $category, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete_category_' . $category->getId(), $request->get('_token'))) {
            $em->remove($category);
            $em->flush();
        }

        return $this->redirectToRoute('admin_categories');
    }


    /* =============================
       ORDERS
    ==============================*/
    #[Route('/orders', name: 'admin_orders')]
    public function orders(OrderRepository $orderRepository): Response
    {
        return $this->render('admin/orders/index.html.twig', [
            'orders' => $orderRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/orders/{id}', name: 'admin_order_show')]
    public function showOrder(Order $order): Response
    {
        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/orders/{id}/update-status', name: 'admin_order_update_status', methods: ['POST'])]
    public function updateOrderStatus(Order $order, Request $request, EntityManagerInterface $em): Response
    {
        $order->setStatus($request->request->get('status'));

        if ($order->getStatus() === 'delivered') {
            $order->setDeliveredAt(new \DateTimeImmutable());
        }

        $em->flush();

        $this->addFlash('success', 'Order status updated successfully!');

        // ✅ Redirect to admin orders index
        return $this->redirectToRoute('admin_orders');
    }


    /* =============================
       USERS
    ==============================*/
    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepository, Request $request, EntityManagerInterface $em): Response
    {
        $users = $userRepository->findAll();
        $currentUserId = $this->getUser()->getId(); // logged-in admin

        // Handle role update
        if ($request->isMethod('POST')) {
            foreach ($users as $user) {
                // Skip logged-in admin
                if ($user->getId() === $currentUserId) {
                    continue;
                }

                $role = $request->request->get('roles_' . $user->getId());

                if ($role === 'ROLE_ADMIN') {
                    $user->setRoles(['ROLE_ADMIN']);
                } else {
                    $user->setRoles(['ROLE_CLIENT']);
                }

                $em->persist($user);
            }
            $em->flush();

            $this->addFlash('success', 'User roles updated successfully.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'currentUserId' => $currentUserId,
        ]);
    }
}
