<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\AttemptAction;
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
    private const ACTION_TYPE_DESCRIPTION_MAP = [
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
                'shootPointCost' => $this->getActionShootPointCost($currentPlayer, $object),
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
        $parameters = [];
        $parameters['actionTarget'] = $this->getActionTargetFromContextService->execute($context);

        return $parameters;
    }

    private function getTypesDescriptions(string $description, array $types, ?string $language = null): string
    {
        foreach ($types as $type) {
            if (\array_key_exists($type, self::ACTION_TYPE_DESCRIPTION_MAP)) {
                $key = self::ACTION_TYPE_DESCRIPTION_MAP[$type];
                $description = $description . '//' . $this->translationService->translate($key . '.description', [], 'actions', $language);
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

    /** @TODO: generalize this for all specialist points. */
    private function getActionShootPointCost(Player $currentPlayer, Action $action): ?int
    {
        if (!$this->isShootAction($action)) {
            return null;
        }

        /** @var ?ChargeStatus $shooterSkill */
        $shooterSkill = $currentPlayer->getSkillByName(SkillEnum::SHOOTER);
        if ($shooterSkill?->getCharge() > 0) {
            return 1;
        }

        return null;
    }

    private function isShootAction(Action $action): bool
    {
        return \in_array(ActionTypeEnum::ACTION_SHOOT, $action->getTypes(), true) || \in_array(ActionTypeEnum::ACTION_SHOOT_HUNTER, $action->getTypes(), true);
    }

    private function getNormalizedSpecialistPointCosts(Player $currentPlayer, Action $action): array
    {
        $specialistPointCostRules = [
            'shootPoint' => [
                'Skill' => SkillEnum::SHOOTER,
                'CompatibleActionTypes' => [ ActionTypeEnum::ACTION_SHOOT, ActionTypeEnum::ACTION_SHOOT_HUNTER ]
            ],
            'engineerPoint' => [
                'Skill' => SkillEnum::TECHNICIAN,
                'CompatibleActionTypes' => [ ActionTypeEnum::ACTION_TECHNICIAN ]
            ]
        ];

        $specialistPointCosts = [];
        foreach ($specialistPointCostRules as $key => $value)
        {
            $specialistPointCost = $this->getSpecialistPointCost($currentPlayer, $action, $value);
            if ($specialistPointCost)
            {
                $specialistPointCosts[] = $key;
            }
        }

        return $specialistPointCosts;
    }

    private function getSpecialistPointCost(Player $currentPlayer, Action $action, array $specialistPointCostRule): ?int
    {
        if (!$this->isActionTypeMatchingWith($action, $specialistPointCostRule['CompatibleActionTypes'])) {
            return null;
        }

        /** @var ?ChargeStatus $shooterSkill */
        $skill = $currentPlayer->getSkillByName($specialistPointCostRule['Skill']);
        if ($skill?->getCharge() > 0) {
            return 1;
        }

        return null;
    }

    /**
     * @param Action $action
     * @param array<string> $types
     * @return bool
     */
    private function isActionTypeMatchingWith(Action $action, array $types): bool
    {
        foreach ($types as $type)
        {
            if (\in_array($type, $action->getTypes(), true)) {
                return true;
            }
        }

        return false;
    }
}
