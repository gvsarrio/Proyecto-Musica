<?php

namespace App\Form;

use App\Entity\Musico;
use App\Entity\InstrumentoSistema;
use App\Entity\InstrumentoPersonalizado;
use App\Entity\Usuario;
use App\Repository\InstrumentoPersonalizadoRepository;
use Doctrine\ORM\EntityRepository;
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

class MusicoType extends AbstractType
{
    public function __construct(
        private InstrumentoPersonalizadoRepository $personalizadoRepo,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $usuario = $options['usuario'];

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
            ->add('imagen_url', FileType::class, [
                'label' => 'Foto de perfil (JPG, PNG, WEBP)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Por favor, sube una imagen válida (JPG, PNG, WEBP)'
                    )
                ],
            ])
            ->add('instrumentos_nuevos', TextType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Ej: guitarra'],
            ])
            ->add('instrumentos_sistema', EntityType::class, [
                'class' => InstrumentoSistema::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'required' => false,
                'label' => false,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('i')->orderBy('i.nombre', 'ASC'),
            ])
            ->add('instrumentos_personalizados', EntityType::class, [
                'class' => InstrumentoPersonalizado::class,
                'choice_label' => 'nombre',
                'multiple' => true,
                'expanded' => true,
                'mapped' => false,
                'required' => false,
                'label' => false,
                'query_builder' => function (EntityRepository $er) use ($usuario) {
                    $qb = $er->createQueryBuilder('ip')->orderBy('ip.nombre', 'ASC');
                    if ($usuario) {
                        $qb->where('ip.usuario = :usuario')->setParameter('usuario', $usuario);
                    } else {
                        $qb->where('1 = 0');
                    }
                    return $qb;
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Musico::class,
            'usuario' => null,
        ]);
        $resolver->setAllowedTypes('usuario', ['null', Usuario::class]);
    }
}
