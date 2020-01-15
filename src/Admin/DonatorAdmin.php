<?php

namespace AppBundle\Admin;

use AppBundle\Donation\DonatorManager;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Donator;
use AppBundle\Entity\DonatorTag;
use AppBundle\Form\Admin\DonatorKinshipType;
use AppBundle\Form\GenderType;
use AppBundle\Form\UnitedNationsCountryType;
use AppBundle\Repository\DonationRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class DonatorAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

    private $donatorManager;

    public function __construct(string $code, string $class, string $baseControllerName, DonatorManager $donatorManager)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->donatorManager = $donatorManager;
    }

    public function configureActionButtons($action, $object = null)
    {
        return array_merge(parent::configureActionButtons($action, $object), [
            'merge' => [
                'template' => 'admin/donator/merge/merge_button.html.twig',
            ],
        ]);
    }

    public function configureBatchActions($actions)
    {
        unset($actions['delete']);

        return $actions;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Informations générales', ['class' => 'col-md-6'])
                ->add('identifier', null, [
                    'label' => 'Numéro donateur',
                    'disabled' => true,
                    'help' => 'Généré automatiquement à la création',
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-6'])
                ->add('city', null, [
                    'label' => 'Ville',
                ])
                ->add('country', UnitedNationsCountryType::class, [
                    'label' => 'Pays',
                ])
            ->end()
            ->with('Administration', ['class' => 'col-md-6'])
                ->add('tags', null, [
                    'label' => 'Tags',
                ])
                ->add('comment', null, [
                    'label' => 'Commentaire',
                ])
            ->end()
        ;

        if (!$this->isCurrentRoute('create')) {
            $form
                ->with('Dons', ['class' => 'col-md-6'])
                    ->add('referenceDonation', null, [
                        'label' => 'Sélectionnez le don de référence',
                        'expanded' => true,
                        'placeholder' => 'Dernier don en date',
                        'query_builder' => function (DonationRepository $repository) {
                            return $repository->getSubscriptionsForDonatorQueryBuilder($this->getSubject());
                        },
                        'choice_label' => function (Donation $donation) {
                            $date = $donation->getCreatedAt();

                            return sprintf(
                                '[%s] %d€ le %s à %s (%s)',
                                $this->trans('donation.type.'.$donation->getType(), []),
                                $donation->getAmountInEuros(),
                                $date->format('d/m/Y'),
                                $date->format('H:i'),
                                $this->trans('donation.status.'.$donation->getStatus(), [])
                            );
                        },
                    ])
                ->end()
            ;
        }

        $form
            ->with('Liens', ['class' => 'col-md-6'])
                ->add('kinships', CollectionType::class, [
                    'entry_type' => DonatorKinshipType::class,
                    'required' => false,
                    'label' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'error_bubbling' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('identifier', null, [
                'label' => 'Numéro donateur',
                'show_filter' => true,
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
            ])
            ->add('country', ChoiceFilter::class, [
                'label' => 'Nationalité',
                'show_filter' => true,
                'field_type' => CountryType::class,
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('isAdherent', CallbackFilter::class, [
                'label' => 'Est adhérent ?',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'required' => false,
                    'choices' => [
                        'yes',
                        'no',
                    ],
                    'choice_label' => function (string $choice) {
                        return 'global.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
                        case 'yes':
                            $qb->andWhere(sprintf('%s.adherent IS NOT NULL', $alias));

                            return true;

                        case 'no':
                            $qb->andWhere(sprintf('%s.adherent IS NULL', $alias));

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('isSubscriber', CallbackFilter::class, [
                'label' => 'Type de don',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'required' => false,
                    'choices' => [
                        'ponctual',
                        'recurrent',
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    switch ($value['value']) {
                        case 'ponctual':
                            $qb
                                ->getQueryBuilder()
                                ->leftJoin("$alias.donations", 'donations')
                                ->andWhere('donations.duration = :duration')
                                ->setParameter('duration', PayboxPaymentSubscription::NONE)
                            ;

                            return true;

                        case 'recurrent':
                            $qb
                                ->getQueryBuilder()
                                ->leftJoin("$alias.donations", 'donations')
                                ->andWhere('donations.duration != :duration')
                                ->setParameter('duration', PayboxPaymentSubscription::NONE)
                            ;

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('paymentMethod', CallbackFilter::class, [
                'label' => 'Méthode de paiement',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'required' => false,
                    'multiple' => true,
                    'choices' => [
                        Donation::TYPE_CB,
                        Donation::TYPE_CHECK,
                        Donation::TYPE_TRANSFER,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (empty($types = $value['value'])) {
                        return false;
                    }

                    $qb
                        ->getQueryBuilder()
                        ->leftJoin("$alias.donations", 'donations')
                        ->andWhere('donations.type IN (:types)')
                        ->setParameter('types', $types)
                    ;

                    return true;
                },
            ])
            ->add('donationStatus', CallbackFilter::class, [
                'label' => 'Status de don',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'required' => false,
                    'multiple' => true,
                    'choices' => [
                        Donation::STATUS_REFUNDED,
                        Donation::STATUS_CANCELED,
                        Donation::STATUS_ERROR,
                        Donation::STATUS_FINISHED,
                        Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                        Donation::STATUS_WAITING_CONFIRMATION,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'donation.type.'.$choice;
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (empty($status = $value['value'])) {
                        return false;
                    }

                    $qb
                        ->getQueryBuilder()
                        ->leftJoin("$alias.donations", 'donations')
                        ->andWhere('donations.status IN (:status)')
                        ->setParameter('status', $status)
                    ;

                    return true;
                },
            ])
            ->add('tags', ModelFilter::class, [
                'label' => 'Tags',
                'show_filter' => true,
                'field_options' => [
                    'class' => DonatorTag::class,
                    'multiple' => true,
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('identifier', null, [
                'label' => 'Numéro donateur',
            ])
            ->add('isAdherent', 'boolean', [
                'label' => 'Adhérent',
                'template' => 'admin/donator/list_is_adherent.html.twig',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('lastSuccessfulDonation', null, [
                'label' => 'Date du dernier don',
                'template' => 'admin/donator/list_last_donation.html.twig',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
                'template' => 'admin/donator/list_tags.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Numéro donateur' => 'identifier',
            'Nom' => 'lastName',
            'Prénom' => 'firstName',
            'Civilité' => 'gender',
            'Adresse e-mail' => 'emailAddress',
            'Ville' => 'city',
            'Pays' => 'country',
            'Commentaire' => 'comment',
            'Adresse de référence' => 'getReferenceAddress',
            'Tags' => 'getTagsAsString',
        ];
    }

    /**
     * @param Donator $donator
     */
    public function prePersist($donator)
    {
        parent::prePersist($donator);

        $donator->setIdentifier($this->donatorManager->incrementeIdentifier(false));
    }
}
