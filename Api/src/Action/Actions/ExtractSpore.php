<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Service\ActionModifierServiceInterface;
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
        GearToolServiceInterface $gearToolService,
        ActionModifierServiceInterface $actionModifierService
    ) {
        parent::__construct(
            $eventDispatcher,
            $gearToolService,
            $actionModifierService
        );

        $this->statusService = $statusService;
    }

    public function canExecute(): bool
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);

        return $this->player->isMush() &&
                (!$sporeStatus ||
                $sporeStatus->getCharge() < 2) &&
                $this->player->getDaedalus()->getSpores() > 0;
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
