<?php

declare(strict_types=1);

namespace Mush\Chat\Gateway;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class NeronAnswerGateway implements NeronAnswerGatewayInterface
{
    public function __construct(
        private string $askNeronEndpoint,
        private HttpClientInterface $httpClient,
    ) {}

    public function getFor(string $question): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->askNeronEndpoint, [
                'json' => [
                    'question' => $question,
                    'chat_history' => [],
                ],
            ]);

            return $response->toArray()['answer'];
        } catch (\Exception $exception) {
            throw new \Exception('Failed to get answer from NERON', 0, $exception);
        }
    }
}
