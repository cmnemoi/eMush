<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\Normalizer;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Game\Service\TranslationService;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class EquipmentNormalizerCest extends AbstractFunctionalTest
{
    private EquipmentNormalizer $equipmentNormalizer;
    private TranslationServiceInterface $translationService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentNormalizer = $I->grabService(EquipmentNormalizer::class);
        $this->equipmentNormalizer->setNormalizer($I->grabService(NormalizerInterface::class));
        $this->translationService = $I->grabService(TranslationService::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldNormalizeBlueprintAsBlueprint(FunctionalTester $I)
    {
        $blueprint = $this->createEquipment(EquipmentEnum::SWEDISH_SOFA . '_' . ItemEnum::BLUEPRINT, $this->player->getPlace());

        $normalizedBlueprint = $this->equipmentNormalizer->normalize($blueprint, context: ['currentPlayer' => $this->player]);

        $name = $this->translationService->translate(
            key: 'blueprint.name',
            parameters: ['item' => 'swedish_sofa'],
            domain: 'items',
            language: $this->daedalus->getLanguage(),
        );

        $definition = $this->translationService->translate(
            key: 'blueprint.description',
            parameters: [],
            domain: 'items',
            language: $this->daedalus->getLanguage(),
        ) . '//' . $this->translationService->translate(
            'blueprint_ingredient.description',
            ['quantity' => '1', 'item' => ItemEnum::THICK_TUBE],
            'items',
            $this->daedalus->getLanguage()
        ) . '//' . $this->translationService->translate(
            'blueprint_ingredient.description',
            ['quantity' => '1', 'item' => ItemEnum::METAL_SCRAPS],
            'items',
            $this->daedalus->getLanguage()
        );

        $I->assertequals(
            $name,
            $normalizedBlueprint['name']
        );
        $I->assertequals(
            $definition,
            $normalizedBlueprint['description']
        );
    }

    public function shouldNormalizeKitAsKit(FunctionalTester $I)
    {
        $kit = $this->createEquipment(EquipmentEnum::SWEDISH_SOFA . '_' . ItemEnum::KIT, $this->player->getPlace());

        $normalizedKit = $this->equipmentNormalizer->normalize($kit, context: ['currentPlayer' => $this->player]);

        $name = $this->translationService->translate(
            key: 'swedish_sofa_kit.name',
            parameters: [],
            domain: 'items',
            language: $this->daedalus->getLanguage(),
        );

        $definition = $this->translationService->translate(
            key: 'swedish_sofa_kit.description',
            parameters: [],
            domain: 'items',
            language: $this->daedalus->getLanguage(),
        ) . '//:heavy: ' . $this->translationService->translate(
            key: 'heavy.description',
            parameters: [],
            domain: 'status',
            language: $this->daedalus->getLanguage(),
        );

        $I->assertequals(
            $name,
            $normalizedKit['name']
        );
        $I->assertequals(
            $definition,
            $normalizedKit['description']
        );
    }

    public function shouldNormalizeHeavyStatusForHeavyItem(FunctionalTester $I)
    {
        $microwave = $this->createEquipment(ToolItemEnum::MICROWAVE, $this->player->getPlace());

        $normalizedMicrowave = $this->equipmentNormalizer->normalize($microwave, context: ['currentPlayer' => $this->player]);

        $definition = $this->translationService->translate(
            key: 'microwave.description',
            parameters: [],
            domain: 'items',
            language: $this->daedalus->getLanguage(),
        ) . '//:heavy: ' . $this->translationService->translate(
            key: 'heavy.description',
            parameters: [],
            domain: 'status',
            language: $this->daedalus->getLanguage(),
        );

        $I->assertequals(
            $definition,
            $normalizedMicrowave['description']
        );
    }

    public function shouldNormalizeHeavyStatusForItemMadeHeavy(FunctionalTester $I)
    {
        $banana = $this->createEquipment(GameFruitEnum::BANANA, $this->player->getPlace());

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::HEAVY,
            holder: $banana,
            tags: [],
            time: new \DateTime(),
        );

        $bananaDescription = $this->equipmentNormalizer->normalize($banana, context: ['currentPlayer' => $this->player]);

        $bananaDefinition = $this->translationService->translate(
            key: 'banana.description',
            parameters: [],
            domain: 'items',
            language: $this->daedalus->getLanguage(),
        ) . '//:heavy: ' . $this->translationService->translate(
            key: 'heavy.description',
            parameters: [],
            domain: 'status',
            language: $this->daedalus->getLanguage(),
        );

        $I->assertequals(
            $bananaDefinition,
            $bananaDescription['description']
        );
    }
}
