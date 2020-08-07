<?php

namespace App\Form\TerritorialCouncil;

use App\Entity\TerritorialCouncil\Candidacy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidacyQualityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quality', ChoiceType::class, [
                'choices' => array_combine($options['qualities'], $options['qualities']),
                'choice_label' => function (string $choice) {
                    return 'territorial_council.membership.quality.'.$choice;
                },
                'disabled' => 1 === \count($options['qualities']),
            ])
            ->add('invitation', CandidacyInvitationType::class, [
                'memberships' => $options['memberships'],
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Candidacy::class,
            ])
            ->setRequired(['qualities', 'memberships'])
            ->setAllowedTypes('qualities', 'array')
            ->setAllowedTypes('memberships', 'array')
        ;
    }
}
