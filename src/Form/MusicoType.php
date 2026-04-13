<?php

namespace App\Form;

use App\Entity\Instrumento;
use App\Entity\Musico;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MusicoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', null, [
                'label' => 'Nombre',
            ])
            ->add('telefono', null, [
                'label' => 'Teléfono',
            ])
            ->add('biografia', TextareaType::class, [
                'label' => 'Biografía',
            ])
            ->add('ubicacion', null, [
                'label' => 'Ubicación',
            ])
            ->add('anyos_experiencia', null, [
                'label' => 'Años de experiencia',
            ])
            ->add('imagen_url', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Imagen de perfil',
            ])
            ->add('instrumentos', EntityType::class, [
                'class' => Instrumento::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'expanded' => false,
                'mapped' => false,
                'required' => false,
                'label' => 'Instrumentos',
                'attr' => [
                    'class' => 'form-select',
                    'id' => 'select-instrumentos',
                    'size' => '6',
                ],
            ])
            ->add('es_banda', CheckboxType::class, [
                'required' => false,
                'label' => 'Soy una banda',
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
