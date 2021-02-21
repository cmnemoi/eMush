<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
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

    /** @var Player */
    protected $parameter;

    private StatusServiceInterface $statusService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService,
        PlayerServiceInterface $playerService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->statusService = $statusService;
        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public function isVisible(): bool
    {
        return parent::isVisible() &&
            $this->player->isMush() &&
            $this->player->getPlace() === $this->parameter->getPlace();
    }

    public function cannotExecuteReason(): ?string
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        /** @var ChargeStatus $mushStatus */
        $mushStatus = $this->player->getStatusByName(PlayerStatusEnum::MUSH);

        if ($sporeStatus === null || !($sporeStatus instanceof ChargeStatus) ||
            $mushStatus === null || !($mushStatus instanceof ChargeStatus)
        ) {
            throw new Error('invalid spore and mush status');
        }

        if ($sporeStatus->getCharge() <= 0) {
            return ActionImpossibleCauseEnum::INFECT_NO_SPORE;
        }
        if ($mushStatus->getCharge() <= 0) {
            return ActionImpossibleCauseEnum::INFECT_DAILY_LIMIT;
        }
        if ($this->parameter->isMush()) {
            return ActionImpossibleCauseEnum::INFECT_MUSH;
        }
        if ($this->parameter->getStatusByName(PlayerStatusEnum::IMMUNIZED)) {
            return ActionImpossibleCauseEnum::INFECT_IMMUNE;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $playerEvent = new PlayerEvent($this->parameter);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        /** @var ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        $sporeStatus->addCharge(-1);
        $this->statusService->persist($sporeStatus);

        /** @var ChargeStatus $mushStatus */
        $mushStatus = $this->player->getStatusByName(PlayerStatusEnum::MUSH);
        $mushStatus->addCharge(-1);
        $this->statusService->persist($mushStatus);

        $target = new Target($this->parameter->getCharacterConfig()->getName(), 'character');

        return new Success($target);
    }
}
