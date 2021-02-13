<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Search extends AbstractAction
{
    protected string $name = ActionEnum::SEARCH;

    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );
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
            ->getPlace()
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
                !($hiddenBy = $hiddenStatus->getTarget()) ||
                !$hiddenBy instanceof Player
            ) {
                throw new \LogicException('invalid hidden status');
            }

            $itemFound = $mostRecentHiddenItem;
            $itemFound->removeStatus($hiddenStatus);

            $hiddenBy->removeStatus($hiddenStatus);

            $this->playerService->persist($hiddenBy);

            $target = new Target($itemFound->getName(), 'items');

            return new Success($target);
        } else {
            return new Fail();
        }
    }
}
