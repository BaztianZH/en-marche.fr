<?php

namespace App\Form;

use App\Entity\CertificationRequest;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfficialReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('document', FileType::class, [
                'attr' => [
                    'accept' => implode(',', CertificationRequest::MIME_TYPES),
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'filter_emojis' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => $this->getTypeChoices(),
                'choice_label' => function (string $choice) {
                    return 'donation.type.'.$choice;
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', CertificationRequest::class);
    }
}
