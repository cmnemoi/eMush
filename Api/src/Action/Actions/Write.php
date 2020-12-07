<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Write extends Action
{
    protected string $name = ActionEnum::WRITE;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();

        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!($message = $actionParameters->getMessage())) {
            throw new \InvalidArgumentException('Invalid message parameter');
        }

        $this->player = $player;
        $this->message = $message;
        $this->actionCost->setActionPointCost(0);
    }

    public function canExecute(): bool
    {
        //Check that the block of post-it is reachable
        return !$this->player->getReachableEquipmentsByName(ToolItemEnum::BLOCK_OF_POST_IT)->isEmpty();
    }

    protected function applyEffects(): ActionResult
    {
        $newGameItem = $this->gameEquipmentService->createGameEquipmentFromName(ItemEnum::POST_IT, $this->player->getDaedalus());
        $contentStatus = new ContentStatus();
        $contentStatus
            ->setName(EquipmentStatusEnum::DOCUMENT_CONTENT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setGameEquipment($newGameItem)
            ->setContent($this->message)
        ;
        $newGameItem->addStatus($contentStatus);

        if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
            $newGameItem->setPlayer($this->player);
        } else {
            $newGameItem->setRoom($this->player->getRoom());
        }

        $this->gameEquipmentService->persist($newGameItem);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::WRITE,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
