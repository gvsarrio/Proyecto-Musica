<?php

namespace App\Form;

use App\Entity\Banda;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class BandaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre de la banda',
                'attr' => ['placeholder' => 'Ej: Los Relámpagos'],
                'constraints' => [
                    new NotBlank(message: 'El nombre no puede estar vacío'),
                    new Length(min: 2, max: 100, minMessage: 'El nombre debe tener al menos 2 caracteres', maxMessage: 'El nombre no puede superar 100 caracteres'),
                ],
            ])
            ->add('biografia', TextareaType::class, [
                'constraints' => [
                    new NotBlank(message: 'La biografía no puede estar vacía'),
                ],
            ])
            ->add('generos', TextType::class, [
                'required' => false,
            ])
            ->add('anyo_formacion', IntegerType::class, [
                'constraints' => [
                    new NotBlank(message: 'El año de formación no puede estar vacío'),
                    new Range(min: 1900, max: 2100, notInRangeMessage: 'El año debe estar entre 1900 y 2100'),
                ],
            ])
            ->add('ubicacion', TextType::class, [
                'constraints' => [
                    new NotBlank(message: 'La ubicación no puede estar vacía'),
                    new Length(min: 3, minMessage: 'La ubicación debe tener al menos 3 caracteres'),
                ],
            ])
            ->add('latitud', HiddenType::class, ['required' => false])
            ->add('longitud', HiddenType::class, ['required' => false])
            //Subida de imagen
            ->add('imagen_url', FileType::class, [
                'label' => 'Imagen de la banda (JPG, PNG, WEBP)',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Banda::class,
        ]);
    }
}