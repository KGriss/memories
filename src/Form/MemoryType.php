<?php

namespace App\Form;

use App\Entity\Memory;
use App\Entity\Mind;
use App\Entity\Travel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('location')
            ->add('picture')
            ->add('mind', EntityType::class, [
                'class' => Mind::class,
                'choice_label' => 'id',
            ])
            ->add('travels', EntityType::class, [
                'class' => Travel::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Memory::class,
        ]);
    }
}
