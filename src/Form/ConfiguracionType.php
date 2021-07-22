<?php

namespace App\Form;

use App\Entity\Configuracion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfiguracionType extends AbstractType
{    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder   
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Ambas claves deben coincidir.',
                'help' => 'Dejar en blanco para mantener la actual',               
                'required' => false,
                'mapped' => false,
                'first_options' => array('label'=>'Clave nueva'),
                'second_options' => array('label'=>'Confirmar clave nueva'),
            ))
            ->add('avisar', CheckboxType::class, array(
                'label' => false,
                'required' => false,
            ))
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Configuracion::class,
        ]);
    }
}
