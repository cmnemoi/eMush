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
        parent::__construct();

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
        if ($record->level->value >= $this->logLevel && filter_var($this->webhookUrl, FILTER_VALIDATE_URL)) {
            $timestamp = date('c');
            $content = $this->formatField($record->message, 6000);
            $title = $this->formatField($record->message, 256);
            $description = $this->formatField($record->formatted, 2048);
            $body = $this->formatField($record->extra['body'], 256);
            $correlationId = $this->formatField($record->extra['correlationId'], 256);
            $uri = $this->formatField($record->extra['uri'], 256);
            $json = [
                // Message
                'content' => $content,
                // text-to-speech
                'tts' => false,
                // Embeds Array
                'embeds' => [
                    [
                        // Title
                        'title' => $title,

                        // Embed Type, do not change.
                        'type' => 'rich',

                        // Description
                        'description' => $description,

                        // Timestamp, only ISO8601
                        'timestamp' => $timestamp,

                        // Left border color, in HEX
                        'color' => hexdec('3366ff'),

                        // Custom fields
                        'fields' => [
                            [
                                'name' => 'Channel',
                                'value' => $record->channel,
                                'inline' => false,
                            ],
                            [
                                'name' => 'CorrelationId',
                                'value' => $correlationId,
                                'inline' => false,
                            ],
                            [
                                'name' => 'Request Body',
                                'value' => $body,
                                'inline' => false,
                            ],
                            [
                                'name' => 'Request Uri',
                                'value' => $uri,
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

    private function sendMessage($json): void
    {
        $this->httpClient->request(
            'POST',
            $this->webhookUrl,
            [
                'json' => $json,
            ]
        );
    }

    private function formatField(?string $field, int $maxLength): string
    {
        $formatted = '';
        if ($field !== null) {
            $formatted = substr($field, 0, $maxLength);
        }

        return $formatted;
    }
}
