<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ContentStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Write extends AbstractAction
{
    protected string $name = ActionEnum::WRITE;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    private GameConfig $gameConfig;
    private string $message;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();

        $this->actionCost->setActionPointCost(0);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        $this->player = $player;
        $this->message = $actionParameters->getMessage();
        $this->actionCost->setActionPointCost(0);
    }

    public function canExecute(): bool
    {
        //Check that the block of post-it is reachable
        return !$this->player->getReachableEquipmentsByName(ToolItemEnum::BLOCK_OF_POST_IT)->isEmpty();
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $newGameItem */
        $newGameItem = $this->gameEquipmentService
            ->createGameEquipmentFromName(ItemEnum::POST_IT, $this->player->getDaedalus())
        ;
        $contentStatus = new ContentStatus();
        $contentStatus
            ->setName(EquipmentStatusEnum::DOCUMENT_CONTENT)
            ->setVisibility(VisibilityEnum::HIDDEN)
            ->setGameEquipment($newGameItem)
            ->setContent($this->message)
        ;
        $newGameItem->addStatus($contentStatus);

        $equipmentEvent = new EquipmentEvent($newGameItem);
        $equipmentEvent->setPlayer($this->player);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $this->gameEquipmentService->persist($newGameItem);
        $this->playerService->persist($this->player);

        return new Success(ActionLogEnum::WRITE_SUCCESS, VisibilityEnum::PUBLIC);
    }
}
