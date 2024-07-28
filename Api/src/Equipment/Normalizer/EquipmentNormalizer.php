<?php

namespace Mush\Equipment\Normalizer;

use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Normalizer\ActionHolderNormalizerTrait;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EquipmentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use ActionHolderNormalizerTrait;
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;
    private EquipmentEffectServiceInterface $equipmentEffectService;

    public function __construct(
        TranslationServiceInterface $translationService,
        ConsumableDiseaseServiceInterface $consumableDiseaseService,
        EquipmentEffectServiceInterface $equipmentEffectService
    ) {
        $this->translationService = $translationService;
        $this->consumableDiseaseService = $consumableDiseaseService;
        $this->equipmentEffectService = $equipmentEffectService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment;
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

        if (EquipmentEnum::equipmentToNormalizeAsItems()->contains($equipment->getName()) || $equipment instanceof GameItem) {
            $normalizedEquipment['updatedAt'] = $equipment->getUpdatedAt();
        }

        return $normalizedEquipment;
    }

    private function getNameKey(GameEquipment $equipment): string
    {
        if ($equipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT) instanceof Blueprint) {
            return ItemEnum::BLUEPRINT;
        }
        if ($equipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BOOK) instanceof Book) {
            return ItemEnum::APPRENTON;
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
        }

        return $nameParameters;
    }

    private function getEquipmentEffects(GameEquipment $equipment, Player $currentPlayer): array
    {
        if ($equipment->isAFruit() && $currentPlayer->hasSkill(SkillEnum::BOTANIST)) {
            return $this->getRationsEffect($equipment, $currentPlayer->getDaedalus());
        }
        if ($equipment->isAPlant() && $currentPlayer->hasSkill(SkillEnum::BOTANIST)) {
            return $this->getPlantEffects($equipment, $currentPlayer->getDaedalus());
        }

        return [];
    }

    private function getRationsEffect(GameEquipment $gameEquipment, Daedalus $daedalus): array
    {
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
            'effects' => array_merge($effects, $this->createConsumableLines($this->equipmentEffectService->getConsumableEffect($ration, $daedalus), $language)),
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

    private function createConsumableLines(ConsumableEffect $consumableEffect, string $language): array
    {
        $effects = [];

        $satiety = $consumableEffect->getSatiety();
        if ($satiety) {
            $effects[] = $this->createEffectLine($satiety, 'satiety_point', $language);
        }
        $actionPoint = $consumableEffect->getActionPoint();
        if ($actionPoint) {
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
        $description = $this->translationService->translate("{$key}.description", [], $type, $language);

        if (($blueprint = $equipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT)) instanceof Blueprint) {
            foreach ($blueprint->getIngredients() as $name => $number) {
                $ingredientTranslation = $this->translationService->translate(
                    'blueprint_ingredient.description',
                    ['quantity' => $number, 'item' => $name],
                    'items',
                    $language
                );
                $description = "{$description}//{$ingredientTranslation}";
            }
        }

        return $description;
    }
}
