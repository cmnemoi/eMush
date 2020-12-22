<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\ActionServiceInterface;
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

        $object->loadParameters($this->getUser()->getCurrentGame(), $actionParameter);

        if ($object->canExecute()) {
            $actionName = $object->getActionName();

            return [
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

    private function getUser(): User
    {
        if (!($token = $this->tokenStorage->getToken())) {
            throw new AccessDeniedException('User should be logged');
        }

        /** @var User $user */
        $user = $token->getUser();

        return $user;
    }
}
