<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\DailySporesLimit;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\MushSpore;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExtractSpore extends AbstractAction
{
    protected string $name = ActionEnum::EXTRACT_SPORE;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::MUSH, 'target' => HasStatus::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new DailySporesLimit(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::DAILY_SPORE_LIMIT]));
        $metadata->addConstraint(new MushSpore(['threshold' => 2, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PERSONAL_SPORE_LIMIT]));
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    protected function checkResult(): ActionResult
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);

        if ($sporeStatus === null) {
            throw new Error('Player should have a spore status');
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);

        if ($sporeStatus === null) {
            throw new Error('Player should have a spore status');
        }

        $sporeStatus->addCharge(1);
        $this->statusService->persist($sporeStatus);

        $this->player->getDaedalus()->setSpores($this->player->getDaedalus()->getSpores() - 1);
    }
}
