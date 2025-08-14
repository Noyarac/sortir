<?php

namespace App\Form;

use App\Entity\Campus;
use App\Form\DTO\FiltreSortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'label' => "Campus",
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
            ->add("contient", TextType::class, [
                'label' => "Le nom de la sortie contient",
                'attr' => [
                    'minlength' => 3,
                    'maxlength' => 255,
                ],
                "required" => false
            ])
            ->add('debut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Entre',
                'input' => 'datetime_immutable',
                'html5' => true,
                "required" => false
            ])
            ->add('fin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'et',
                'input' => 'datetime_immutable',
                'html5' => true,
                "required" => false
            ])
            ->add('organisateur', CheckboxType::class, [
                'label' => "Sorties dont je suis l'organisateur·trice",
                "required" => false
            ])
            ->add('participant', CheckboxType::class, [
                'label' => "Sorties auxquelles je suis inscrit·e",
                "required" => false
            ])
            ->add('nonParticipant', CheckboxType::class, [
                'label' => "Sorties auxquelles je ne suis pas inscrit·e",
                "required" => false
            ])
            ->add('terminees', CheckboxType::class, [
                'label' => "Inclure les sorties terminées",
                "required" => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FiltreSortie::class,
        ]);
    }
}
