<?php

namespace App\Form;

use App\Entity\Musico;
use App\Entity\Usuario;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('imagen_url')
            ->add('creado_en')
            ->add('actualizado_en')
            ->add('es_banda')
            ->add('user_id', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'id',
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
