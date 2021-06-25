<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nick', TextType::class, array(
                'label' => 'Nick',
                'mapped' => false,
                'required' => true,
            ))
            ->add('sexo', ChoiceType::class, array(
                'placeholder' => 'Sexo',
                'mapped' => false,
                'required' => true,
                'choices'  => array(
                    'Masculino' => 'Masculino',
                    'Femenino' => 'Femenino'
                ),
            ))
            ->add('email')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Ambas contraseñas deben coincidir.',                
                'required' => true,
                'mapped' => false,
                'first_options' => array(
                    'label'=>'Clave',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Usted debe especificar la contraseña',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'La contraseña debe tener al menos {{ limit }} caracteres',
                            // max length allowed by Symfony for security reasons
                            'max' => 100,
                        ]),
                    ]
                ),
                'second_options' => array(
                    'label'=>'Confirmar clave',                    
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Usted debe especificar la contraseña',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'La contraseña debe tener al menos {{ limit }} caracteres',
                            // max length allowed by Symfony for security reasons
                            'max' => 100,
                        ]),
                    ]
                ),
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
