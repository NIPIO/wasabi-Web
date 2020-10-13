<?php

namespace AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Categoria;

class ProductoType extends AbstractType
{	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

            $builder->add('nombre', TextType::class, []) 
                        ->add('descripcion', TextType::class, []) 
                        ->add('categoria',EntityType::class, [
                            // 'class' => Categoria::class,
                            'class' => 'AppBundle:Categoria',
                            'choice_value' => function ($categoria) {
                                if ($categoria) {
                                    return $categoria->getId();
                                }
                            },
                            'choice_label' => function ($categoria) {
                                return $categoria->getNombre();
                            }
                        ])
                        //para que en el select aparezca entidad hace falta el metodo __tostring en las entidadades.
                        ->add('precio', TextType::class, []) 
                        ->add('url', FileType::class, []) 
                        ->add('save', SubmitType::class, ['label' => 'Cargar']);
    }

}
    