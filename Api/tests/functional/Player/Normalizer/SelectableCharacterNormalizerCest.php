<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Normalizer;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Normalizer\SelectableCharacterNormalizer;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class SelectableCharacterNormalizerCest extends AbstractFunctionalTest
{
    private SelectableCharacterNormalizer $normalizer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->normalizer = $I->grabService(SelectableCharacterNormalizer::class);
        $this->normalizer->setNormalizer($I->grabService(NormalizerInterface::class));
    }

    public function shouldNormalizeCharacter(FunctionalTester $I): void
    {
        // given
        $characterConfig = $I->grabEntityFromRepository(CharacterConfig::class, ['name' => CharacterEnum::JANICE]);
        $characterConfig->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::DIPLOMAT]),
        ]);

        // when
        $normalizedCharacter = $this->normalizer->normalize(
            $characterConfig,
            context: [
                'character' => $characterConfig,
                'daedalus' => $this->daedalus,
            ]
        );

        // then
        $I->assertEquals([
            'key' => 'janice',
            'name' => 'Janice',
            'abstract' => 'Janice s\'occupe de toute la partie informatique du vaisseau. Elle gère les défragmentations psychanalytiques régulières de NERON et a mis en place le protocole de sécurité A-TRUE, qui autorise l\'ordinateur central à nommer lui-même son administrateur. Janice gère également le suivi psychologique des membres du Daedalus.',
            'skills' => [
                [
                    'key' => 'diplomat',
                    'name' => 'Diplomatie',
                    'description' => 'Un bon diplomate sait comment entrer en contact avec les races extra-terrestres.//:point: Empêche l\'évènement **Combat** lors des rencontres extra-terrestres.//:point: Peut déclarer un cessez-le-feu une fois par partie. Le cessez-le-feu dure **3** cycles.//:point: Ouvre de nouvelles possibilités d\'échanges commerciaux.',
                ],
            ],
            'level' => 1,
        ], $normalizedCharacter);
    }
}
