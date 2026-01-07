<?php

namespace App\Twig;

use App\Repository\CategoryRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class GlobalCategoriesExtension extends AbstractExtension implements GlobalsInterface
{
    private CategoryRepository $repo;

    public function __construct(CategoryRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getGlobals(): array
    {
        return [
            'categories' => $this->repo->findAll()
        ];
    }
}
