<?php

declare(strict_types=1);

namespace Mush\Tests\api\MetaGame;

use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Tests\ApiTester;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
final class BiographyControllerCest
{
    public function _before(ApiTester $I): void
    {
        $I->loginUser('default');
    }

    public function shouldReturnCharacterBiography(ApiTester $I): void
    {
        foreach (CharacterEnum::getAllBiographies() as $character) {
            $this->whenIRequestCharacterBiography($I, $character);

            $this->thenResponseShouldContainBiographyData($I);
        }
    }

    private function whenIRequestCharacterBiography(ApiTester $I, string $character): void
    {
        $I->sendGetRequest('/biography/' . $character, [
            'language' => LanguageEnum::FRENCH,
        ]);
    }

    private function thenResponseShouldContainBiographyData(ApiTester $I): void
    {
        $I->seeResponseCodeIs(Response::HTTP_OK);
        $schema = [
            'type' => 'object',
            'properties' => [
                'details' => [
                    'type' => 'object',
                    'properties' => [
                        'fullName' => [
                            'type' => 'string',
                            'pattern' => '^[a-zA-Z]+ [a-zA-Z\- ]+$',
                        ],
                        'age' => [
                            'type' => 'string',
                            'pattern' => '^\*\*\*Âge :\*\*\* .+$',
                        ],
                        'employment' => [
                            'type' => 'string',
                            'pattern' => '^\*\*\*Profession :\*\*\* .+$',
                        ],
                        'abstract' => [
                            'type' => 'string',
                            'pattern' => '^\*\*\*Résumé :\*\*\* .+$',
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'biography' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'date' => [
                                'type' => 'string',
                                'pattern' => '^[0-9]{4}$',
                            ],
                            'entry' => [
                                'type' => 'string',
                                'pattern' => '^.+$',
                            ],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'additionalProperties' => false,
        ];
        $I->seeResponseIsValidOnJsonSchemaString(json_encode($schema));
    }
}
