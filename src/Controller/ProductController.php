<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
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
