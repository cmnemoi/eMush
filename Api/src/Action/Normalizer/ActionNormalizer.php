<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\AttemptAction;
use Mush\Action\Actions\PrintZeList;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Mush\Action\Service\GetActionTargetFromContextService;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Entity\Skill;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActionNormalizer implements NormalizerInterface
{
    private const array ACTION_TYPE_DESCRIPTION_MAP = [
        ActionTypeEnum::ACTION_AGGRESSIVE->value => ActionTypeEnum::ACTION_AGGRESSIVE->value,
        VisibilityEnum::COVERT => VisibilityEnum::COVERT,
        VisibilityEnum::SECRET => VisibilityEnum::SECRET,
    ];

    private const array COST_POINT_MAP = [
        'actionPointCost' => PlayerVariableEnum::ACTION_POINT,
        'movementPointCost' => PlayerVariableEnum::MOVEMENT_POINT,
        'moralPointCost' => PlayerVariableEnum::MORAL_POINT,
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

    public function getSupportedTypes(?string $format): array
    {
        return [
            Action::class => false,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var ActionConfig $actionConfig */
        $actionConfig = $object->getActionConfig();

        /** @var ActionProviderInterface $actionProvider */
        $actionProvider = $object->getActionProvider();
        $actionClass = $this->actionStrategyService->getAction($actionConfig->getActionName());

        if (!$actionClass) {
            return [];
        }

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        $parameters = ['actionTarget' => $this->getActionTargetFromContextService->execute($context)];

        /** @var ?LogParameterInterface $actionTarget */
        $actionTarget = $parameters['actionTarget'];

        if ($actionClass->support($actionTarget, $parameters)) {
            $actionClass->loadParameters(
                $actionConfig,
                $actionProvider,
                $currentPlayer,
                $actionTarget,
                $parameters
            );

            if ($actionClass->isVisible()) {
                $normalizedAction = [
                    'id' => $actionConfig->getId(),
                    'key' => $actionConfig->getActionName()->value,
                    'actionProvider' => [
                        'id' => $actionProvider->getId(),
                        'class' => $actionProvider->getClassName(),
                    ],
                ];

                $normalizedAction = $this->normalizeCost(
                    $normalizedAction,
                    $currentPlayer,
                    $actionClass,
                );

                if ($actionClass instanceof AttemptAction) {
                    $normalizedAction['successRate'] = $actionClass->getSuccessRate();
                } else {
                    $normalizedAction['successRate'] = 100;
                }

                return $this->normalizeDescription(
                    $normalizedAction,
                    $currentPlayer,
                    $actionTarget,
                    $actionClass
                );
            }
        }

        return [];
    }

    private function normalizeCost(
        array $normalizedAction,
        Player $currentPlayer,
        AbstractAction $actionClass,
    ): array {
        $actionConfig = $actionClass->getActionConfig();
        foreach (self::COST_POINT_MAP as $key => $pointName) {
            $normalizedAction[$key] = $this->actionService->getActionModifiedActionVariable(
                $currentPlayer,
                $actionConfig,
                $actionClass->getActionProvider(),
                $actionClass->getTarget(),
                $pointName,
                $actionClass->getTags()
            );
        }

        $normalizedAction['skillPointCosts'] = $this->getNormalizedSkillPointCosts($currentPlayer, $actionConfig);

        return $normalizedAction;
    }

    private function normalizeDescription(
        array $normalizedAction,
        Player $currentPlayer,
        ?LogParameterInterface $actionTarget,
        AbstractAction $actionClass
    ): array {
        $actionName = $actionClass->getActionName();
        $actionConfig = $actionClass->getActionConfig();

        // translation parameters
        $language = $currentPlayer->getDaedalus()->getLanguage();
        $translationParameters = $this->getTranslationParameters($actionClass, $currentPlayer, $actionTarget);

        $normalizedAction['name'] = $this->translationService->translate(
            "{$actionName}.name",
            $translationParameters,
            'actions',
            $language
        );

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
            $description = $this->getTypesDescriptions($description, $actionConfig->getTypes(), $language);
            $normalizedAction['description'] = $description;
            $normalizedAction['canExecute'] = true;
            $normalizedAction['confirmation']
                = \in_array(ActionTypeEnum::ACTION_CONFIRM->value, $actionConfig->getTypes(), true)
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
        $actionProvider = $actionClass->getActionProvider();

        $translationParameters = [
            $currentPlayer->getLogKey() => $currentPlayer->getLogName(),
            $actionProvider->getLogKey() => $actionProvider->getLogName(),
            'outputQuantity' => $actionClass->getOutputQuantity(),
        ];

        if ($actionTarget) {
            $translationParameters['target_' . $actionTarget->getLogKey()] = $actionTarget->getLogName();
        }
        if ($actionName === ActionEnum::EXTRACT_SPORE->value) {
            $sporeGameVariable = $daedalus->getVariableByName(DaedalusVariableEnum::SPORE);

            $translationParameters['quantity_maximum'] = $sporeGameVariable->getMaxValue();
            $translationParameters['quantity_remaining'] = $sporeGameVariable->getMaxValue() - $sporeGameVariable->getValue();
        }
        if ($actionName === ActionEnum::PRINT_ZE_LIST->value) {
            /** @var PrintZeList $printZeListAction */
            $printZeListAction = $actionClass;
            $translationParameters['numberOfNames'] = $printZeListAction->numberOfNames();
        }
        if (ActionEnum::getTakeOffToPlanetActions()->contains($actionName)) {
            $translationParameters['planet'] = $this->getTranslatedInOrbitPlanet($currentPlayer);
        }

        return $translationParameters;
    }

    private function getNormalizedSkillPointCosts(Player $currentPlayer, ActionConfig $action): array
    {
        $skillPointCosts = [];

        /** @var Skill $skill */
        foreach ($currentPlayer->getSkillsWithPoints() as $skill) {
            if ($skill->hasAnyActionTypes($action->getTypes())) {
                $skillPointCosts[] = $skill->getSkillPointsName();
            }
        }

        return $this->prioritizeCorePointsOverITPoints($skillPointCosts);
    }

    private function getTranslatedInOrbitPlanet(Player $currentPlayer): ?string
    {
        $daedalus = $currentPlayer->getDaedalus();
        $inOrbitPlanet = $this->planetService->findPlanetInDaedalusOrbit($daedalus);

        return $inOrbitPlanet
            ? $this->translationService->translate(
                key: 'planet_name',
                parameters: $inOrbitPlanet->getName()->toArray(),
                domain: 'planet',
                language: $daedalus->getLanguage()
            ) : null;
    }

    private function prioritizeCorePointsOverITPoints(array $skillPointCosts): array
    {
        $itIndex = array_search('computer', $skillPointCosts, true);
        $coreIndex = array_search('core', $skillPointCosts, true);
        if ($coreIndex && $itIndex !== false && $coreIndex > $itIndex) {
            [$skillPointCosts[$itIndex], $skillPointCosts[$coreIndex]] = [$skillPointCosts[$coreIndex], $skillPointCosts[$itIndex]];
        }

        return $skillPointCosts;
    }
}
