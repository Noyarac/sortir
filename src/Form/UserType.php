<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use App\Repository\CampusRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if($options['isAdmin']){
            $builder
                ->add('actif', CheckboxType::class, [
                    'label' => 'Compte actif',
                    'required' => false,
                ]);
        }
            $builder
            ->add(
                'deleteImage',
                CheckboxType::class,
                [
                    "label" => "Supprimer l'image de profil.",
                    "mapped" => false,
                    "required" => false,
                ]
            )
            ->add(
                'image',
                FileType::class,
                [
                    "mapped" => false,
                    "required" => false,
                    "constraints" => [
                        new Image([
                            "maxSize" => "6000k",
                            "mimeTypes" => [
                                "image/jpeg",
                                "image/png",
                                "image/jpeg",
                            ],
                            "mimeTypesMessage" => "Veuillez utiliser un JPEG ou un PNG.",
                        ])
                    ]
                ]
            )
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom',
                'expanded' => false,
                'multiple' => false,
                'disabled'=> !$options['isAdmin'],
                'placeholder' => 'Veuillez choisir un campus',
                'query_builder' => function (CampusRepository $campusRepository) {
                    return $campusRepository->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                }
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'attr' => [
                    'minlength' => 2,
                    'maxlength' => 50,
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'minlength' => 2,
                    'maxlength' => 50,
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'minlength' => 2,
                    'maxlength' => 50,
                ],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ] );
        if(!$options['isAdmin'] || $options['creation']){
            $builder
                ->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les mots de passe doivent correspondre.',
                    'mapped' => false,
                    'required' => $options['creation'],
                    'first_options'  => [
                        'label' => 'Nouveau mot de passe',
                        'attr' => ['autocomplete' => 'new-password'],
                    ],
                    'second_options' => [
                        'label' => 'Confirmation mot de passe',
                    ],
                    'constraints' => [
                        new Length(max:4096),
                        new Regex(pattern:'/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_\-+={}\[\]|:;\/\\\\"\'<>,.?~]).{8,}$/',
                            message:"Le mot de passe doit comporter au minimum 8 caractères dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial : !@#$%^&*()_-+={}[]|:;/\"'<>,.?~",
                        )
                    ]
                ]);
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isAdmin' => false,//valeur par défaut false : par défaut on considère un utilisateur comme non admin
            'creation' => false,
        ]);
    }
}
