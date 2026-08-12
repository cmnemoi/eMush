<?php

declare(strict_types=1);

namespace Mush\Tests\api\MetaGame;

use Mush\Tests\ApiTester;
use Symfony\Component\HttpFoundation\Response;

final class GameCountersControllerCest
{
    public function shouldExposePublicGameCounters(ApiTester $I): void
    {
        $I->sendGetRequest('/game-counters');

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeHttpHeader('Cache-Control', 'no-cache, public');
        $I->seeHttpHeader('ETag');
        $I->seeResponseIsValidOnJsonSchemaString(json_encode([
            'type' => 'object',
            'required' => [
                'daedalusesInGame',
                'mushKilled',
                'messagesSent',
                'expeditionsStarted',
            ],
            'properties' => [
                'daedalusesInGame' => ['type' => 'integer', 'minimum' => 0],
                'mushKilled' => ['type' => 'integer', 'minimum' => 0],
                'messagesSent' => ['type' => 'integer', 'minimum' => 0],
                'expeditionsStarted' => ['type' => 'integer', 'minimum' => 0],
            ],
            'additionalProperties' => false,
        ]));
    }

    public function shouldReturnNotModifiedForFreshCounters(ApiTester $I): void
    {
        $I->sendGetRequest('/game-counters');
        $etag = $I->grabHttpHeader('ETag');

        $I->haveHttpHeader('If-None-Match', $etag);
        $I->sendGetRequest('/game-counters');

        $I->seeResponseCodeIs(Response::HTTP_NOT_MODIFIED);
    }
}
