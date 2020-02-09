<?php

namespace AppBundle\Form;

use AppBundle\Entity\VoteResult;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoteResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('registered', IntegerType::class, [
                'attr' => ['min' => 0],
            ])
            ->add('abstentions', IntegerType::class, [
                'attr' => ['min' => 0],
            ])
            ->add('voters', IntegerType::class, [
                'attr' => ['min' => 0],
            ])
            ->add('expressed', IntegerType::class, [
                'attr' => ['min' => 0],
            ])
            ->add('lists', CollectionType::class, [
                'entry_type' => VoteResultListType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoteResult::class,
        ]);
    }
}
