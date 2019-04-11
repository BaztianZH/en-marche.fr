<?php

namespace Tests\AppBundle\AdherentMessage\Handler;

use AppBundle\AdherentMessage\Command\AdherentMessageChangeCommand;
use AppBundle\AdherentMessage\Handler\AdherentMessageChangeCommandHandler;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use AppBundle\Entity\Committee;
use AppBundle\Entity\District;
use AppBundle\Entity\ReferentTag;
use AppBundle\Mailchimp\Campaign\CampaignContentRequestBuilder;
use AppBundle\Mailchimp\Campaign\CampaignRequestBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\CommitteeMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\DeputyMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\ReferentMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\MailchimpObjectIdMapping;
use AppBundle\Mailchimp\Campaign\SegmentConditionsBuilder;
use AppBundle\Mailchimp\Driver;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdherentMessageChangeCommandHandlerTest extends TestCase
{
    private $adherentDummy;
    private $commandDummy;
    private $clientMock;

    public function testCommitteeMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(CommitteeAdherentMessage::class);
        $message->setFilter($committeeFilter = new CommitteeFilter());
        $committeeFilter->setCommittee($this->createConfiguredMock(Committee::class, [
            'getName' => 'Committee name',
            'getMailchimpId' => 456,
        ]));

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '3',
                        'template_id' => 3,
                        'subject_line' => 'Subject',
                        'title' => 'Full Name - '.date('d/m/Y'),
                        'reply_to' => 'FromName',
                        'from_name' => 'Full Name',
                    ],
                    'recipients' => [
                        'list_id' => 'listId',
                        'segment_opts' => [
                            'match' => 'all',
                            'conditions' => [
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontainsall',
                                    'field' => 'interests-C',
                                    'value' => [],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 456,
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/123/content', ['json' => [
                    'template' => [
                        'id' => 3,
                        'sections' => [
                            'content' => 'Content',
                            'committee_name' => 'Committee name',
                            'committee_link' => '<a class="mcnButton" title="VOIR LE COMITÉ" href="https://committee_url" target="_blank" style="font-weight:normal;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#2BBAFF;">VOIR LE COMITÉ</a>',
                        ],
                    ],
                ]]]
            )
            ->willReturn(new Response(200, [], json_encode(['id' => 123])))
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testReferentMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(ReferentAdherentMessage::class);
        $message->setFilter($filter = new AdherentZoneFilter($tag = new ReferentTag('Tag1', 'code1')));
        $tag->setExternalId(123);

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '1',
                        'template_id' => 1,
                        'subject_line' => 'Subject',
                        'title' => 'Full Name - '.date('d/m/Y'),
                        'reply_to' => 'FromName',
                        'from_name' => 'Full Name',
                    ],
                    'recipients' => [
                        'list_id' => 'listId',
                        'segment_opts' => [
                            'match' => 'all',
                            'conditions' => [
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontainsall',
                                    'field' => 'interests-C',
                                    'value' => [],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 123,
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/123/content', ['json' => [
                    'template' => [
                        'id' => 1,
                        'sections' => [
                            'content' => 'Content',
                            'first_name' => 'First Name',
                        ],
                    ],
                ]]]
            )
            ->willReturn(new Response(200, [], json_encode(['id' => 123])))
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testDeputyMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(DeputyAdherentMessage::class);
        $message->setFilter($filter = new AdherentZoneFilter($tag = new ReferentTag('Tag1', 'code1')));
        $tag->setExternalId(123);

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '2',
                        'template_id' => 2,
                        'subject_line' => 'Subject',
                        'title' => 'Full Name - '.date('d/m/Y'),
                        'reply_to' => 'FromName',
                        'from_name' => 'Full Name',
                    ],
                    'recipients' => [
                        'list_id' => 'listId',
                        'segment_opts' => [
                            'match' => 'all',
                            'conditions' => [
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontainsall',
                                    'field' => 'interests-C',
                                    'value' => [],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 123,
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/123/content', ['json' => [
                    'template' => [
                        'id' => 2,
                        'sections' => [
                            'content' => 'Content',
                            'first_name' => 'First Name',
                            'full_name' => 'Full Name',
                            'district_name' => 'District1',
                        ],
                    ],
                ]]]
            )
            ->willReturn(new Response(200, [], json_encode(['id' => 123])))
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    protected function setUp()
    {
        $this->adherentDummy = $this->createConfiguredMock(Adherent::class, [
            '__toString' => 'Full Name',
            'getFullName' => 'Full Name',
            'getFirstName' => 'First Name',
            'getManagedDistrict' => $this->createConfiguredMock(District::class, ['__toString' => 'District1']),
        ]);

        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->commandDummy = $this->createMock(AdherentMessageChangeCommand::class);
        $this->commandDummy->expects($this->once())->method('getUuid')->willReturn(Uuid::uuid4());
    }

    private function preparedMessage(string $messageClass): AdherentMessageInterface
    {
        $message = new $messageClass(Uuid::uuid4(), $this->adherentDummy);
        $message->setSubject('Subject');
        $message->setContent('Content');

        return $message;
    }

    private function creatRequestBuildersLocator(): ContainerInterface
    {
        return new SimpleContainer([
            CampaignRequestBuilder::class => new CampaignRequestBuilder(
                $mailchimpMapping = new MailchimpObjectIdMapping([
                    'referent' => 1,
                    'deputy' => 2,
                    'committee' => 3,
                    'citizen_project' => 4,
                ], [
                    'referent' => 1,
                    'deputy' => 2,
                    'committee' => 3,
                    'citizen_project' => 4,
                ]),
                new SegmentConditionsBuilder([], 'A', 'B', 'C'),
                'listId',
                'FromName',
                'FromEmail'
            ),
            CampaignContentRequestBuilder::class => new CampaignContentRequestBuilder($mailchimpMapping, $this->createSectionRequestBuildersLocator()),
        ]);
    }

    private function createSectionRequestBuildersLocator(): ContainerInterface
    {
        return new SimpleContainer([
            CommitteeAdherentMessage::class => new CommitteeMessageSectionBuilder($this->createConfiguredMock(UrlGeneratorInterface::class, ['generate' => 'https://committee_url'])),
            ReferentAdherentMessage::class => new ReferentMessageSectionBuilder(),
            DeputyAdherentMessage::class => new DeputyMessageSectionBuilder(),
        ]);
    }

    private function createRepositoryMock(AdherentMessageInterface $message): AdherentMessageRepository
    {
        $repositoryMock = $this->createMock(AdherentMessageRepository::class);
        $repositoryMock->expects($this->once())->method('findOneByUuid')->willReturn($message);

        return $repositoryMock;
    }

    private function createHandler(AdherentMessageInterface $message): AdherentMessageChangeCommandHandler
    {
        return new AdherentMessageChangeCommandHandler(
            $this->createRepositoryMock($message),
            new Manager(new Driver($this->clientMock, 'test'), $this->creatRequestBuildersLocator()),
            $this->createMock(ObjectManager::class)
        );
    }
}

class SimpleContainer implements ContainerInterface
{
    private $container;

    public function __construct(array $container)
    {
        $this->container = $container;
    }

    public function get($id)
    {
        return $this->container[$id] ?? null;
    }

    public function has($id)
    {
        return isset($this->container[$id]);
    }
}
