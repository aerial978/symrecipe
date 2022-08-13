<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control',
                        'minlength' => '2',
                        'maxlength' => '180'
                    ],
                    'label' => 'New password',
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
                            // max length allowed by Symfony for security reasons
                            'max' => 180,
                        ]),
                    ],
                ],
                'second_options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control',
                    ],
                    'label' => 'Repeat Password',
                    'label_attr' => [
                        'class' => 'form-label mt-4'
                    ],
                ],
                'invalid_message' => 'The password fields must match.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
