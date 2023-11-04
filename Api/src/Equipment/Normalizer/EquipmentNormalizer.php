<?php

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Entity\Mechanics\Book;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EquipmentNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;
    private GearToolServiceInterface $gearToolService;
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;
    private EquipmentEffectServiceInterface $equipmentEffectService;

    public function __construct(
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService,
        ConsumableDiseaseServiceInterface $consumableDiseaseService,
        EquipmentEffectServiceInterface $equipmentEffectService
    ) {
        $this->translationService = $translationService;
        $this->gearToolService = $gearToolService;
        $this->consumableDiseaseService = $consumableDiseaseService;
        $this->equipmentEffectService = $equipmentEffectService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        $language = $currentPlayer->getDaedalus()->getLanguage();

        $key = $object->getName();
        $nameParameters = [];

        if ($object instanceof Door) {
            $context['door'] = $object;
            $type = 'door';
        } elseif ($object instanceof GameItem || EquipmentEnum::equipmentToNormalizeAsItems()->contains($object->getName())) {
            $context['item'] = $object;
            $type = 'items';
        } else {
            $context['equipment'] = $object;
            $type = 'equipments';
        }

        $statuses = [];
        foreach ($object->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['equipment' => $object]));
            if (is_array($normedStatus) && count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        if (($blueprint = $object->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT)) instanceof Blueprint) {
            $key = ItemEnum::BLUEPRINT;
            $resultEquipmentName = $blueprint->getCraftedEquipmentName();
            $nameParameters['item'] = $resultEquipmentName;
        }

        if (($book = $object->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BOOK)) instanceof Book) {
            $key = ItemEnum::APPRENTON;
            $nameParameters['skill'] = $book->getSkill();
        }

        $definition = $this->getDefinition($object, $key, $type, $language);

        return [
            'id' => $object->getId(),
            'key' => $key,
            'name' => $this->translationService->translate($key . '.name', $nameParameters, $type, $language),
            'description' => $definition,
            'statuses' => $statuses,
            'actions' => $this->getActions($object, $currentPlayer, $format, $context),
            'effects' => $this->getRationsEffect($object, $currentPlayer->getDaedalus()),
        ];
    }

    private function getActions(GameEquipment $gameEquipment, Player $currentPlayer, ?string $format, array $context): array
    {
        $actions = [];

        $contextActions = $this->getContextActions($gameEquipment, $currentPlayer);

        /** @var Action $action */
        foreach ($contextActions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        $actionsObject = $gameEquipment->getEquipment()->getActions()
            ->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::CURRENT)
        ;

        /** @var Action $action */
        foreach ($actionsObject as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        return $actions;
    }

    private function getContextActions(GameEquipment $gameEquipment, Player $currentPlayer): Collection
    {
        $scopes = [ActionScopeEnum::ROOM];
        $scopes[] = ($gameEquipment->isInShelf()) ? ActionScopeEnum::SHELVE : ActionScopeEnum::INVENTORY;

        if ($gameEquipment instanceof GameItem) {
            $target = GameItem::class;
        } else {
            $target = GameEquipment::class;
        }

        return $this->gearToolService->getActionsTools($currentPlayer, $scopes, $target);
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
                $description = "$description//$ingredientTranslation";
            }
        }

        return $description;
    }
}
