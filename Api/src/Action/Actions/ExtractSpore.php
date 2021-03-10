<?php

namespace Mush\Action\Actions;

use Error;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\DailySporesLimit;
use Mush\Action\Validator\MushSpore;
use Mush\Action\Validator\Status;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExtractSpore extends AbstractAction
{
    protected string $name = ActionEnum::EXTRACT_SPORE;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->statusService = $statusService;
    }

    protected function getConstraints(): array
    {
        return [
            new Status(['status' => PlayerStatusEnum::MUSH, 'target' => Status::PLAYER, 'groups' => ['visibility']]),
            new DailySporesLimit(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::DAILY_SPORE_LIMIT]),
            new MushSpore(['threshold' => 2, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PERSONAL_SPORE_LIMIT])
        ];
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter === null;
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
