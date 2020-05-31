<?php

namespace App\Form;

use App\Entity\Building;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuildingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('user')
            ->add('bulk', TextareaType::class, [
                'required' => false,
                'mapped' => false,
                'data' => "First Floor: Kitchen, Dining Room, Living Room, Bathroom\nSecond Floor: Bedroom 1, Bathroom, Bedroom 2\n",
                'help' => "Format is topName: list,of,children"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Building::class,
        ]);
    }
}
