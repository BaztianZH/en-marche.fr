<?php

namespace AppBundle\Admin;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\ElectedRepresentative\ElectedRepresentative;
use AppBundle\Entity\ElectedRepresentative\ElectedRepresentativeLabel;
use AppBundle\Entity\ElectedRepresentative\MandateTypeEnum;
use AppBundle\Entity\ElectedRepresentative\PoliticalFunctionNameEnum;
use AppBundle\Entity\PoliticalLabel;
use AppBundle\Form\AdherentEmailType;
use AppBundle\Form\ElectedRepresentative\ElectedRepresentativeLabelType;
use AppBundle\Form\GenderType;
use AppBundle\Form\MandateType;
use AppBundle\Form\PoliticalFunctionType;
use AppBundle\Form\SocialNetworkLinkType;
use AppBundle\Form\SponsorshipType;
use AppBundle\Repository\PoliticalLabelRepository;
use Doctrine\ORM\Query\Expr;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ElectedRepresentativeAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    /** @var PoliticalLabelRepository */
    private $politicalLabelRepository;

    public function __construct($code, $class, $baseControllerName, PoliticalLabelRepository $politicalLabelRepository)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->politicalLabelRepository = $politicalLabelRepository;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('currentMandates', null, [
                'label' => 'Mandats actuels (nuance politique)',
                'template' => 'admin/elected_representative/list_mandates.html.twig',
            ])
            ->add('currentPoliticalFunctions', null, [
                'label' => 'Fonctions actuelles',
                'template' => 'admin/elected_representative/list_political_functions.html.twig',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/elected_representative/list_is_adherent.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Identité', ['class' => 'col-md-6'])
                ->add('officialId', null, [
                    'label' => 'ID élu officiel',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('emailAddress', EmailType::class, [
                    'mapped' => false,
                    'label' => 'Adresse e-mail de l\'adhérent',
                    'template' => 'admin/elected_representative/show_email.html.twig',
                ])
                ->add('contactEmail', null, [
                    'label' => 'Autre e-mail de contact',
                ])
                ->add('isAdherent', null, [
                    'label' => 'Adhérent',
                    'template' => 'admin/elected_representative/show_is_adherent.html.twig',
                ])
                ->add('phone', null, [
                    'mapped' => false,
                    'label' => 'Téléphone',
                    'template' => 'admin/elected_representative/show_phone.html.twig',
                ])
                ->add('contactPhone', null, [
                    'label' => 'Autre téléphone de contact',
                    'template' => 'admin/elected_representative/show_contact_phone.html.twig',
                ])
                ->add('birthDate', null, [
                    'label' => 'Date de naissance',
                ])
                ->add('birthPlace', null, [
                    'label' => 'Lieu de naissance',
                ])
                ->add('isSupportingLaREM', null, [
                    'label' => 'Sympathisant LaREM',
                ])
                ->add('hasFollowedTraining', null, [
                    'label' => 'Formation Tous Politiques !',
                ])
                ->add('comment', null, [
                    'label' => 'Commentaire',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Identité', ['class' => 'col-md-6'])
                ->add('officialId', null, [
                    'label' => 'ID élu officiel',
                    'disabled' => true,
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('adherent', AdherentEmailType::class, [
                    'required' => false,
                    'label' => 'Adresse e-mail',
                    'help' => 'Attention, changer l\'e-mail ici fera que l\'élu sera associé à un autre compte adhérent.'
                        .' Si vous souhaitez ajouter un autre mail de contact, faites-le ci-dessous.',
                ])
                ->add('contactEmail', null, [
                    'label' => 'Autre e-mail de contact',
                    'required' => false,
                ])
                ->add('isAdherent', ChoiceType::class, [
                    'label' => 'Est adhérent ?',
                    'choices' => [
                        'global.yes' => true,
                        'global.no' => false,
                        'global.maybe' => null,
                    ],
                ])
                ->add('adherentPhone', PhoneNumberType::class, [
                    'required' => false,
                    'disabled' => true,
                    'label' => 'Téléphone',
                ])
                ->add('contactPhone', PhoneNumberType::class, [
                    'required' => false,
                    'label' => 'Autre téléphone de contact',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'default_region' => Address::FRANCE,
                    'preferred_country_choices' => [Address::FRANCE],
                ])
                ->add('birthDate', 'sonata_type_date_picker', [
                    'label' => 'Date de naissance',
                ])
                ->add('birthPlace', null, [
                    'required' => false,
                    'label' => 'Lieu de naissance',
                ])
                ->add('isSupportingLaREM', null, [
                    'label' => 'Sympathisant LaREM',
                ])
                ->add('hasFollowedTraining', null, [
                    'label' => 'Formation Tous Politiques !',
                ])
                ->add('comment', TextareaType::class, [
                    'required' => false,
                    'label' => 'Commentaire',
                ])
            ->end()
            ->with(
                'Réseaux sociaux',
                [
                    'class' => 'col-md-6',
                ]
            )
                ->add('socialNetworkLinks', CollectionType::class, [
                    'entry_type' => SocialNetworkLinkType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
            ->end()
            ->with(
                'Parrainages',
                [
                    'class' => 'col-md-6',
                ]
            )
                ->add('sponsorships', CollectionType::class, [
                    'entry_type' => SponsorshipType::class,
                    'label' => false,
                    'by_reference' => false,
                ])
            ->end()
            ->with('Mandats')
                ->add('mandates', CollectionType::class, [
                    'entry_type' => MandateType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
            ->end()
            ->with('Fonctions')
                ->add('politicalFunctions', CollectionType::class, [
                    'entry_type' => PoliticalFunctionType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
            ->end()
            ->with('Étiquettes')
                ->add('labels', CollectionType::class, [
                    'entry_type' => ElectedRepresentativeLabelType::class,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
            ->end()
        ;

        $formMapper->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
        $formMapper->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
    }

    public function preSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var Adherent $adherent */
        $adherent = $form->getData()->getAdherent();
        $adherentEmail = $adherent ? $adherent->getEmailAddress() : null;
        $formAdherentEmail = $data['adherent'] ?: null;

        // for any change of email, 'isAdherent' should be set to null ('Peut-être' value)
        if ($adherentEmail !== $formAdherentEmail) {
            $data['isAdherent'] = null;
            $event->setData($data);
        }
    }

    public function submit(FormEvent $event): void
    {
        /** @var ElectedRepresentative $electedRepresentative */
        $electedRepresentative = $event->getData();
        $laREM = $this->politicalLabelRepository->findOneByName(PoliticalLabel::LABEL_LAREM);

        // if adherent, we should add the LaREM label, if it does not exist
        $label = $electedRepresentative->getLabel(PoliticalLabel::LABEL_LAREM);
        if ($electedRepresentative->isAdherent()
            && $electedRepresentative->getAdherent()
            && !$label) {
            $labelLaREM = new ElectedRepresentativeLabel(
                $laREM,
                $electedRepresentative,
                true,
                $electedRepresentative->getAdherent()->getRegisteredAt()->format('Y')
            );
            $electedRepresentative->addLabel($labelLaREM);

            return;
        }

        // and if not adherent, we should remove the LaREM label
        if (true !== $electedRepresentative->isAdherent() && $label) {
            $electedRepresentative->removeLabel($label);
        }
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('mandates.type', CallbackFilter::class, [
                'label' => 'Mandats actuels',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => MandateTypeEnum::CHOICES,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $mandate) {
                        $where->add("$alias.type = :mandate_".$mandate);
                        $qb->setParameter('mandate_'.$mandate, $mandate);
                    }

                    $qb->andWhere($where);
                    $qb->andWhere("$alias.onGoing = 1");

                    return true;
                },
            ])
            ->add('politicalFunctions.name', CallbackFilter::class, [
                'label' => 'Fonctions actuelles',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => PoliticalFunctionNameEnum::CHOICES,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value['value'] as $politicalFunctions) {
                        $where->add("$alias.name = :function_".$politicalFunctions);
                        $qb->setParameter('function_'.$politicalFunctions, $politicalFunctions);
                    }

                    $qb->andWhere($where);
                    $qb->andWhere("$alias.onGoing = 1");

                    return true;
                },
            ])
            ->add('mandates.politicalAffiliation', CallbackFilter::class, [
                'label' => 'Nuance politique',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb->andWhere("$alias.politicalAffiliation LIKE :pa");
                    $qb->setParameter('pa', '%'.$value['value'].'%');
                    $qb->andWhere("$alias.onGoing = 1");

                    return true;
                },
            ])
            ->add('isAdherent', CallbackFilter::class, [
                'label' => 'Est adhérent ?',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'yes',
                        'no',
                        'maybe',
                    ],
                    'choice_label' => function (string $choice) {
                        return 'global.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
                        case 'yes':
                            $qb->andWhere(sprintf('%s.isAdherent = 1', $alias));

                            return true;
                        case 'no':
                            $qb->andWhere(sprintf('%s.isAdherent = 0', $alias));

                            return true;
                        case 'maybe':
                            $qb->andWhere(sprintf('%s.isAdherent IS NULL', $alias));

                            return true;
                        default:
                            return false;
                    }
                },
            ])
        ;
    }

    public function getExportFields()
    {
        return [
            'Nom' => 'lastName',
            'Prénom' => 'firstName',
            'Mandats actuels (nuance politique)' => 'exportMandates',
            'Fonctions actuelles' => 'exportPoliticalFunctions',
            'Adhérent' => 'exportIsAdherent',
        ];
    }

    public function getExportFormats()
    {
        return ['csv', 'xls'];
    }
}
