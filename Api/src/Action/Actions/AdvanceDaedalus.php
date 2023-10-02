<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Daedalus\Enum\DaedalusStatusEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AdvanceDaedalus extends AbstractAction
{
    protected string $name = ActionEnum::ADVANCE_DAEDALUS;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService,
    ) { 
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
        );
        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::FOCUSED,
            'target' => HasStatus::PLAYER,
            'groups' => ['visibility'],
        ]));
    }

    protected function checkResult(): ActionResult
    {   
        if ($this->player->getDaedalus()->getCombustionChamberFuel() <= 0) {
            return new Fail();
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {   
        if ($result instanceof Fail) {
            return;
        }

        $actionTags = $this->action->getActionTags();
        $daedalus = $this->player->getDaedalus();
        $now = new \DateTime();

        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::TRAVELING,
            holder: $daedalus,
            tags: $actionTags,
            time: $now,
        );

        $travelLaunchedEvent = new DaedalusEvent(
            daedalus: $daedalus,
            tags: $actionTags,
            time: $now,
        );
        $this->eventService->callEvent($travelLaunchedEvent, DaedalusEvent::TRAVEL_LAUNCHED);
    }
}