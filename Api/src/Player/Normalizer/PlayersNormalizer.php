<?php

namespace Mush\Player\Normalizer;

use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionTargetEnum;
use Mush\Action\Normalizer\ActionNormalizer;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Player\Entity\Player;
use Mush\Status\Normalizer\StatusNormalizer;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayersNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;
    private TokenStorageInterface $tokenStorage;
    private EquipmentNormalizer $equipmentNormalizer;
    private StatusNormalizer $statusNormalizer;
    private ActionNormalizer $actionNormalizer;
    private ActionServiceInterface $actionService;

    public function __construct(
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage,
        EquipmentNormalizer $equipmentNormalizer,
        StatusNormalizer $statusNormalizer,
        ActionNormalizer $actionNormalizer,
        ActionServiceInterface $actionService
    ) {
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->equipmentNormalizer = $equipmentNormalizer;
        $this->statusNormalizer = $statusNormalizer;
        $this->actionNormalizer = $actionNormalizer;
        $this->actionService = $actionService;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Player;
    }

    /**
     * @param Player $player
     *
     * @return array
     */
    public function normalize($player, string $format = null, array $context = [])
    {
        $statuses = [];
        foreach ($player->getStatuses() as $status) {
            $normedStatus = $this->statusNormalizer->normalize($status, null, ['player' => $player]);
            if (count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        $actionParameter = new ActionParameters();
        $actionParameter->setPlayer($player);

        $actions = [];
        if ($this->getUser()->getCurrentGame() === $player) {
            foreach (ActionEnum::getPermanentSelfActions() as $actionName) {
                $actionclass = $this->actionService->getAction($actionName);
                if ($actionclass) {
                    $normedAction = $this->actionNormalizer->normalize($actionclass);
                    if (count($normedAction) > 0) {
                        $actions[] = $normedAction;
                    }
                }
            }
        } else {
            foreach (ActionEnum::getPermanentPlayerActions() as $actionName) {
                $actionclass = $this->actionService->getAction($actionName);
                if ($actionclass) {
                    $normedAction = $this->actionNormalizer->normalize($actionclass, null, ['player' => $player]);
                    if (count($normedAction) > 0) {
                        $actions[] = $normedAction;
                    }
                }
            }
        }

        //Handle tools
        $tools = $this->getUser()->getCurrentGame()->getReachableTools()
            ->filter(fn (GameEquipment $gameEquipment) => count($gameEquipment->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()) > 0);

        foreach ($tools as $tool) {
            if ($this->getUser()->getCurrentGame() === $player) {
                $playerActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                        ->filter(fn (string $actionName) => $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                        ->getActionsTarget()[$actionName] === ActionTargetEnum::SELF_PLAYER);
                $context = [];
            } else {
                $playerActions = $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)->getGrantActions()
                        ->filter(fn (string $actionName) => $tool->GetEquipment()->getMechanicByName(EquipmentMechanicEnum::TOOL)
                        ->getActionsTarget()[$actionName] === ActionTargetEnum::TARGET_PLAYER);
                $context = ['player' => $player];
            }
            foreach ($playerActions as $actionName) {
                $actionClass = $this->actionService->getAction($actionName);
                if ($actionClass) {
                    $normedAction = $this->actionNormalizer->normalize($actionClass, null, $context);
                    if (count($normedAction) > 0) {
                        $actions[] = $normedAction;
                    }
                }
            }
        }

        $playerPersonalInfo = [];
        if ($this->getUser()->getCurrentGame() === $player) {
            $items = [];
            /** @var GameItem $item */
            foreach ($player->getItems() as $item) {
                $items[] = $this->equipmentNormalizer->normalize($item);
            }

            $playerPersonalInfo = [
                'items' => $items,
                'actionPoint' => $player->getActionPoint(),
                'movementPoint' => $player->getMovementPoint(),
                'healthPoint' => $player->getHealthPoint(),
                'moralPoint' => $player->getMoralPoint(),
                'triumph' => $player->getTriumph(),
            ];
        }

        return array_merge([
            'id' => $player->getId(),
            'character' => [
                'key' => $player->getPerson(),
                'value' => $this->translator->trans($player->getPerson() . '.name', [], 'characters'),
            ],
            'statuses' => $statuses,
            'skills' => $player->getSkills(),
            'actions' => $actions,
        ], $playerPersonalInfo);
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
