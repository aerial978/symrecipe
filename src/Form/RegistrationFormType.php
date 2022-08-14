<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('fullName', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'minlength' => '2',
                'maxlength' => '50'
            ],
            'label' => 'Nom',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            'constraints' => [
                new Assert\Length(['min' => 2, 'max' => 50]),
                new Assert\NotBlank()
            ]
        ])
        ->add('pseudo', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'minlength' => '2',
                'maxlength' => '50'
            ],
            'required' => false,
            'label' => 'Pseudo (optional)',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            'constraints' => [
                new Assert\Length(['min' => 2, 'max' => 50])
            ]
        ])
        ->add('email', EmailType::class, [
            'attr' => [
                'class' => 'form-control',
                'minlength' => '2',
                'maxlength' => '180'
            ],
            'label' => 'Email address',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            'constraints' => [
                new Assert\Email(),
                new Assert\Length(['min' => 2, 'max' => 180]),
                new Assert\NotBlank()
            ]
        ])
        ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            // au lieu d'être placé directement sur l'objet,
            // ceci est lu et encodé dans le contrôleur
            'first_options'  => [
                'mapped' => false,
                'attr' => [
                'autocomplete' => 'new-password',
                'class' => 'form-control'
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
        ->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
            'constraints' => [
                new IsTrue([
                    'message' => 'You should agree to our terms.',
                ]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
