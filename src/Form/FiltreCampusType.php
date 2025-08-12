<?php

namespace App\Form;

use App\Form\DTO\FiltreCampus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreCampusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'contient',
                TextType::class,
                [
                    "label" => "Le nom contient",
                    "required" => false
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FiltreCampus::class,
            "csrf_token_id" => "csrf_filtre_campus",
        ]);
    }
}
