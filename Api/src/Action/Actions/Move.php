<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Move extends AbstractAction
{
    protected string $name = ActionEnum::MOVE;

    private Door $door;

    private RoomLogServiceInterface $roomLogService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        RoomLogServiceInterface $roomLogService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->roomLogService = $roomLogService;
        $this->playerService = $playerService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($door = $actionParameters->getDoor())) {
            throw new \InvalidArgumentException('Invalid door parameter');
        }

        $this->door = $door;
    }

    public function isVisible(): bool
    {
        if ($this->door->isBroken() ||
            !$this->player->getPlace()->getDoors()->contains($this->door)
        ) {
            return false;
        }

        return parent::isVisible();
    }

    protected function applyEffects(): ActionResult
    {
        $newRoom = $this->door->getOtherRoom($this->player->getPlace());
        $this->player->setPlace($newRoom);

        $this->playerService->persist($this->player);

        $this->createLog();

        return new Success();
    }

    protected function createLog(): void
    {
        $this->roomLogService->createActionLog(
            ActionLogEnum::ENTER_ROOM,
            $this->player->getPlace(),
            $this->player,
            null,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
        $this->roomLogService->createActionLog(
            ActionLogEnum::EXIT_ROOM,
            $this->door->getOtherRoom($this->player->getPlace()),
            $this->player,
            null,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
