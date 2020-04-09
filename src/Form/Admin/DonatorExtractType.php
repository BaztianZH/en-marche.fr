<?php

namespace AppBundle\Form\Admin;

use AppBundle\Donation\DonatorExtractCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DonatorExtractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('emails', TextareaType::class, [
                'required' => true,
            ])
            ->add('fields', ChoiceType::class, [
                'choices' => DonatorExtractCommand::FIELD_CHOICES,
                'choice_label' => function (string $choice) {
                    return "donator.extract.field.$choice";
                },
                'required' => true,
                'expanded' => true,
                'multiple' => true,
            ])
        ;

        $builder
            ->get('emails')
            ->addModelTransformer(new CallbackTransformer(
                function ($data) {
                    return implode("\n", array_map('trim', $data));
                },
                function ($value) {
                    return array_filter(array_map('trim', explode("\n", $value)));
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DonatorExtractCommand::class,
        ]);
    }
}
