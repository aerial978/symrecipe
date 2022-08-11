<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/user/edit/{id}', name: 'user.edit', methods: ['GET','POST'])]
    public function edit(User $user, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher, Request $request): Response
    {
        if(!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }

        if($this->getUser() !== $user) {
            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($user, $form->getData()->getPlainPassword())) {
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'User data was modified successfully !'
                );

            return $this->redirectToRoute('recipe.index');

            } else {

            $this->addFlash(
                'warning',
                'Invalid password !'
            );

            }
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
