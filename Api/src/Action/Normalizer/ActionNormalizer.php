<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Actions\AttemptAction;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionTypeEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class ActionNormalizer implements ContextAwareNormalizerInterface
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

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $actionClass = $this->actionStrategyService->getAction($object->getName());
        if (!$actionClass) {
            return [];
        }

        if (!($currentPlayer = $context['currentPlayer'] ?? null)) {
            throw new \LogicException('Current player is missing from context');
        }

        $parameter = $this->loadParameters($context);

        $actionClass->loadParameters($object, $currentPlayer, $parameter);

        if ($actionClass->isVisible()) {
            $actionName = $object->getName();

            $normalizedAction = [
                'id' => $object->getId(),
                'key' => $object->getName(),
                'name' => $this->translationService->translate("{$actionName}.name", [], 'actions'),
                'actionPointCost' => $this->actionService->getTotalActionPointCost($currentPlayer, $object, $parameter),
                'movementPointCost' => $this->actionService->getTotalMovementPointCost($currentPlayer, $object, $parameter),
                'moralPointCost' => $this->actionService->getTotalMoralPointCost($currentPlayer, $object, $parameter),
                ];

            if ($actionClass instanceof AttemptAction) {
                $normalizedAction['successRate'] = $actionClass->getSuccessRate();
            } else {
                $normalizedAction['successRate'] = 100;
            }

            if ($reason = $actionClass->cannotExecuteReason()) {
                $normalizedAction['description'] = $this->translationService->translate("{$reason}.description", [], 'action_fail');
                $normalizedAction['canExecute'] = false;
            } else {
                $description = $this->translationService->translate("{$actionName}.description", [], 'actions');
                $description = $this->getTypesDescriptions($description, $object->getTypes());
                $normalizedAction['description'] = $description;
                $normalizedAction['canExecute'] = true;
            }

            return $normalizedAction;
        }

        return [];
    }

    private function loadParameters(array $context): ?LogParameterInterface
    {
        $parameter = null;
        if (array_key_exists('player', $context)) {
            $parameter = $context['player'];
        }
        if (array_key_exists('door', $context)) {
            $parameter = $context['door'];
        }
        if (array_key_exists('item', $context)) {
            $parameter = $context['item'];
        }
        if (array_key_exists('equipment', $context)) {
            $parameter = $context['equipment'];
        }

        return $parameter;
    }

    private function getTypesDescriptions(string $description, array $types): string
    {
        foreach ($types as $type) {
            if (key_exists($type, self::ACTION_TYPE_DESCRIPTION_MAP)) {
                $key = self::ACTION_TYPE_DESCRIPTION_MAP[$type];
                $description = $description . '//' . $this->translationService->translate($key . '.description', [], 'actions');
            }
        }

        return $description;
    }
}
