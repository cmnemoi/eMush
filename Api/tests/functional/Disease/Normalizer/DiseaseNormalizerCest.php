<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Normalizer;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Normalizer\DiseaseNormalizer;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

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

    public function testNormalizeReturnsExpectedArray(FunctionalTester $I): void
    {
        // given a disease
        $this->playerDisease = $this->playerDiseaseService->createDiseaseFromName(
            DiseaseEnum::FLU,
            $this->player,
            []
        );

        // when normalizing the disease
        $normalizedDisease = $this->diseaseNormalizer->normalize($this->playerDisease, format: null, context: ['currentPlayer' => $this->player]);

        // then the normalized disease is as expected
        $I->assertEquals(
            [
                'key' => DiseaseEnum::FLU,
                'name' => 'Grippe',
                'type' => MedicalConditionTypeEnum::DISEASE,
                'description' => 'Variante sévère de la grippe du poney... Irritable, autoritaire, mal de crâne, rhume, courbatures, rien ne vous est épargné. Attention à ne pas trop laisser traîner, on ne sait jamais.//Chaque cycle, **10%** de chances de perdre **1** :hp:.//Chaque cycle, **20%** de chances de perdre **1** :pa:.//Max :pmo: **-2**.//Max :hp: **-2**.//Fait subir le symptôme **Vomissements**.//Chaque cycle, **40%** de chances de rendre sale.//Fait subir le symptôme **Nausée**.',
            ],
            $normalizedDisease
        );
    }
}
