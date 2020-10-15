<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Room\Entity\Door;
use Mush\Room\Entity\Room;

class Move extends Action
{
    private Player $player;
    private Door $door;
    private PlayerServiceInterface $playerService;

    public function __construct(PlayerServiceInterface $playerService)
    {
        $this->playerService = $playerService;
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (! ($door = $actionParameters->getDoor())) {
            throw new \InvalidArgumentException('Invalid door parameter');
        }

        $this->player = $player;
        $this->door = $door;
    }

    public function canExecute(): bool
    {
        return ($this->player->getActionPoint() > 0 || $this->player->getMovementPoint() > 0)
            && $this->player->getRoom()->getDoors()->contains($this->door);
    }

    protected function apply(): ActionResult
    {
        if ($this->player->getMovementPoint() > 0) {
            $this->player->setMovementPoint($this->player->getMovementPoint() - 1);
        }elseif($this->player->getActionPoint() > 0) {
            $this->player->setActionPoint($this->player->getActionPoint() - 1);
            $this->player->setMovementPoint(2);
        }

        $newRoom = $this->door->getRooms()->filter(fn(Room $room) => $room !== $this->player->getRoom())->first();

        $this->player->setRoom($newRoom);

        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        // TODO: Implement createLog() method.
    }


}