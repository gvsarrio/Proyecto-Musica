<?php

namespace App\Form;

use App\Entity\Musico;
use App\Entity\Instrumento;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MusicoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('telefono')
            ->add('biografia')
            ->add('ubicacion')
            ->add('anyos_experiencia')
            
            // Campo de imagen corregido con sintaxis PHP 8
            ->add('imagen_url', FileType::class, [
                'label' => 'Foto de perfil (JPG, PNG, WEBP)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        mimeTypesMessage: 'Por favor, sube una imagen válida (JPG, PNG, WEBP)'
                    )
                ],
            ])

            // Campo de instrumentos configurado como Checkboxes
            ->add('instrumentos', EntityType::class, [
                'class' => Instrumento::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'expanded' => true, // Transforma el select en checkboxes
                'mapped' => false,  // Se gestiona manualmente en el Controlador
                'required' => false,
                'label' => 'Instrumentos que tocas',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Musico::class,
        ]);
    }
}