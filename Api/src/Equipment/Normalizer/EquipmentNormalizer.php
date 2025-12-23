<?php

namespace Mush\Equipment\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Entity\Player;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EquipmentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use ActionHolderNormalizerTrait;
    use NormalizerAwareTrait;

    public function __construct(
        private ConsumableDiseaseServiceInterface $consumableDiseaseService,
        private EquipmentEffectServiceInterface $equipmentEffectService,
        private RebelBaseRepositoryInterface $rebelBaseRepository,
        private TranslationServiceInterface $translationService,
    ) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            GameEquipment::class => true,
            GameItem::class => true,
            SpaceShip::class => true,
            Door::class => true,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var GameEquipment $equipment */
        $equipment = $object;

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        $language = $currentPlayer->getDaedalus()->getLanguage();

        $key = $this->getNameKey($equipment);

        $context[$equipment->getClassName()] = $equipment;
        $type = $equipment->getNormalizationType();

        $statuses = [];
        foreach ($equipment->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['equipment' => $equipment]));
            if (\is_array($normedStatus) && \count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        $nameParameters = $this->getNameParameters($equipment);

        $definition = $this->getDefinition($equipment, $key, $type, $language);

        $normalizedEquipment = [
            'id' => $equipment->getId(),
            'key' => $key,
            'name' => $this->translationService->translate($key . '.name', $nameParameters, $type, $language),
            'description' => $definition,
            'statuses' => $statuses,
            'actions' => $this->getNormalizedActions($equipment, ActionHolderEnum::EQUIPMENT, $currentPlayer, $format, $context),
            'effects' => $this->getEquipmentEffects($equipment, $currentPlayer),
        ];

        if ($equipment->shouldBeNormalizedAsItem() || $equipment->getName() === EquipmentEnum::SWEDISH_SOFA) {
            $normalizedEquipment['updatedAt'] = $equipment->getUpdatedAt();
        }

        return $normalizedEquipment;
    }

    private function getNameKey(GameEquipment $equipment): string
    {
        if ($equipment->hasMechanicByName(EquipmentMechanicEnum::KIT)) {
            return $equipment->getName();
        }
        if ($equipment->hasMechanicByName(EquipmentMechanicEnum::BLUEPRINT)) {
            return ItemEnum::BLUEPRINT;
        }
        if ($equipment->hasMechanicByName(EquipmentMechanicEnum::BOOK)) {
            return ItemEnum::APPRENTRON;
        }
        if ($equipment instanceof SpaceShip) {
            return $equipment->getPatrolShipName();
        }

        return $equipment->getName();
    }

    private function getNameParameters(GameEquipment $equipment): array
    {
        $nameParameters = [];
        if (($blueprint = $equipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT)) instanceof Blueprint) {
            $resultEquipmentName = $blueprint->getCraftedEquipmentName();
            $nameParameters['item'] = $resultEquipmentName;
        }
        if ($equipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::PLANT) instanceof Plant) {
            $nameParameters['age'] = $equipment->hasStatus(EquipmentStatusEnum::PLANT_YOUNG) ? 'young' : '';
        }
        if (($book = $equipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BOOK)) instanceof Book) {
            $nameParameters['skill'] = $book->getSkill()->toString();
            $nameParameters['character_gender'] = 'other';
            $nameParameters['character'] = 'other';
        }

        return $nameParameters;
    }

    private function getEquipmentEffects(GameEquipment $equipment, Player $player): array
    {
        if ($player->canReadFoodProperties($equipment)) {
            return $this->getRationsEffect($equipment, $player);
        }
        if ($player->canReadPlantProperties($equipment)) {
            return $this->getPlantEffects($equipment, $player->getDaedalus());
        }

        return [];
    }

    private function getRationsEffect(GameEquipment $gameEquipment, Player $player): array
    {
        $daedalus = $player->getDaedalus();
        $language = $daedalus->getLanguage();

        /** @var Ration $ration */
        $ration = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::RATION);
        if ($ration === null) {
            return [];
        }

        $effects = [];

        $consumableDiseaseEffect = $this->consumableDiseaseService->findConsumableDiseases($gameEquipment->getName(), $daedalus);
        if ($consumableDiseaseEffect !== null) {
            /** @var ConsumableDiseaseAttribute $disease */
            foreach ($consumableDiseaseEffect->getDiseases() as $disease) {
                $effects[] = $this->createDiseaseLine($disease, $language);
            }

            /** @var ConsumableDiseaseAttribute $cure */
            foreach ($consumableDiseaseEffect->getCures() as $cure) {
                $effects[] = $this->createCureLine($cure, $language);
            }
        }

        return [
            'title' => $this->translationService->translate('ration_data', [], 'misc', $language),
            'effects' => array_merge($effects, $this->createConsumableLines($gameEquipment, $this->equipmentEffectService->getConsumableEffect($ration, $daedalus), $player)),
        ];
    }

    private function getPlantEffects(GameEquipment $plant, Daedalus $daedalus): array
    {
        $language = $daedalus->getLanguage();

        $effects = [
            $this->translationService->translate('fruit_productivity', ['quantity' => $plant->getFruitProduction()], 'misc', $language),
            $this->translationService->translate('o2_productivity', ['quantity' => $plant->getOxygenProduction()], 'misc', $language),
            $plant->isYoungPlant() ? $this->translationService->translate('maturity_info', ['quantity' => $plant->getMaturationTimeLeftOrThrow()], 'misc', $language) : '',
        ];

        return [
            'title' => $this->translationService->translate('plant_data', [], 'misc', $language),
            'effects' => array_filter($effects),
        ];
    }

    private function createConsumableLines(GameEquipment $food, ConsumableEffect $consumableEffect, Player $player): array
    {
        $language = $player->getLanguage();
        $effects = [];

        $satiety = $consumableEffect->getSatiety();
        if ($satiety) {
            $effects[] = $this->createEffectLine($satiety, 'satiety_point', $language);
        }
        $actionPoint = $consumableEffect->getActionPoint();
        if ($actionPoint) {
            $actionPoint += $this->getFrugivoreBonus($food, $player);
            $actionPoint += $this->getSiriusRebelBaseBonus($food);
            $actionPoint += $this->getGuarannaCappuccinoBonus($food, $player);
            $effects[] = $this->createEffectLine($actionPoint, 'action_point', $language);
        }
        $movementPoint = $consumableEffect->getMovementPoint();
        if ($movementPoint) {
            $effects[] = $this->createEffectLine($movementPoint, 'movement_point', $language);
        }
        $healthPoint = $consumableEffect->getHealthPoint();
        if ($healthPoint) {
            $effects[] = $this->createEffectLine($healthPoint, 'health_point', $language);
        }
        $moralPoint = $consumableEffect->getMoralPoint();
        if ($moralPoint) {
            $effects[] = $this->createEffectLine($moralPoint, 'moral_point', $language);
        }

        return $effects;
    }

    private function createDiseaseLine(ConsumableDiseaseAttribute $disease, string $language): string
    {
        $diseaseName = $this->translationService->translate($disease->getDisease() . '.name', [], 'disease', $language);

        if ($disease->getDelayMin() > 0) {
            $key = 'delayed_disease_info';
            $params = [
                'quantity' => $disease->getRate(),
                'diseaseName' => $diseaseName,
                'start' => $disease->getDelayMin(),
            ];

            if ($disease->getDelayLength() > 0) {
                $key .= '_with_range';
                $params['end'] = $disease->getDelayMin() + $disease->getDelayLength();
            }
        } else {
            $key = 'disease_info';
            $params = [
                'quantity' => $disease->getRate(),
                'diseaseName' => $diseaseName,
            ];
        }

        return $this->translationService->translate($key, $params, 'misc', $language);
    }

    private function createCureLine(ConsumableDiseaseAttribute $cure, string $language): string
    {
        $cureName = $this->translationService->translate($cure->getDisease() . '.name', [], 'disease', $language);

        if ($cure->getDelayMin() > 0) {
            $key = 'delayed_cure_info';
            $params = [
                'diseaseName' => $cureName,
                'start' => $cure->getDelayMin(),
            ];

            if ($cure->getDelayLength() > 0) {
                $key .= '_with_range';
                $params['end'] = $cure->getDelayMin() + $cure->getDelayLength();
            }
        } else {
            $key = 'cure_info';
            $params = [
                'diseaseName' => $cureName,
            ];
        }

        return $this->translationService->translate($key, $params, 'misc', $language);
    }

    private function createEffectLine(int $quantity, string $key, string $language): string
    {
        $sign = $quantity > 0 ? '+' : '-';
        $quantity = abs($quantity);

        return "{$sign} {$quantity} {$this->translationService->translate($key, [], 'misc', $language)}";
    }

    private function getDefinition(GameEquipment $equipment, string $key, string $type, string $language): string
    {
        $translationParameters = [];
        if ($equipment->hasMechanicByName(EquipmentMechanicEnum::CONTAINER)) {
            $translationParameters['quantity'] = $equipment->getUsedCharge(ActionEnum::OPEN_CONTAINER->toString())?->getCharge();
        }

        if ($equipment instanceof Drone) {
            $translationParameters = array_merge(
                $translationParameters,
                $equipment->toExamineLogParameters($this->translationService)
            );
        }

        $description = $this->translationService->translate("{$key}.description", $translationParameters, $type, $language);

        if ($equipment->hasMechanicByName(EquipmentMechanicEnum::BLUEPRINT) && !$equipment->hasMechanicByName(EquipmentMechanicEnum::KIT)) {
            foreach ($equipment->getBlueprintMechanicOrThrow()->getIngredients() as $name => $number) {
                $ingredientTranslation = $this->translationService->translate(
                    'blueprint_ingredient.description',
                    ['quantity' => $number, 'item' => $name],
                    'items',
                    $language
                );
                $description = "{$description}//{$ingredientTranslation}";
            }
        }

        if ($equipment->hasStatus(EquipmentStatusEnum::HEAVY)) {
            $heavyTranslation = $this->translationService->translate(
                'heavy.description',
                [],
                'status',
                $language
            );
            $description = "{$description}//:heavy: {$heavyTranslation}";
        }

        return $description;
    }

    private function getFrugivoreBonus(GameEquipment $food, Player $player): int
    {
        if ($player->hasModifierByModifierName(ModifierNameEnum::FRUGIVORE_MODIFIER_FOR_ALIEN_FRUITS) && $food->isAnAlienFruit()) {
            return (int) $player->getModifiers()->getModifierByModifierNameOrThrow(ModifierNameEnum::FRUGIVORE_MODIFIER_FOR_ALIEN_FRUITS)->getVariableModifierConfigOrThrow()->getDelta();
        }
        if ($player->hasModifierByModifierName(ModifierNameEnum::FRUGIVORE_MODIFIER_FOR_BANANA) && $food->isABanana()) {
            return (int) $player->getModifiers()->getModifierByModifierNameOrThrow(ModifierNameEnum::FRUGIVORE_MODIFIER_FOR_BANANA)->getVariableModifierConfigOrThrow()->getDelta();
        }

        return 0;
    }

    private function getSiriusRebelBaseBonus(GameEquipment $food): int
    {
        $daedalus = $food->getDaedalus();

        if (!$daedalus->hasModifierByModifierName(ModifierNameEnum::SIRIUS_REBEL_BASE_MODIFIER)) {
            return 0;
        }

        $siriusModifierConfig = $daedalus->getModifiers()->getModifierByModifierNameOrThrow(ModifierNameEnum::SIRIUS_REBEL_BASE_MODIFIER)->getVariableModifierConfigOrThrow();
        if (!\in_array($food->getName(), array_keys($siriusModifierConfig->getTagConstraints()), true)) {
            return 0;
        }

        return (int) $siriusModifierConfig->getDelta();
    }

    private function getGuarannaCappuccinoBonus(GameEquipment $food, Player $currentPlayer): int
    {
        $daedalus = $currentPlayer->getDaedalus();
        if (!$daedalus->hasActiveProject(ProjectName::GUARANA_CAPPUCCINO)) {
            return 0;
        }

        $guarannaModifierConfig = $daedalus->getModifiers()->getModifierByModifierNameOrThrow(ModifierNameEnum::GUARANA_CAPPUCCINO_MODIFIER)->getVariableModifierConfigOrThrow();
        if (!\in_array($food->getName(), array_keys($guarannaModifierConfig->getTagConstraints()), true)) {
            return 0;
        }

        return (int) $guarannaModifierConfig->getDelta();
    }
}
