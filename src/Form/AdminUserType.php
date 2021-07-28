<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, array(
                'label' => 'Email',
                'attr' => array('class'=>'form-control')                
            ))
            ->add('roles', ChoiceType::class, array(
                'label' => 'Rol',
                'attr' => array('class'=>'form-control'), 
                'required'=> true,
                'mapped' => false,
                'choices' => array(
                    'Administrador'=>'ROLE_ADMIN',
                    'Usuario'=>'ROLE_USER'
                )
            ))
            ->add('password', PasswordType::class, array(
                'label' => 'Clave',
                'attr' => array('class'=>'form-control'), 
                'required' => true
            ))
            ->add('nick', TextType::class, array(
                'label' => 'Nick',
                'attr' => array('class'=>'form-control'), 
                'mapped' => false,
                'required' => true
            ))
            ->add('nombre', TextType::class, array(
                'label' => 'Nombre',
                'attr' => array('class'=>'form-control'), 
                'mapped' => false,
                'required' => false
            ))
            ->add('apellidos', TextType::class, array(
                'label' => 'Apellidos',
                'attr' => array('class'=>'form-control'), 
                'mapped' => false,
                'required' => false
            ))
            ->add('direccion', TextareaType::class, array(
                'label' => 'Dirección',
                'attr' => array('class'=>'form-control'), 
                'mapped' => false,
                'required' => false
            ))
            ->add('telefono', TextType::class, array(
                'label' => 'Teléfono',
                'attr' => array('class'=>'form-control'), 
                'mapped' => false,
                'required' => false
            ))
            ->add('sexo', ChoiceType::class, array(
                'label' => 'Sexo',
                'attr' => array('class'=>'form-control'), 
                'mapped' => false,
                'required' => true,
                'choices' => array(
                    'Masculino'=>'Masculino',
                    'Femenino'=>'Femenino'
                )
            ))
            ->add('descripcion', TextareaType::class, array(
                'label' => 'Descripción',
                'attr' => array('class'=>'form-control'), 
                'mapped' => false,
                'required' => false
            ))  
            ->add('estado', ChoiceType::class, array(
                'label' => 'Estado',
                'attr' => array('class'=>'form-control'), 
                'mapped' => false,
                'required' => true,
                'choices' => array(
                    'Desbloqueado'=>'1',
                    'Bloqueado'=>'0'
                )
            ))
            ->add('avatar', filetype::class, array(
                "label" => "Foto de Perfil:",
                "help" => "No especificar para mantener el avatar actual",
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
