<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Actions\AttemptAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActionNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;
    private ActionStrategyServiceInterface $actionService;

    public function __construct(
        TranslatorInterface $translator,
        ActionStrategyServiceInterface $actionService
    ) {
        $this->translator = $translator;
        $this->actionService = $actionService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Action;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $actionClass = $this->actionService->getAction($object->getName());
        if (!$actionClass) {
            return [];
        }

        if (!($currentPlayer = $context['currentPlayer'] ?? null)) {
            throw new \LogicException('Current player is missing from context');
        }

        $actionParameter = new ActionParameters();
        if (array_key_exists('player', $context)) {
            $actionParameter->setPlayer($context['player']);
        }
        if (array_key_exists('door', $context)) {
            $actionParameter->setDoor($context['door']);
        }
        if (array_key_exists('item', $context)) {
            $actionParameter->setItem($context['item']);
        }
        if (array_key_exists('equipment', $context)) {
            $actionParameter->setEquipment($context['equipment']);
        }

        $actionClass->loadParameters($object, $currentPlayer, $actionParameter);

        if ($actionClass->getActionCost()->canPlayerDoAction($currentPlayer) && $actionClass->canExecute()) {
            $actionName = $object->getName();
            $actionCost = $actionClass->getActionCost();

            $successRate = $object->getSuccessRate();

            if ($actionClass instanceof AttemptAction) {
                $successRate = $actionClass->getSuccessRate();
            }

            return [
                'id' => $object->getId(),
                'key' => $actionName,
                'name' => $this->translator->trans("{$actionName}.name", [], 'actions'),
                'description' => $this->translator->trans("{$actionName}.description", [], 'actions'),
                'actionPointCost' => $actionCost->getActionPointCost(),
                'movementPointCost' => $actionCost->getMovementPointCost(),
                'moralPointCost' => $actionCost->getMoralPointCost(),
                'successRate' => $successRate,
            ];
        }

        return [];
    }
}
