<?php

namespace App\Form;

use App\Entity\Banda;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BandaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('biografia')
            ->add('generos')
            ->add('anyo_formacion')
            ->add('ubicacion')
            ->add('imagen_url')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Banda::class,
        ]);
    }
}
