<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActionNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;
    private ActionServiceInterface $actionService;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TranslatorInterface $translator,
        ActionServiceInterface $actionService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->actionService = $actionService;
        $this->tokenStorage = $tokenStorage;
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

        $actionClass->loadParameters($object, $this->getCurrentPlayer(), $actionParameter);

        if ($actionClass->canExecute()) {
            $actionName = $object->getName();

            return [
                'id' => $object->getId(),
                'key' => $actionName,
                'name' => $this->translator->trans("{$actionName}.name", [], 'actions'),
                'description' => $this->translator->trans("{$actionName}.description", [], 'actions'),
                'actionPointCost' => $object->getActionCost()->getActionPointCost(),
                'movementPointCost' => $object->getActionCost()->getMovementPointCost(),
                'moralPointCost' => $object->getActionCost()->getMoralPointCost(),
            ];
        }

        return [];
    }

    private function getCurrentPlayer(): Player
    {
        if (!$token = $this->tokenStorage->getToken()) {
            throw new AccessDeniedException('User should be logged to access that');
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$player = $user->getCurrentGame()) {
            throw new AccessDeniedException('User should be in game to access that');
        }

        return $player;
    }
}
