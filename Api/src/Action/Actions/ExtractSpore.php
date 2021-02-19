<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExtractSpore extends AbstractAction
{
    protected string $name = ActionEnum::EXTRACT_SPORE;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->statusService = $statusService;
    }

    public function isVisible(): bool
    {
        return parent::isVisible() && $this->player->isMush();
    }

    public function cannotExecuteReason(): ?string
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);

        if ($sporeStatus === null || !($sporeStatus instanceof ChargeStatus)) {
            throw new Error('invalid spore status');
        }

        if ($sporeStatus->getCharge() >= 2) {
            return ActionImpossibleCauseEnum::PERSONAL_SPORE_LIMIT;
        }
        if ($this->player->getDaedalus()->getSpores() <= 0) {
            return ActionImpossibleCauseEnum::DAILY_SPORE_LIMIT;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);

        if ($sporeStatus === null) {
            throw new Error('Player should have a spore status');
        }

        $sporeStatus->addCharge(1);
        $this->statusService->persist($sporeStatus);

        $this->player->getDaedalus()->setSpores($this->player->getDaedalus()->getSpores() - 1);

        return new Success();
    }
}
