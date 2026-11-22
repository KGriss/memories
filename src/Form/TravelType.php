<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Memory;
use App\Entity\Travel;
use App\Repository\MemoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TravelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //dump($options);
        // Get the current travel from 'data' option passed to the form
        $travel = $options['data'] ?? null;
        // get the travel's creator
        $member = $travel->getCreator();
        $builder
            ->add('name')
            ->add('description')
            ->add('published')
            ->add('creator', EntityType::class, [
                'class' => Member::class,
//                 'choice_label' => 'id',
                'disabled' => true,
            ])
            ->add('memories', EntityType::class,
                    // options :
                    [
                        'class' => Memory::class,
                        // avec 'by_reference' => false, sauvegarde les modifications
                        'by_reference' => false,
                        // permet sélection multiple
                        'multiple' => true,
                        // affiche sous forme de checkboxes
                        'expanded' => true,
                        // adjust the loading of possible memories to those of the current member's mind
                        // the use helps pass the member to the lambda
                        'query_builder' => function (MemoryRepository $er) use ($member) {
                                              return $er->createQueryBuilder('o')
                                                        ->leftJoin('o.mind', 'i')
                                                        ->leftJoin('i.member', 'm')
                                                        ->andWhere('m.id = :memberId')
                                                        ->setParameter('memberId', $member->getId())
                                                        ;
                                                }
                    ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Travel::class,
        ]);
    }
}
