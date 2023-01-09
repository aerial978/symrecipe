<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{
    #[Route('/recipe', name: 'recipe.index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recipe/public', name: 'recipe.index.public', methods: ['GET'])]
    public function indexPublic(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $recipes = $paginator->paginate(
            $repository->findPublicRecipe(null),
            $request->query->getInt('page', 1),
            10
        );
        
        return $this->render('pages/recipe/index_public.html.twig',[
            'recipes' => $recipes
        ]);
    }

    // Allow us to see a recipe if this one is public
    #[Route('/recipe/{id}', name: 'recipe.show', methods: ['GET','POST'])]
    #[Security("is_granted('ROLE_USER') and recipe.getIsPublic() === true")]
    public function show(Recipe $recipe, MarkRepository $markRepository, EntityManagerInterface $manager, Request $request) : Response
    {
        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);

            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if(!$existingMark) {
                $manager->persist($mark);
            } else {
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
            }

            $manager->flush();

            $this->addFlash(
                'success',
                'Your rating has been taken into account !'
            );

            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
           
        }
        
        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
    }

    #[Route('/recipe/new', name: 'recipe.new', methods: ['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(EntityManagerInterface $manager, Request $request): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Your recipe was created successfully !'
            );

            return $this->redirectToRoute('recipe.index');
        }
        
        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/recipe/edit/{id}', name: 'recipe.edit', methods: ['GET','POST'])]
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
    public function edit(recipe $recipe, EntityManagerInterface $manager, Request $request): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Your recipe was modified successfully !'
            );

            return $this->redirectToRoute('recipe.index');
        }
        
        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createview()
        ]);     
    }

    #[Route('/recipe/delete/{id}', name: 'recipe.delete', methods: ['GET'])]
    public function delete(recipe $recipe, EntityManagerInterface $manager): Response
    {
        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash(
            'success',
            'Recipe was delete successfully !'
        );
    
        return $this->redirectToRoute('recipe.index');
    }
}
