<?php

namespace App\Form;

use App\Entity\Perfil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PerfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nick')
            ->add('nombre')
            ->add('apellidos')
            ->add('direccion')
            ->add('telefono')
            ->add('sexo', ChoiceType::class, array(
                'placeholder' => 'Sexo',
                'label' => 'Sexo',
                'required'=> true,
                'choices' => array(
                    'Masculino'=>'Masculino',
                    'Femenino'=>'Femenino'
                    )
            ))
            ->add('descripcion')
            ->add('avatar', filetype::class, array(
                "label" => "Foto de Perfil:",
                "help" => "No especificar para mantener el avatar actual",
                //"data_class" => null,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Por favor seleccione un archivo de imágen válido (JPEG/PNG) y no mayor de 500kb',
                    ])
                ],

            ))
            ->add('usuario', HiddenType::class, array('mapped' => false));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Perfil::class,
        ]);
    }
}
