<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Normalizer;

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

    public function testNormalizeFlu(FunctionalTester $I): void
    {
        // given a player with a flu
        $this->playerDisease = $this->playerDiseaseService->createDiseaseFromName(
            DiseaseEnum::FLU,
            $this->player,
            []
        );

        // when normalizing the flu
        $normalizedDisease = $this->diseaseNormalizer->normalize($this->playerDisease, format: null, context: ['currentPlayer' => $this->player]);

        // then the normalized flu is as expected
        $I->assertEquals(
            [
                'key' => DiseaseEnum::FLU,
                'name' => 'Grippe',
                'type' => MedicalConditionTypeEnum::DISEASE,
                'description' => 'Variante sévère de la grippe du poney... Irritable, autoritaire, mal de crâne, rhume, courbatures, rien ne vous est épargné. Attention à ne pas trop laisser traîner, on ne sait jamais.//Max :hp: **-2**.//Max :pmo: **-2**.//Chaque cycle, **10%** de chances de perdre **1** :hp:.//Chaque cycle, **20%** de chances de perdre **1** :pa:.//Fait subir le symptôme **Vomissements**.//Fait subir le symptôme **Nausée**.//Chaque cycle, **40%** de chances de rendre sale.',
            ],
            $normalizedDisease
        );
    }

    public function testNormalizeAgoraphobia(FunctionalTester $I): void
    {
        // given a player with agoraphobia
        $this->playerDisease = $this->playerDiseaseService->createDiseaseFromName(
            DisorderEnum::AGORAPHOBIA,
            $this->player,
            []
        );

        // when normalizing the agoraphobia
        $normalizedDisease = $this->diseaseNormalizer->normalize($this->playerDisease, format: null, context: ['currentPlayer' => $this->player]);

        // then the normalized agoraphobia is as expected
        $I->assertEquals(
            [
                'key' => DisorderEnum::AGORAPHOBIA,
                'name' => 'Agoraphobie',
                'type' => MedicalConditionTypeEnum::DISORDER,
                'description' => 'Vous êtes effrayé par la foule et les grands espaces, vous avez, en plus, des vertiges incontrôlables... Dans l\'espace, ça va pas le faire.//S\'il y a au moins **4** personnes dans la pièce, chaque action coûte **1** :pa: de plus.//S\'il y a au moins **4** personnes dans la pièce, chaque déplacement coûte **1** :pm: de plus.//Impossible de **piloter**.',
            ],
            $normalizedDisease
        );
    }

    public function shouldNormalizeGastroEnteritis(FunctionalTester $I): void
    {
        // given a player with gastro-enteritis
        $this->playerDisease = $this->playerDiseaseService->createDiseaseFromName(
            DiseaseEnum::GASTROENTERIS,
            $this->player,
            []
        );

        // when normalizing the gastro-enteritis
        $normalizedDisease = $this->diseaseNormalizer->normalize($this->playerDisease, format: null, context: ['currentPlayer' => $this->player]);

        // then the normalized gastro-enteritis is as expected
        $I->assertEquals(
            [
                'key' => DiseaseEnum::GASTROENTERIS,
                'name' => 'GastroEntérite',
                'type' => MedicalConditionTypeEnum::DISEASE,
                'description' => 'Y a-t-il vraiment besoin de faire un dessin, de parler de l\'odeur ou des spasmes de l\'estomac ?//Max :hp: **-1**.//Chaque cycle, fait perdre **1** :pm:.//Chaque cycle, **16%** de chances de perdre **1** :hp:.//À chaque action **Consommer**, fait perdre **1** :pa:.//Fait subir le symptôme **Vomissements**.//Fait subir le symptôme **Nausée**.//Chaque cycle, rend sale.',
            ],
            $normalizedDisease
        );
    }

    public function shouldNormalizeParanoia(FunctionalTester $I): void
    {
        // given a player with paranoia
        $this->playerDisease = $this->playerDiseaseService->createDiseaseFromName(
            DisorderEnum::PARANOIA,
            $this->player,
            []
        );

        // when normalizing the paranoia
        $normalizedDisease = $this->diseaseNormalizer->normalize($this->playerDisease, format: null, context: ['currentPlayer' => $this->player]);

        // then the normalized paranoia is as expected
        $I->assertEquals(
            [
                'key' => DisorderEnum::PARANOIA,
                'name' => 'Crise Paranoïaque',
                'type' => MedicalConditionTypeEnum::DISORDER,
                'description' => 'Vous avez peur pour votre vie et éventuellement le faites savoir.//Max :pmo: **-3**.//Vos messages sont parfois modifiés.',
            ],
            $normalizedDisease
        );
    }
}
