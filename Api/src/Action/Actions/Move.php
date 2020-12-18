<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Door;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Move extends Action
{
    protected string $name = ActionEnum::MOVE;

    private Door $door;

    private RoomLogServiceInterface $roomLogService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        RoomLogServiceInterface $roomLogService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->playerService = $playerService;

        $this->actionCost->setMovementPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!($door = $actionParameters->getDoor())) {
            throw new \InvalidArgumentException('Invalid door parameter');
        }

        $this->player = $player;
        $this->door = $door;
    }

    public function canExecute(): bool
    {
        return !$this->door->isBroken()
            && $this->player->getRoom()->getDoors()->contains($this->door);
    }

    protected function applyEffects(): ActionResult
    {
        $newRoom = $this->door->getRooms()->filter(fn (Room $room) => $room !== $this->player->getRoom())->first();
        $this->player->setRoom($newRoom);

        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            LogEnum::ENTER_ROOM,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
        $this->roomLogService->createPlayerLog(
            LogEnum::EXIT_ROOM,
            $this->door->getRooms()->filter(fn (Room $room) => $room !== $this->player->getRoom())->first(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
