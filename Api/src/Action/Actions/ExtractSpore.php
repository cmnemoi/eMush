<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ParameterHasAction;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

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

    public static function loadVisibilityValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Status(['status' => PlayerStatusEnum::MUSH, 'target' => Status::PLAYER]));
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter === null;
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
