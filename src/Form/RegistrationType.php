<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your first name']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'First name must be at least {{ limit }} characters',
                        'maxMessage' => 'First name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your first name'
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your last name']),
                    new Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Last name must be at least {{ limit }} characters',
                        'maxMessage' => 'Last name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your last name'
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter an email address']),
                    new Email(['message' => 'Please enter a valid email address']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'your.email@example.com'
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Password',
                    'constraints' => [
                        new NotBlank(['message' => 'Please enter a password']),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Password must be at least {{ limit }} characters',
                            'max' => 4096,
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                            'message' => 'Password must include at least one uppercase, one lowercase, one number, and one symbol',
                        ]),
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Enter your password'
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirm Password',
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Confirm your password'
                    ],
                ],
                'invalid_message' => 'The password fields must match.',
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Phone',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your phone number']),
                    new Regex([
                        'pattern' => '/^[0-9]{8}$/',
                        'message' => 'Invalid phone number (must be 8 digits)',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '12345678',
                ],
            ])
            ->add('street', TextType::class, [
                'label' => 'Street',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'Please enter your street'])],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'Please enter your city'])],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal Code',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your postal code']),
                    new Regex(['pattern' => '/^\d{4,5}$/', 'message' => 'Postal code must be 4-5 digits'])
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'I agree to the terms and conditions',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You must agree to the terms and conditions.',
                    ]),
                ],
                'attr' => ['class' => 'form-check-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
