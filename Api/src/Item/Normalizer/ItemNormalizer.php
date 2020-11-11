<?php

namespace Mush\Item\Normalizer;

use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Item\Entity\GameItem;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ItemNormalizer implements ContextAwareNormalizerInterface
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

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof GameItem;
    }

    /**
     * @param GameItem $item
     *
     * @return array
     */
    public function normalize($item, string $format = null, array $context = [])
    {
        $actions = [];
        $actionParameter = new ActionParameters();
        $actionParameter
            ->setItem($item)
        ;

        foreach ($item->getActions() as $actionName) {
            $actionClass = $this->actionService->getAction($actionName);
            if ($actionClass instanceof Action) {
                $actionClass->loadParameters($this->getUser()->getCurrentGame(), $actionParameter);
                if ($actionClass->canExecute()) {
                    $actions[] = [
                        'key' => $actionName,
                        'name' => $this->translator->trans("{$actionName}.name", [], 'actions'),
                        'description' => $this->translator->trans("{$actionName}.description", [], 'actions'),
                        'actionPointCost' => $actionClass->getActionCost()->getActionPointCost(),
                        'movementPointCost' => $actionClass->getActionCost()->getMovementPointCost(),
                        'moralPointCost' => $actionClass->getActionCost()->getMoralPointCost(),
                    ];
                }
            }
        }

        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'statuses' => $item->getStatuses(),
            'actions' => $actions,
        ];
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
