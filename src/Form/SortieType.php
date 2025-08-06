<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'attr' => [
                    'minlength' => 3,
                    'maxlength' => 255,
                ],
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie',
                'input' => 'datetime_immutable',
                'html5' => true,
                'data' => (new \DateTimeImmutable('+2 days'))->setTime(18,0),
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'widget' => 'single_text',
                'label' => "Date limite d'inscription",
                'input' => 'datetime_immutable',
                'html5' => true,
                'data' => new \DateTimeImmutable('+1 day'),
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre de places',
                'data' => 10,
                'attr' => [
                    'min' => 3,
                    'max' => 100,
                ],
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'data' => 60,
                'attr' => [
                    'min' => 15,
                    'max' => 4320,
                ],
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
                'attr' => [
                    'minlength' => 5,
                    'maxlength' => 1000,
                ],
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom',
                'disabled' => true,
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu',
                'placeholder' => 'Choisissez un lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue',
                'mapped' => false,
                'disabled' => true,
            ])
            ->add('ville', TextType::class, [
                'label' => 'Ville',
                'mapped' => false,
                'disabled' => true,
            ])
            ->add('codePostal', TextType::class, [
                'label' => 'Code postal',
                'mapped' => false,
                'disabled' => true,
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'mapped' => false,
                'disabled' => true,
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'mapped' => false,
                'disabled' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
