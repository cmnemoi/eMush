<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Normalizer;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Normalizer\SelectableCharacterNormalizer;
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
                [
                    'key' => 'it_expert',
                    'name' => 'Informaticien',
                    'description' => 'L\'Informaticien est à l\'aise dès qu\'il passe derrière un écran. Il manie à la perfection les
                    interfaces des terminaux de NERON, d\'astrophysique, et de communication.//:point: +2 :pa_comp: (points
                    d\'action **Informatique**) par jour.//:point: Chances de réussites doublées avec le **Bidouilleur**.//:point: Bonus pour développer certains **Projets NERON**.',
                ],
                [
                    'key' => 'neron_only_friend',
                    'name' => 'Seule amie de NERON',
                    'description' => 'Lorsque vous êtes présente dans le vaisseau, NERON est apaisé, ses routines tournent sans erreurs et ses performances s\'en trouvent grandement améliorées.//:point: Les gains de **chaque projet NERON** sont **maximisés** pour tous.',
                ],
                [
                    'key' => 'shrink',
                    'name' => 'Psy',
                    'description' => 'Le Psy occupe un poste de soutien psychologique. Il permet de garder le moral et soigne les maladies psychologiques.//:point: A chaque cycle, **1 Point de Moral (:pmo:) est régénéré** à chaque personnage allongé dans sa pièce.//:point: **Soigne les maladies Psy**.//:point: Accorde l\'action **Réconforter**, laquelle améliore le moral.//:point: Bonus pour développer certains **Projets NERON**.',
                ],
            ],
        ], $normalizedCharacter);
    }
}
