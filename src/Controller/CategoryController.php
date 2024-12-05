<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/categorie/{slug}', name: 'app_category')]
    public function index($slug, CategoryRepository $categoryRepository): Response
    {
        /* CategoryRepository
            1. J'ouvre une connexion avec ma BDD
            2. Connecte toi à la table qui s'appelle Category
            3.Fais une action en base de donnée
         */
        $category = $categoryRepository->findOneBySlug($slug);
        if (!$category)
        {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('category/index.html.twig', [
            'category'=>$category
        ]);
    }
}
