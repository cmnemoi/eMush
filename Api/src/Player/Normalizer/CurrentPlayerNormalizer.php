<?php

namespace Mush\Player\Normalizer;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class CurrentPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TokenStorageInterface $tokenStorage;
    private ActionServiceInterface $actionService;
    private TranslatorInterface $translator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ActionServiceInterface $actionService,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->actionService = $actionService;
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Player && $data === $this->getUserPlayer();
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $player = $object;

        $items = [];
        /** @var GameItem $item */
        foreach ($player->getItems() as $item) {
            $items[] = $this->normalizer->normalize($item);
        }

        $statuses = [];
        foreach ($player->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, null, ['player' => $player]);
            if (is_array($normedStatus) && count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        return [
            'id' => $player->getId(),
            'character' => [
                'key' => $player->getPerson(),
                'value' => $this->translator->trans($player->getPerson() . '.name', [], 'characters'),
            ],
            'daedalus' => $this->normalizer->normalize($object->getDaedalus()),
            'room' => $this->normalizer->normalize($object->getRoom()),
            'skills' => $player->getSkills(),
            'actions' => $this->getActions($object),
            'items' => $items,
            'statuses' => $statuses,
            'actionPoint' => $player->getActionPoint(),
            'movementPoint' => $player->getMovementPoint(),
            'healthPoint' => $player->getHealthPoint(),
            'moralPoint' => $player->getMoralPoint(),
            'triumph' => $player->getTriumph(),
        ];
    }

    private function getActions(Player $player): array
    {
        //Handle tools
        $tools = $player->getReachableTools()
            ->filter(function (GameEquipment $gameEquipment) {
                /** @var Tool $tool */
                $tool = $gameEquipment->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL);

                return !$tool->getGrantActions()->isEmpty();
            })
        ;

        $actions = [];
        $playerActions = ActionEnum::getPermanentSelfActions();

        foreach ($tools as $tool) {
            $toolActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions();
            $toolTargets = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getActionsTarget();

            foreach ($toolActions as $actionName) {
                if ($toolTargets[$actionName] === ActionTargetEnum::DOOR) {
                    $playerActions[] = $actionName;
                }
            }
        }

        foreach ($playerActions as $actionName) {
            $actionClass = $this->actionService->getAction($actionName);
            if ($actionClass) {
                $normedAction = $this->normalizer->normalize($actionClass);
                if (is_array($normedAction) && count($normedAction) > 0) {
                    $actions[] = $normedAction;
                }
            }
        }

        return $actions;
    }

    private function getUserPlayer(): Player
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
