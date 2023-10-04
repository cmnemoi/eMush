<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Actions\AttemptAction;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActionNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;
    private ActionStrategyServiceInterface $actionStrategyService;
    private ActionServiceInterface $actionService;

    private const ACTION_TYPE_DESCRIPTION_MAP = [
        ActionTypeEnum::ACTION_AGGRESSIVE => ActionTypeEnum::ACTION_AGGRESSIVE,
        VisibilityEnum::COVERT => VisibilityEnum::COVERT,
        VisibilityEnum::SECRET => VisibilityEnum::SECRET,
    ];

    public function __construct(
        TranslationServiceInterface $translationService,
        ActionStrategyServiceInterface $actionStrategyService,
        ActionServiceInterface $actionService
    ) {
        $this->translationService = $translationService;
        $this->actionStrategyService = $actionStrategyService;
        $this->actionService = $actionService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Action && empty($context['groups']);
    }

    public function normalize($object, string $format = null, array $context = []): array
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
        $translationParameters = [$currentPlayer->getLogKey() => $currentPlayer->getLogName()];
        if ($actionName === ActionEnum::EXTRACT_SPORE) {
            $translationParameters['quantity'] = $currentPlayer->getDaedalus()->getVariableByName(DaedalusVariableEnum::SPORE)->getMaxValue();
        }
        if ($actionTarget instanceof Player) {
            $translationParameters['target.' . $actionTarget->getLogKey()] = $actionTarget->getLogName();
        }

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
            }

            return $normalizedAction;
        }

        return [];
    }

    private function loadParameters(array $context): array
    {
        $parameters = [];
        $actionTarget = null;
        if (array_key_exists('player', $context)) {
            $actionTarget = $context['player'];
        }
        if (array_key_exists('door', $context)) {
            $actionTarget = $context['door'];
        }
        if (array_key_exists('item', $context)) {
            $actionTarget = $context['item'];
        }
        if (array_key_exists('equipment', $context)) {
            $actionTarget = $context['equipment'];
        }
        if (array_key_exists('hunter', $context)) {
            $actionTarget = $context['hunter'];
        }
        if (array_key_exists('terminal', $context)) {
            $actionTarget = $context['terminal'];
        }

        $parameters['actionTarget'] = $actionTarget;

        return $parameters;
    }

    private function getTypesDescriptions(string $description, array $types, string $language = null): string
    {
        foreach ($types as $type) {
            if (key_exists($type, self::ACTION_TYPE_DESCRIPTION_MAP)) {
                $key = self::ACTION_TYPE_DESCRIPTION_MAP[$type];
                $description = $description . '//' . $this->translationService->translate($key . '.description', [], 'actions', $language);
            }
        }

        return $description;
    }
}
