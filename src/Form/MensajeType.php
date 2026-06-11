<?php

namespace App\Form;

use App\Entity\Mensaje;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MensajeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenido', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'chat-textarea',
                    'rows' => 5,
                    'maxlength' => 2000,
                    'placeholder' => 'Escribe tu mensaje...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mensaje::class,
        ]);
    }
}
