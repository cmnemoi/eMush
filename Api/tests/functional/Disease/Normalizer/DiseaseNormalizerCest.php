<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Normalizer;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Normalizer\DiseaseNormalizer;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DiseaseNormalizerCest extends AbstractFunctionalTest
{
    private DiseaseNormalizer $diseaseNormalizer;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private PlayerDisease $playerDisease;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->diseaseNormalizer = $I->grabService(DiseaseNormalizer::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
    }

    #[DataProvider('shouldNormalizeDiseaseDataProvider')]
    public function shouldNormalizeDisease(FunctionalTester $I, Example $example): void
    {
        $this->givenADisease($example);

        $actualNormalizedDisease = $this->whenInormalizeTheDisease();

        $this->thenTheNormalizedDiseaseIsAsExpected(
            $actualNormalizedDisease,
            $this->expectedNormalizedDisease($example),
            $I
        );
    }

    private function givenADisease(Example $example): void
    {
        $this->playerDisease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: $example['diseaseKey'],
            player: $this->player,
            reasons: []
        );
    }

    private function whenInormalizeTheDisease(): array
    {
        return $this->diseaseNormalizer->normalize($this->playerDisease, format: null, context: ['currentPlayer' => $this->player]);
    }

    private function thenTheNormalizedDiseaseIsAsExpected(array $actualNormalizedDisease, array $expectedNormalizedDisease, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $expectedNormalizedDisease['key'],
            actual: $actualNormalizedDisease['key']
        );
        $I->assertEquals(
            expected: $expectedNormalizedDisease['name'],
            actual: $actualNormalizedDisease['name']
        );
        $I->assertEquals(
            expected: $expectedNormalizedDisease['type'],
            actual: $actualNormalizedDisease['type']
        );

        // When we add a new modifier, this may change the order of the disease modifiers in its description.
        // Therefore we just check that all expected modifiers are present in the description, instead of an exact match.
        $expectedDescriptionParts = explode('//', $expectedNormalizedDisease['description']);
        foreach ($expectedDescriptionParts as $expectedDescriptionPart) {
            $I->assertStringContainsString(
                needle: $expectedDescriptionPart,
                haystack: $actualNormalizedDisease['description']
            );
        }
    }

    private function expectedNormalizedDisease(Example $example): array
    {
        return [
            'key' => $example['diseaseKey'],
            'name' => $example['diseaseName'],
            'type' => $example['diseaseType'],
            'description' => $example['diseaseDescription'],
        ];
    }

    private function shouldNormalizeDiseaseDataProvider(): array
    {
        return [
            'flu' => [
                'diseaseKey' => DiseaseEnum::FLU,
                'diseaseName' => 'Grippe',
                'diseaseType' => MedicalConditionTypeEnum::DISEASE,
                'diseaseDescription' => 'Variante sévère de la grippe du poney... Irritable, autoritaire, mal de crâne, rhume, courbatures, rien ne vous est épargné. Attention à ne pas trop laisser traîner, on ne sait jamais.//Chaque cycle, **40%** de chances de rendre sale.//Max :hp: **-2**.//Max :pmo: **-2**.//Chaque cycle, **10%** de chances de perdre **1** :hp:.//Chaque cycle, **20%** de chances de perdre **1** :pa:.//Fait subir le symptôme **Vomissements**.//Fait subir le symptôme **Nausée**.',
            ],
            'agoraphobia' => [
                'diseaseKey' => DisorderEnum::AGORAPHOBIA,
                'diseaseName' => 'Agoraphobie',
                'diseaseType' => MedicalConditionTypeEnum::DISORDER,
                'diseaseDescription' => 'Vous êtes effrayé par la foule et les grands espaces, vous avez, en plus, des vertiges incontrôlables... Dans l\'espace, ça va pas le faire.//S\'il y a au moins **4** personnes dans la pièce, chaque action coûte **1** :pa: de plus.//S\'il y a au moins **4** personnes dans la pièce, chaque déplacement coûte **1** :pm: de plus.//Impossible de **piloter**.',
            ],
            'gastroenteritis' => [
                'diseaseKey' => DiseaseEnum::GASTROENTERIS,
                'diseaseName' => 'GastroEntérite',
                'diseaseType' => MedicalConditionTypeEnum::DISEASE,
                'diseaseDescription' => 'Y a-t-il vraiment besoin de faire un dessin, de parler de l\'odeur ou des spasmes de l\'estomac ?//Max :hp: **-1**.//Chaque cycle, fait perdre **1** :pm:.//Chaque cycle, **16%** de chances de perdre **1** :hp:.//À chaque action **Consommer**, fait perdre **1** :pa:.//Fait subir le symptôme **Vomissements**.//Fait subir le symptôme **Nausée**.//Chaque cycle, rend sale.',
            ],
            'paranoia' => [
                'diseaseKey' => DisorderEnum::PARANOIA,
                'diseaseName' => 'Crise Paranoïaque',
                'diseaseType' => MedicalConditionTypeEnum::DISORDER,
                'diseaseDescription' => 'Vous avez peur pour votre vie et éventuellement le faites savoir.//Max :pmo: **-3**.//Vos messages sont parfois modifiés.',
            ],
        ];
    }
}
