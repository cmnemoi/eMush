<?php

namespace Mush\Logger\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MonologDiscordWebHookHandler extends AbstractProcessingHandler
{
    private HttpClientInterface $httpClient;
    private string $webhookUrl;
    private int $logLevel;
    private string $environmentName;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function setWebhook(string $webHookUrl): void
    {
        $this->webhookUrl = $webHookUrl;
    }

    public function setLogLevel(int $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    public function setEnvironmentName(string $envName): void
    {
        $this->environmentName = $envName;
    }

    protected function write(LogRecord $record): void
    {
        if ($this->webhookUrl != null && filter_var($this->webhookUrl, FILTER_VALIDATE_URL)) {
            if ($record->level->value >= $this->logLevel) {
                $timestamp = date('c', strtotime('now'));
                $json = [
                    // Message
                    'content' => $record->message,
                    // text-to-speech
                    'tts' => false,
                    // Embeds Array
                    'embeds' => [
                        [
                            // Title
                            'title' => $record->message,

                            // Embed Type, do not change.
                            'type' => 'rich',

                            // Description
                            'description' => $record->formatted,

                            // Timestamp, only ISO8601
                            'timestamp' => $timestamp,

                            // Left border color, in HEX
                            'color' => hexdec('3366ff'),

                            // Custom fields
                            'fields' => [
                                [
                                    'name' => 'CorrelationId',
                                    'value' => $record->extra['correlationId'],
                                    'inline' => false,
                                ],
                                [
                                    'name' => 'Request Body',
                                    'value' => $record->extra['body'],
                                    'inline' => false,
                                ],
                                [
                                    'name' => 'Request Uri',
                                    'value' => $record->extra['uri'],
                                    'inline' => false,
                                ],
                                [
                                    'name' => 'Environment',
                                    'value' => $this->environmentName,
                                    'inline' => false,
                                ],
                            ],
                        ],
                    ],
                ];
                $this->sendMessage($json);
            }
        }
    }

    private function sendMessage($json): void
    {
        $response = $this->httpClient->request(
            'POST',
            $this->webhookUrl,
            [
                'json' => $json,
            ]
        );
    }
}
