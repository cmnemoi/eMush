<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Search extends Action
{
    protected string $name = ActionEnum::SEARCH;

    private ?GameItem $itemFound = null;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;

        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        $this->player = $player;
    }

    public function canExecute(): bool
    {
        //@TODO add condition on the room
        return true;
    }

    protected function applyEffects(): ActionResult
    {
        $hiddenItems = $this->player
            ->getRoom()
            ->getEquipments()
            ->filter(
                fn (GameEquipment $gameEquipment) => ($gameEquipment->getStatusByName(EquipmentStatusEnum::HIDDEN) !== null)
            )
        ;

        if (!$hiddenItems->isEmpty()) {
            /** @var GameItem $mostRecentHiddenItem */
            $mostRecentHiddenItem = $this->statusService
                ->getMostRecent(EquipmentStatusEnum::HIDDEN, $hiddenItems)
            ;

            if (!($hiddenStatus = $mostRecentHiddenItem->getStatusByName(EquipmentStatusEnum::HIDDEN)) ||
                !($hiddenBy = $hiddenStatus->getPlayer())
            ) {
                throw new \LogicException('invalid hidden status');
            }

            $this->itemFound = $mostRecentHiddenItem;
            $this->itemFound->removeStatus($hiddenStatus);

            $hiddenBy->removeStatus($hiddenStatus);

            $this->playerService->persist($hiddenBy);
            $this->gameEquipmentService->persist($mostRecentHiddenItem);

            return new Success();
        } else {
            return new Fail();
        }
    }

    protected function createLog(ActionResult $actionResult): void
    {
        if ($actionResult instanceof Success && $this->itemFound !== null) {
            $this->roomLogService->createEquipmentLog(
                ActionEnum::SEARCH,
                $this->player->getRoom(),
                $this->player,
                $this->itemFound,
                VisibilityEnum::COVERT,
                new \DateTime('now')
            );
        } else {
            $this->roomLogService->createPlayerLog(
                ActionEnum::SEARCH,
                $this->player->getRoom(),
                $this->player,
                VisibilityEnum::COVERT,
                new \DateTime('now')
            );
        }
    }
}
