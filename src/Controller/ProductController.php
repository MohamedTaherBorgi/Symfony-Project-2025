<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ProductType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/new', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid() . '.' . $image->guessExtension();
                $image->move($this->getParameter('product_images'), $filename);
                $product->setImage($filename);
            }

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('admin/products/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid() . '.' . $image->guessExtension();
                $image->move($this->getParameter('product_images'), $filename);
                $product->setImage($filename);
            }

            $em->flush();
            return $this->redirectToRoute('product_index');
        }

        return $this->render('admin/products/edit.html.twig', [
            'form' => $form,
            'product' => $product
        ]);
    }

    #[Route('/{id}/delete', name: 'product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete_product_' . $product->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException("Invalid CSRF token");
        }

        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('product_index');
    }

    #[Route('/', name: 'product_index')]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/category/{id}', name: 'product_by_category', requirements: ['id' => '\d+'])]
    public function byCategory(int $id, CategoryRepository $catRepo, ProductRepository $productRepo): Response
    {
        $category = $catRepo->find($id);

        if (!$category) {
            throw $this->createNotFoundException("Category not found");
        }

        return $this->render('product/index.html.twig', [
            'products' => $productRepo->findBy(['category' => $category]),
            'category' => $category
        ]);
    }

    #[Route('/{id}', name: 'product_show', requirements: ['id' => '\d+'])]
    public function show(Product $product, ProductRepository $productRepository): Response
    {
        // Load related products (same category, excluding current product)
        $related = $productRepository->findBy(
            ['category' => $product->getCategory()],
            ['id' => 'DESC']
        );

        // Remove the current product if it appears in the list
        $related = array_filter($related, fn($p) => $p->getId() !== $product->getId());

        // Limit to 4 related products
        $related = array_slice($related, 0, 4);

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}
