<?php 

declare(strict_types=1);

namespace Mush;

final readonly class ApiConfig {

    private const string DEV_ENV_NAME = 'dev';

    public string $appName;
    public string $appEnv;
    public string $otelExporterOltpEndpoint;
    public string $oauthClientId;
    public string $oauthClientSecret;

    public function __construct(array $environmentVariables) {
        $this->appName = $environmentVariables['APP_NAME'];
        $this->appEnv = $environmentVariables['APP_ENV'];
        $this->otelExporterOltpEndpoint = $environmentVariables['OTEL_EXPORTER_OTLP_ENDPOINT'];
        $this->oauthClientId = $environmentVariables['OAUTH_CLIENT_ID'];
        $this->oauthClientSecret = $environmentVariables['OAUTH_SECRET_ID'];
    }

    public function isApiOnDev(): bool {
        return $this->appEnv === self::DEV_ENV_NAME;
    }
}
