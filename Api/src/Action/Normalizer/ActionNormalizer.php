<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\AttemptAction;
use Mush\Action\DTO\ActionSpecialistPointRule;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Mush\Action\Service\GetActionTargetFromContextService;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActionNormalizer implements NormalizerInterface
{
    private const array ACTION_TYPE_DESCRIPTION_MAP = [
        ActionTypeEnum::ACTION_AGGRESSIVE => ActionTypeEnum::ACTION_AGGRESSIVE,
        VisibilityEnum::COVERT => VisibilityEnum::COVERT,
        VisibilityEnum::SECRET => VisibilityEnum::SECRET,
    ];
    private TranslationServiceInterface $translationService;
    private ActionStrategyServiceInterface $actionStrategyService;
    private ActionServiceInterface $actionService;
    private GetActionTargetFromContextService $getActionTargetFromContextService;
    private PlanetServiceInterface $planetService;

    public function __construct(
        TranslationServiceInterface $translationService,
        ActionStrategyServiceInterface $actionStrategyService,
        ActionServiceInterface $actionService,
        GetActionTargetFromContextService $getActionTargetFromContextService,
        PlanetServiceInterface $planetService
    ) {
        $this->translationService = $translationService;
        $this->actionStrategyService = $actionStrategyService;
        $this->actionService = $actionService;
        $this->getActionTargetFromContextService = $getActionTargetFromContextService;
        $this->planetService = $planetService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Action && empty($context['groups']);
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $actionClass = $this->actionStrategyService->getAction($object->getActionName());
        if (!$actionClass) {
            return [];
        }

        $actionName = $object->getActionName();

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        $language = $currentPlayer->getDaedalus()->getLanguage();

        $parameters = $this->loadParameters($context);

        /** @var ?LogParameterInterface $actionTarget */
        $actionTarget = $parameters['actionTarget'];

        $actionClass->loadParameters($object, $currentPlayer, $actionTarget, $parameters);

        // translation parameters
        $translationParameters = $this->getTranslationParameters($actionClass, $currentPlayer, $actionTarget);

        if ($actionClass->isVisible()) {
            $normalizedAction = [
                'id' => $object->getId(),
                'key' => $object->getActionName(),
                'name' => $this->translationService->translate(
                    "{$actionName}.name",
                    $translationParameters,
                    'actions',
                    $language
                ),
                'actionPointCost' => $this->actionService->getActionModifiedActionVariable(
                    $currentPlayer,
                    $object,
                    $actionTarget,
                    PlayerVariableEnum::ACTION_POINT,
                ),
                'movementPointCost' => $this->actionService->getActionModifiedActionVariable(
                    $currentPlayer,
                    $object,
                    $actionTarget,
                    PlayerVariableEnum::MOVEMENT_POINT,
                ),
                'moralPointCost' => $this->actionService->getActionModifiedActionVariable(
                    $currentPlayer,
                    $object,
                    $actionTarget,
                    PlayerVariableEnum::MORAL_POINT,
                ),
                'specialistPointCosts' => $this->getNormalizedSpecialistPointCosts($currentPlayer, $object),
            ];

            if ($actionClass instanceof AttemptAction) {
                $normalizedAction['successRate'] = $actionClass->getSuccessRate();
            } else {
                $normalizedAction['successRate'] = 100;
            }

            if ($reason = $actionClass->cannotExecuteReason()) {
                $normalizedAction['description'] = $this->translationService->translate(
                    "{$reason}.description",
                    $translationParameters,
                    'action_fail',
                    $language
                );
                $normalizedAction['canExecute'] = false;
            } else {
                $description = $this->translationService->translate(
                    "{$actionName}.description",
                    $translationParameters,
                    'actions',
                    $language
                );
                $description = $this->getTypesDescriptions($description, $object->getTypes(), $language);
                $normalizedAction['description'] = $description;
                $normalizedAction['canExecute'] = true;
                $normalizedAction['confirmation'] =
                    \in_array(ActionTypeEnum::ACTION_CONFIRM, $object->getTypes(), true)
                    ? $this->translationService->translate(
                        "{$actionName}.confirmation",
                        $translationParameters,
                        'actions',
                        $language
                    )
                    : null;
            }

            return $normalizedAction;
        }

        return [];
    }

    private function loadParameters(array $context): array
    {
        return [
            'actionTarget' => $this->getActionTargetFromContextService->execute($context),
        ];
    }

    private function getTypesDescriptions(string $description, array $types, ?string $language = null): string
    {
        foreach ($types as $type) {
            if (\array_key_exists($type, self::ACTION_TYPE_DESCRIPTION_MAP)) {
                $key = self::ACTION_TYPE_DESCRIPTION_MAP[$type];
                $description .= '//' . $this->translationService->translate($key . '.description', [], 'actions', $language);
            }
        }

        return $description;
    }

    private function getTranslationParameters(AbstractAction $actionClass, Player $currentPlayer, ?LogParameterInterface $actionTarget): array
    {
        $actionName = $actionClass->getActionName();
        $daedalus = $currentPlayer->getDaedalus();

        $translationParameters = [$currentPlayer->getLogKey() => $currentPlayer->getLogName()];

        if ($actionName === ActionEnum::EXTRACT_SPORE) {
            $translationParameters['quantity'] = $daedalus->getVariableByName(DaedalusVariableEnum::SPORE)->getMaxValue();
        }
        if ($actionTarget instanceof Player) {
            $translationParameters['target.' . $actionTarget->getLogKey()] = $actionTarget->getLogName();
        }
        if (ActionEnum::getTakeOffToPlanetActions()->contains($actionName)) {
            $inOrbitPlanet = $this->planetService->findPlanetInDaedalusOrbit($daedalus);
            if ($inOrbitPlanet) {
                $translationParameters['planet'] = $this->translationService->translate(
                    key: 'planet_name',
                    parameters: $inOrbitPlanet->getName()->toArray(),
                    domain: 'planet',
                    language: $daedalus->getLanguage()
                );
            }
        }

        return $translationParameters;
    }

    private function getNormalizedSpecialistPointCosts(Player $currentPlayer, Action $action): array
    {
        /** @var ActionSpecialistPointRule[] $specialistPointCostRules */
        $specialistPointCostRules = [
            new ActionSpecialistPointRule('shoot', SkillEnum::SHOOTER, [ActionTypeEnum::ACTION_SHOOT, ActionTypeEnum::ACTION_SHOOT_HUNTER]),
            new ActionSpecialistPointRule('engineer', SkillEnum::TECHNICIAN, [ActionTypeEnum::ACTION_TECHNICIAN]),
            new ActionSpecialistPointRule('core', SkillEnum::CONCEPTOR, [ActionTypeEnum::ACTION_CONCEPTOR]),
        ];

        $specialistPointCosts = [];
        foreach ($specialistPointCostRules as $specialistPointCostRule) {
            $specialistPointCost = $this->getSpecialistPointCost($currentPlayer, $action, $specialistPointCostRule);
            if ($specialistPointCost) {
                $specialistPointCosts[] = $specialistPointCostRule->name;
            }
        }

        return $specialistPointCosts;
    }

    /**
     * Check how many specialist points the user will be charged for.
     */
    private function getSpecialistPointCost(Player $currentPlayer, Action $action, ActionSpecialistPointRule $specialistPointCostRule): ?int
    {
        if (!$this->doesActionTypeMatchArray($action, $specialistPointCostRule->actionTypes)) {
            return null;
        }

        /** @var ?ChargeStatus $skill */
        $skill = $currentPlayer->getSkillByName($specialistPointCostRule->skill);

        if ($skill?->getCharge() > 0 && $currentPlayer->hasSkill($specialistPointCostRule->skill)) {
            return 1;
        }

        return null;
    }

    /**
     * @param array<string> $types
     */
    private function doesActionTypeMatchArray(Action $action, array $types): bool
    {
        foreach ($types as $type) {
            if (\in_array($type, $action->getTypes(), true)) {
                return true;
            }
        }

        return false;
    }
}
