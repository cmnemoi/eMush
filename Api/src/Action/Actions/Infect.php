<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Infect extends AbstractAction
{
    protected string $name = ActionEnum::INFECT;

    private Player $targetPlayer;

    private StatusServiceInterface $statusService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct($eventDispatcher);

        $this->statusService = $statusService;
        $this->playerService = $playerService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($targetPlayer = $actionParameters->getPlayer())) {
            throw new \InvalidArgumentException('Invalid player parameter');
        }

        $this->targetPlayer = $targetPlayer;
    }

    public function canExecute(): bool
    {
        /** @var ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        /** @var ChargeStatus $mushStatus */
        $mushStatus = $this->player->getStatusByName(PlayerStatusEnum::MUSH);

        return $this->player->isMush() &&
            $sporeStatus &&
            $sporeStatus->getCharge() > 0 &&
            $mushStatus->getCharge() > 0 &&
            !$this->targetPlayer->isMush() &&
            !$this->targetPlayer->getStatusByName(PlayerStatusEnum::IMMUNIZED) &&
            $this->player->getRoom() === $this->targetPlayer->getRoom();
    }

    protected function applyEffects(): ActionResult
    {
        $playerEvent = new PlayerEvent($this->targetPlayer);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        /** @var ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        $sporeStatus->addCharge(-1);
        $this->statusService->persist($sporeStatus);

        /** @var ChargeStatus $mushStatus */
        $mushStatus = $this->player->getStatusByName(PlayerStatusEnum::MUSH);
        $mushStatus->addCharge(-1);
        $this->statusService->persist($mushStatus);

        $target = new Target($this->targetPlayer->getCharacterConfig()->getName(), 'character');

        return new Success($target);
    }
}
