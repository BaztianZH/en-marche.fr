<?php

namespace AppBundle\Mailchimp\SignUp;

use AppBundle\Entity\Adherent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SignUpHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $subscriptionGroupId;
    private $subscriptionIds;
    private $client;
    private $formId;
    private $listId;

    public function __construct(
        HttpClientInterface $client,
        int $subscriptionGroupId,
        array $subscriptionIds,
        string $formId,
        string $listId
    ) {
        $this->subscriptionGroupId = $subscriptionGroupId;
        $this->subscriptionIds = $subscriptionIds;
        $this->formId = $formId;
        $this->listId = $listId;
        $this->client = $client;
    }

    public function signUpAdherent(Adherent $adherent): bool
    {
        try {
            $response = $this->client->request('POST', '/subscribe/post', [
                'query' => [
                    'u' => $this->formId,
                    'id' => $this->listId,
                ],
                'body' => $this->getFormData($adherent),
            ]);

            return 200 === $response->getStatusCode();
        } catch (ClientExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    private function getFormData(Adherent $adherent)
    {
        $formData = [
            'EMAIL' => $adherent->getEmailAddress(),
            $this->getTokenKey() => null,
        ];

        foreach ($this->subscriptionIds as $code => $id) {
            if ($adherent->hasSubscriptionType($code)) {
                $formData[sprintf('group[%d][%d]', $this->subscriptionGroupId, $id)] = true;
            }
        }

        return $formData;
    }

    private function getTokenKey(): string
    {
        return sprintf('b_%s_%s', $this->formId, $this->listId);
    }
}
