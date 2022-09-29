<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/user/edit/{id}', name: 'user.edit', methods: ['GET','POST'])]
    #[Security("is_granted('ROLE_USER') and user === identifiedUser")]
    public function edit(User $identifiedUser, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $identifiedUser);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($identifiedUser, $form->getData()->getPlainPassword())) {
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

    #[Route('/user/edit-password/{id}', name: 'user.edit.password', methods: ['GET','POST'])]
    #[Security("is_granted('ROLE_USER') and user === identifiedUser")]
    public function editPassword(User $identifiedUser, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher, Request $request): Response
    {
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($identifiedUser, $form->getData()['plainPassword'])) {
                $identifiedUser->setUpdatedAt(new \DateTimeImmutable());
                $identifiedUser->setPlainPassword(
                    $form->getData()['newPassword']
                );

                $manager->persist($identifiedUser);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Password modified successfully !'
                );

                return $this->redirectToRoute('recipe.index');
            } else {
                $this->addFlash(
                    'warning',
                    'Invalid password !'
                );
            }
         }
        
        
        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
