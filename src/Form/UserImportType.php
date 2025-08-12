<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('csvFile', FileType::class, [
                'label' => 'Fichier CSV des utilisateurs',
                'mapped' => false,
                'attr' =>[
                    'accept' => '.csv',
                ],
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: 'text/csv',
                        mimeTypesMessage: 'Merci de télécharger un fichier CSV valide'
                    ),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
