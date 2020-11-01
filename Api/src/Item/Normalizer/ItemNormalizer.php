<?php

namespace Mush\Item\Normalizer;

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
     * @param string|null $format
     * @param array $context
     * @return array
     */
    public function normalize($item, string $format = null, array $context = [])
    {
        $actions = [];
        $actionParameter = new ActionParameters();
        $actionParameter
            ->setItem($item)
        ;

        foreach ($item->getActions() as $action) {
            if ($this->actionService->canExecuteAction($this->getUser()->getCurrentGame(), $action, $actionParameter))
                $actions[] = [
                    'key' => $action,
                    'name' => $this->translator->trans("{$action}.name", [], 'actions'),
                    'description' => $this->translator->trans("{$action}.description", [], 'actions')
                ];
        }

        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'statuses' => $item->getStatuses(),
            'actions' => $actions
        ];
    }


    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
