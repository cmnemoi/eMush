<?php

namespace Mush\Disease\Normalizer;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomCondition;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Game\Event\PreparePercentageRollEvent;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Entity\Condition\HolderHasStatusModifierCondition;
use Mush\Modifier\Entity\Condition\MinimumPlayerInPlaceModifierCondition;
use Mush\Modifier\Entity\Condition\ModifierCondition;
use Mush\Modifier\Entity\Condition\RandomModifierCondition;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\ResourceMaxPointEvent;
use Mush\Player\Event\ResourcePointChangeEvent;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DiseaseNormalizer implements ContextAwareNormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PlayerDisease;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var DiseaseConfig $diseaseConfig */
        $diseaseConfig = $object->getDiseaseConfig();

        $language = $diseaseConfig->getGameConfig()->getLanguage();

        $description = $this->translationService->translate(
            "{$diseaseConfig->getName()}.description",
            [],
            'disease',
            $language
        );

        $description = $this->getSymptomEffects($diseaseConfig, $description, $language);
        $description = $this->getModifierEffects($diseaseConfig, $description, $language);

        return [
            'key' => $diseaseConfig->getName(),
            'name' => $this->translationService->translate(
                $diseaseConfig->getName() . '.name',
                [],
                'disease',
                $language
            ),
            'type' => $diseaseConfig->getType(),
            'description' => $description,
       ];
    }

    private function getSymptomEffects(DiseaseConfig $diseaseConfig, string $description, string $language): string
    {
        // first get symptom effects
        $symptomEffects = [];
        /** @var SymptomConfig $symptomConfig */
        foreach ($diseaseConfig->getSymptomConfigs() as $symptomConfig) {
            $name = $symptomConfig->getName();

            $randomCondition = $symptomConfig->getSymptomConditions()
                ->filter(fn (SymptomCondition $condition) => $condition->getName() === SymptomConditionEnum::RANDOM);
            if (!$randomCondition->isEmpty()) {
                $chance = $randomCondition->first()->getValue();
            } else {
                $chance = 100;
            }

            $effect = $this->translationService->translate(
                $name . '.description',
                ['chance' => $chance],
                'modifiers',
                $language
            );

            if (!in_array($effect, $symptomEffects)) {
                array_push($symptomEffects, $effect);
            }
        }

        // then add them to the description
        if (!empty($symptomEffects)) {
            foreach ($symptomEffects as $symptomEffect) {
                $description = $description . '//' . $symptomEffect;
            }
        }

        return $description;
    }

    private function getModifierEffects(DiseaseConfig $diseaseConfig, string $description, string $language): string
    {
        // Get Modifier effect description
        /** @var ModifierConfig $modifierConfig */
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $value = $modifierConfig->getValue();
            $mode = $modifierConfig->getMode();
            $scope = $modifierConfig->getTargetEvents();
            $target = $modifierConfig->getVariable();

            if ($mode == ModifierModeEnum::MULTIPLICATIVE) {
                $subKey = $value < 1 ? '_decrease' : '_increase';
                $key = $this->getKey($scope, $subKey);
                $value = (1 - $value) * 100;
            } else {
                $subKey = $value < 0 ? '_decrease' : '_increase';
                $key = $this->getKey($scope, $subKey);
            }

            $emote = PlayerVariableEnum::getEmoteMap()[$target] ?? '';

            $chance = $this->getModifierChance($modifierConfig);
            $action = $this->getModifierAction($modifierConfig);
            $action = $this->translateAction($action, $language);

            $parameters = [
                'chance' => $chance,
                'action_name' => $action,
                'emote' => $emote,
                'quantity' => abs($value),
            ];

            $effect = $this->translationService->translate(
                $key . '.description',
                $parameters,
                'modifiers',
                $language
            );

            if ($effect) {
                $description = $description . '//' . $effect;
            }
        }

        return $description;
    }

    private function getKey($scope, $subKey): string
    {
        if (array_key_exists(ResourceMaxPointEvent::CHECK_MAX_POINT, $scope)) {
            return 'max_point' . $subKey;
        } elseif (array_key_exists(AbstractQuantityEvent::CHANGE_VARIABLE, $scope)) {
            if (in_array([EventEnum::NEW_CYCLE], $scope[AbstractQuantityEvent::CHANGE_VARIABLE])) {
                return 'new_cycle' . $subKey;
            } elseif (in_array([PlayerEvent::INFECTION_PLAYER], $scope[AbstractQuantityEvent::CHANGE_VARIABLE])) {
                return 'infection.player' . $subKey;
            } else {
                return 'post_action' . $subKey;
            }
        } elseif (array_key_exists(PreparePercentageRollEvent::ACTION_ROLL_RATE, $scope)) {
            if (in_array([PlayerEvent::CYCLE_DISEASE], $scope[PreparePercentageRollEvent::ACTION_ROLL_RATE])) {
                return 'cycle_disease' . $subKey;
            } else {
                return 'action_shoot' . $subKey;
            }
        } elseif (array_key_exists(ResourcePointChangeEvent::CHECK_CHANGE_ACTION_POINT, $scope)) {
            return 'actions' . $subKey;
        } elseif (array_key_exists(ResourcePointChangeEvent::CHECK_CHANGE_MOVEMENT_POINT, $scope)) {
            return 'move' . $subKey;
        } else {
            throw new \LogicException('No possible key found.');
        }
    }

    private function getModifierChance(ModifierConfig $modifierConfig): int
    {
        $randomCondition = $modifierConfig->getConditions()
                ->filter(fn (ModifierCondition $condition) => $condition instanceof RandomModifierCondition);
        if (!$randomCondition->isEmpty()) {
            return $randomCondition->first()->getValue();
        } else {
            return 100;
        }
    }

    private function getModifierAction(ModifierConfig $modifierConfig): ?string
    {
        $condition = $modifierConfig->getConditions()
            ->filter(fn (ModifierCondition $condition) => !($condition instanceof RandomModifierCondition));
        if (!$condition->isEmpty()) {
            $reasonCondition = $condition->first();

            if ($reasonCondition instanceof HolderHasStatusModifierCondition) {
                return SymptomConditionEnum::PLAYER_STATUS;
            } elseif ($reasonCondition instanceof MinimumPlayerInPlaceModifierCondition) {
                return SymptomConditionEnum::THREE_OTHERS;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    private function translateAction(?string $action, $language): string
    {
        if ($action) {
            return $this->translationService->translate($action . 'name', [], 'actions', $language);
        } else {
            return '';
        }
    }
}
