<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\ArackPreventsTravel;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\NoFuel;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Enum\DaedalusStatusEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AdvanceDaedalus extends AbstractAction
{
    protected string $name = ActionEnum::ADVANCE_DAEDALUS;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
        );
        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::FOCUSED,
            'target' => HasStatus::PLAYER,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::TRAVELING,
            'target' => HasStatus::DAEDALUS,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DAEDALUS_TRAVELING,
        ]));
    }

    protected function checkResult(): ActionResult
    {
        $daedalus = $this->player->getDaedalus();
        if ($daedalus->getCombustionChamberFuel() <= 0) {
            return new NoFuel();
        }
        if ($daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::SPIDER)->count() > 0) {
            return new ArackPreventsTravel();
        }

        /** @var false|GameEquipment $emergencyReactor */
        $emergencyReactor = $this->gameEquipmentService->findByNameAndDaedalus(
            name: EquipmentEnum::EMERGENCY_REACTOR,
            daedalus: $daedalus,
        )->first();

        if ($emergencyReactor && $emergencyReactor->isBroken()) {
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
