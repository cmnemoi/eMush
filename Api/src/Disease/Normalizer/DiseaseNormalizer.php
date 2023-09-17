<?php

namespace Mush\Disease\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\SymptomActivationRequirementEnum;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\DirectModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DiseaseNormalizer implements NormalizerInterface
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
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var DiseaseConfig $diseaseConfig */
        $diseaseConfig = $object->getDiseaseConfig();

        $language = $currentPlayer->getDaedalus()->getLanguage();

        $description = $this->translationService->translate(
            "{$diseaseConfig->getDiseaseName()}.description",
            [],
            'disease',
            $language
        );

        $description = $this->getVariableEventModifierEffects($diseaseConfig, $description, $language);
        $description = $this->getDirectModifierEffects($diseaseConfig, $description, $language);
        $description = $this->getTriggerEventModifierEffects($diseaseConfig, $description, $language);
        $description = $this->getSymptomEffects($diseaseConfig, $description, $language);

        return [
            'key' => $diseaseConfig->getDiseaseName(),
            'name' => $this->translationService->translate(
                $diseaseConfig->getDiseaseName() . '.name',
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
            $name = $symptomConfig->getSymptomName();

            $randomActivationRequirement = $symptomConfig->getSymptomActivationRequirements()
                ->filter(fn (SymptomActivationRequirement $activationRequirement) => $activationRequirement->getActivationRequirementName() === SymptomActivationRequirementEnum::RANDOM);
            if (!$randomActivationRequirement->isEmpty()) {
                $chance = $randomActivationRequirement->first()->getValue();
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
                $symptomEffects[] = $effect;
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

    private function getVariableEventModifierEffects(DiseaseConfig $diseaseConfig, string $description, string $language): string
    {
        $effects = [];
        /** @var VariableEventModifierConfig $modifierConfig */
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            if (!$modifierConfig instanceof VariableEventModifierConfig) {
                continue;
            }
            $delta = $modifierConfig->getDelta();
            $mode = $modifierConfig->getMode();
            $scope = $modifierConfig->getTargetEvent();
            $target = $modifierConfig->getTargetVariable();
            $tagConstraints = $modifierConfig->getTagConstraints();

            if ($mode == VariableModifierModeEnum::MULTIPLICATIVE) {
                if ($delta < 1) {
                    $key = $modifierConfig->getTargetEvent() . '_decrease';
                } else {
                    $key = $modifierConfig->getTargetEvent() . '_increase';
                }
                $delta = (1 - $delta) * 100;
            } else {
                if ($delta < 0) {
                    $key = $modifierConfig->getTargetEvent() . '_decrease';
                } else {
                    $key = $modifierConfig->getTargetEvent() . '_increase';
                }
            }

            foreach (array_keys($tagConstraints) as $tagConstraint) {
                if ($tagConstraints[$tagConstraint] !== ModifierRequirementEnum::NONE_TAGS) {
                    $key .= '_' . $tagConstraint;
                }
            }

            $emoteMap = PlayerVariableEnum::getEmoteMap();
            if (isset($emoteMap[$scope])) {
                $emote = $emoteMap[$scope];
            } elseif (isset($emoteMap[$target])) {
                $emote = $emoteMap[$target];
            } else {
                $emote = '';
            }

            $chance = $this->getModifierChance($modifierConfig);
            $action = $this->getModifierActions($modifierConfig, $language);

            $parameters = [
                'chance' => $chance,
                'action_name' => $action,
                'emote' => $emote,
                'quantity' => abs($delta),
            ];

            $effect = $this->translationService->translate(
                $key . '.description',
                $parameters,
                'modifiers',
                $language
            );

            if ($effect) {
                if (!in_array($effect, $effects)) {
                    $effects[] = $effect;
                }
            }
        }

        if (!empty($effects)) {
            foreach ($effects as $effect) {
                $description = $description . '//' . $effect;
            }
        }

        return $description;
    }

    private function getDirectModifierEffects(DiseaseConfig $diseaseConfig, string $description, string $language): string
    {
        $effects = [];
        /** @var DirectModifierConfig $modifierConfig */
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            if (!$modifierConfig instanceof DirectModifierConfig) {
                continue;
            }

            /** @var VariableEventConfig $triggeredEvent */
            $triggeredEvent = $modifierConfig->getTriggeredEvent();
            if ($triggeredEvent->getVariableHolderClass() !== 'player') {
                throw new \Exception('Disease DirectModifierConfig should be held only by a player, not a ' . $triggeredEvent->getVariableHolderClass());
            }
            $eventName = $triggeredEvent->getEventName();
            switch ($eventName) {
                case VariableEventInterface::CHANGE_VALUE_MAX:
                    $key = 'max_point';
                    break;
                case VariableEventInterface::CHANGE_VARIABLE:
                    $key = 'change_point';
                    break;
                case VariableEventInterface::SET_VALUE:
                    $key = 'set_point';
                    break;
                default:
                    throw new \Exception('unknown event name');
                    break;
            }
            $quantity = $triggeredEvent->getQuantity();
            if ($quantity < 0) {
                $key = $key . '_decrease';
            } else {
                $key = $key . '_increase';
            }

            $targetVariable = $triggeredEvent->getTargetVariable();

            $emoteMap = PlayerVariableEnum::getEmoteMap();
            if (isset($emoteMap[$targetVariable])) {
                $emote = $emoteMap[$targetVariable];
            } else {
                $emote = '';
            }

            $parameters = [
                'emote' => $emote,
                'quantity' => abs($quantity),
            ];

            $effect = $this->translationService->translate(
                $key . '.description',
                $parameters,
                'modifiers',
                $language
            );

            if ($effect) {
                if (!in_array($effect, $effects)) {
                    $effects[] = $effect;
                }
            }
        }

        if (!empty($effects)) {
            foreach ($effects as $effect) {
                $description = $description . '//' . $effect;
            }
        }

        return $description;
    }

    private function getTriggerEventModifierEffects(DiseaseConfig $diseaseConfig, string $description, string $language): string
    {
        $effects = [];
        /** @var TriggerEventModifierConfig $modifierConfig */
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            if (!$modifierConfig instanceof TriggerEventModifierConfig) {
                continue;
            }

            $eventConfig = $modifierConfig->getTriggeredEvent();
            if (!$eventConfig instanceof VariableEventConfig) {
                return '';
            }

            $quantity = $eventConfig->getQuantity();
            $target = $eventConfig->getTargetVariable();

            if ($quantity < 0) {
                $key = $modifierConfig->getTargetEvent() . '_decrease';
            } else {
                $key = $modifierConfig->getTargetEvent() . '_increase';
            }

            $emoteMap = PlayerVariableEnum::getEmoteMap();
            if (isset($emoteMap[$target])) {
                $emote = $emoteMap[$target];
            } else {
                $emote = '';
            }

            $chance = $this->getModifierChance($modifierConfig);
            $action = $this->getModifierActions($modifierConfig, $language);

            $parameters = [
                'action_name' => $action,
                'chance' => $chance,
                'emote' => $emote,
                'quantity' => abs($quantity),
            ];

            $effect = $this->translationService->translate(
                $key . '.description',
                $parameters,
                'modifiers',
                $language
            );

            if ($effect) {
                if (!in_array($effect, $effects)) {
                    $effects[] = $effect;
                }
            }
        }

        if (!empty($effects)) {
            foreach ($effects as $effect) {
                $description = $description . '//' . $effect;
            }
        }

        return $description;
    }

    private function getModifierChance(AbstractModifierConfig $modifierConfig): int
    {
        $randomActivationRequirement = $modifierConfig->getModifierActivationRequirements()
                ->filter(fn (ModifierActivationRequirement $activationRequirement) => $activationRequirement->getActivationRequirementName() === SymptomActivationRequirementEnum::RANDOM);
        if (!$randomActivationRequirement->isEmpty()) {
            return $randomActivationRequirement->first()->getValue();
        } else {
            return 100;
        }
    }

    private function getModifierActions(EventModifierConfig $modifierConfig, string $language): ?string
    {
        $actions = [];

        $tagConstraints = $modifierConfig->getTagConstraints();
        foreach (array_keys($tagConstraints) as $action) {
            if ($tagConstraints[$action] !== ModifierRequirementEnum::NONE_TAGS
                && ActionEnum::getAll()->contains($action)
            ) {
                $action = $this->translateAction($action, $language);
                if (!in_array($action, $actions)) {
                    $actions[] = $action;
                }
            }
        }

        return implode(', ', $actions);
    }

    private function translateAction(?string $action, string $language): string
    {
        if ($action) {
            return $this->translationService->translate($action . '.name', [], 'actions', $language);
        } else {
            return '';
        }
    }
}
