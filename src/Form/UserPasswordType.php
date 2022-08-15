<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;


class UserPasswordtype extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            // au lieu d'être placé directement sur l'objet,
            // ceci est lu et encodé dans le contrôleur
            'first_options'  => [
                'mapped' => false,
                'attr' => [
                'autocomplete' => 'new-password',
                'class' => 'form-control',
                'placeholder' => 'at least 6 characters'
                ],
                'label' => 'Password',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // longueur max autorisée par Symfony pour des raisons de sécurité
                        'max' => 4096,
                    ]),
                ],
            ],
            'second_options' => [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Confirm Password',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'invalid_message' => 'The password fields must match !'
            ]
        ])
        ->add('newPassword', PasswordType::class, [
            'attr' => [
                'class' => 'form-control',
            ],
            'label' => 'New Password',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            'constraints' => [
                new Assert\NotBlank()
            ]
        ])

        ->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary mt-4'
            ],
            'label' => 'Submit'
        ]);
    }
}