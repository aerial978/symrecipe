<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IngredientController extends AbstractController
{
    #[Route('/ingredient', name: 'ingredient.index', methods: ['GET'])]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    #[Route('/ingredient/new', name: 'ingredient.new', methods: ['GET','POST'])]
    public function new(EntityManagerInterface $manager, Request $request): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Your ingredient was created successfully !'
            );

            return $this->redirectToRoute('ingredient.index');
        }
        
        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/ingredient/edit/{id}', name: 'ingredient.edit', methods: ['GET','POST'])]
    public function edit(ingredient $ingredient, EntityManagerInterface $manager, Request $request): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Your ingredient was modified successfully !'
            );

            return $this->redirectToRoute('ingredient.index');
        }
        
        
        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createview()
        ]);     
    }

    #[Route('/ingredient/delete/{id}', name: 'ingredient.delete', methods: ['GET'])]
    public function delete(ingredient $ingredient, EntityManagerInterface $manager): Response
    {
        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash(
            'success',
            'Ingredient was delete successfully !'
        );
    
        return $this->redirectToRoute('ingredient.index');
    }
}
