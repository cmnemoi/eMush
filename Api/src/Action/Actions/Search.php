<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Search extends AbstractAction
{
    protected string $name = ActionEnum::SEARCH;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
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

            $itemFound = $mostRecentHiddenItem;
            $itemFound->removeStatus($hiddenStatus);

            $hiddenBy->removeStatus($hiddenStatus);

            $this->playerService->persist($hiddenBy);

            $this->statusService->delete($hiddenStatus);
            $this->gameEquipmentService->persist($mostRecentHiddenItem);

            $target = new Target($itemFound->getName(), 'items');

            return new Success(ActionLogEnum::SEARCH_SUCCESS, VisibilityEnum::COVERT, $target);
        } else {
            return new Fail(ActionLogEnum::SEARCH_FAIL, VisibilityEnum::COVERT);
        }
    }
}
