<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            ])
            ->add('dateHeureDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie',
                'input' => 'datetime_immutable',
                'html5' => true,
                'format' => 'd/m/Y H:i',
                'data' => (new \DateTimeImmutable('+2 days'))->setTime(18,0),
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'widget' => 'single_text',
                'label' => "Date limite d'inscription",
                'input' => 'datetime_immutable',
                'html5' => true,
                'format' => 'd/m/Y', // pour masquer l'heure
                'data' => new \DateTimeImmutable('+1 day'),
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre de places',
                'data' => 10,
                'attr' => [
                    'min' => 5,
                    'max' => 1000,
                ],
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'data' => 60,
                'attr' => [
                    'min' => 5,
                    'max' => 100,
                ],
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
            ])
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom',
                'disabled' => true,
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
