<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home.index')]
    public function index(RecipeRepository $recipeRepository): Response
    {   
        $recipes = $recipeRepository->findPublicRecipe(3);
        
        return $this->render('pages/home.html.twig', [
            'recipes' => $recipes
        ]);
    }
}
