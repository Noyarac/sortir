<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ville', EntityType::class, [
                'label' => 'Ville',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisissez une ville',
                'query_builder' => function (VilleRepository $villeRepository) {
                    return $villeRepository->createQueryBuilder('v')
                        ->orderBy('v.nom', 'ASC');
                }
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'minlength' => 2,
                    'maxlength' => 50,
                ]
            ] )
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'attr' => [
                    'minlength' => 3,
                    'maxlength' => 255,
                ]
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'attr' => [
                    'min'=> -90,
                    'max'=> 90,
                    'step'=> '0.000001',
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'attr' => [
                    'min'=> -180,
                    'max'=> 180,
                    'step'=> '0.000001'
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
