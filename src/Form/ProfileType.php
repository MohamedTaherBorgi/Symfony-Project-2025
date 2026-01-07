<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;


class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your first name']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'First name must be at least {{ limit }} characters',
                        'maxMessage' => 'First name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
                'empty_data' => '',  // <-- this ensures null becomes empty string
            ])
            ->add('nom', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your last name']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Last name must be at least {{ limit }} characters',
                        'maxMessage' => 'Last name cannot be longer than {{ limit }} characters',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
                'empty_data' => '',  // <-- this ensures null becomes empty string
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'disabled' => true, // shows email but not editable
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your email']),
                    new Assert\Email(['message' => 'Please enter a valid email address']),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Phone Number',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your phone number']),
                    new Assert\Regex([
                        'pattern' => '/^[0-9]{8}$/',
                        'message' => 'Invalid phone number (must be 8 digits)',
                    ]),
                ],
                'attr' => ['class' => 'form-control', 'placeholder' => '12345678'],
                'empty_data' => '',  // <-- this ensures null becomes empty string
            ])
            ->add('street', TextType::class, [
                'label' => 'Street',
                'constraints' => [new Assert\NotBlank(['message' => 'Please enter your street'])],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'constraints' => [new Assert\NotBlank(['message' => 'Please enter your city'])],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal Code',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your postal code']),
                    new Assert\Regex([
                        'pattern' => '/^\d{4,5}$/',
                        'message' => 'Postal code must be 4-5 digits',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'New Password (optional)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Password must be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                        'message' => 'Password must include at least one uppercase, one lowercase, one number, and one symbol',
                    ]),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Profile Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Upload a valid JPG or PNG file',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
