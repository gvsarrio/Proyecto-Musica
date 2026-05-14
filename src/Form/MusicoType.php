<?php

namespace App\Form;

use App\Entity\Musico;
use App\Entity\Instrumento;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Count;

class MusicoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['placeholder' => 'Tu nombre completo'],
            ])
            ->add('telefono', TelType::class, [
                'label' => 'Teléfono',
                'required' => false,
                'attr' => ['placeholder' => '000000000'],
            ])
            ->add('biografia', TextareaType::class, [
                'label' => 'Biografía',
                'attr' => ['placeholder' => 'Cuéntanos sobre ti...', 'rows' => 5],
            ])
            ->add('ubicacion', TextType::class, [
                'label' => 'Ubicación',
                'attr' => ['placeholder' => 'Ciudad o región'],
            ])
            ->add('latitud', HiddenType::class, [
                'required' => false,
            ])
            ->add('longitud', HiddenType::class, [
                'required' => false,
            ])
            ->add('anyos_experiencia', IntegerType::class, [
                'label' => 'Años de experiencia',
                'attr' => ['placeholder' => '0'],
            ])

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
                'expanded' => true,
                'mapped' => false,
                'required' => false,
                'label' => '¿Qué instrumentos tocas?',
                'constraints' => [
                    new Count(
                        min: 1,
                        minMessage: 'Debes seleccionar al menos un instrumento'
                    )
                ],
                'attr' => [
                    'class' => 'perfil-instrumentos-container' // <-- ESTA ES LA CLAVE
                ],
                'row_attr' => [
                    'id' => 'musico_instrumentos' // <-- ESTO ACTIVA VUESTROS ESTILOS POR ID
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Musico::class,
        ]);
    }
}