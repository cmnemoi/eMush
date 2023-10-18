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
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AdvanceDaedalus extends AbstractAction
{
    public const ARACK_PREVENTS_TRAVEL = 'arack_prevents_travel';
    public const EMERGENCY_REACTOR_BROKEN = 'emergency_reactor_broken';
    public const NO_FUEL = 'no_fuel';
    public const OK = 'ok';

    public static array $statusMap = [
        self::ARACK_PREVENTS_TRAVEL => [
            'key' => self::ARACK_PREVENTS_TRAVEL,
            'type' => ActionOutputEnum::FAIL,
        ],
        self::EMERGENCY_REACTOR_BROKEN => [
            'key' => self::EMERGENCY_REACTOR_BROKEN,
            'type' => ActionOutputEnum::FAIL,
        ],
        self::NO_FUEL => [
            'key' => self::NO_FUEL,
            'type' => ActionOutputEnum::FAIL,
        ],
        self::OK => [
            'key' => self::OK,
            'type' => ActionOutputEnum::SUCCESS,
        ],
    ];

    protected string $name = ActionEnum::ADVANCE_DAEDALUS;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
        );
        $this->gameEquipmentService = $gameEquipmentService;
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
            'statusTargetName' => EquipmentEnum::COMMAND_TERMINAL,
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

    /**
     * Returns the status of the action : OK, NO_FUEL, ARACK_PREVENTS_TRAVEL, EMERGENCY_REACTOR_BROKEN.
     *
     * For example, if there is no fuel in the combustion chamber, this function will return NO_FUEL.
     */
    public static function getActionStatus(Daedalus $daedalus, GameEquipmentServiceInterface $gameEquipmentService): string
    {
        /** @var false|GameEquipment $emergencyReactor */
        $emergencyReactor = $gameEquipmentService->findByNameAndDaedalus(
            name: EquipmentEnum::EMERGENCY_REACTOR,
            daedalus: $daedalus,
        )->first();

        if ($emergencyReactor && $emergencyReactor->isBroken()) {
            return self::EMERGENCY_REACTOR_BROKEN;
        }
        if ($daedalus->getCombustionChamberFuel() <= 0) {
            return self::NO_FUEL;
        }
        if ($daedalus->getAttackingHunters()->getAllHuntersByType(HunterEnum::SPIDER)->count() > 0) {
            return self::ARACK_PREVENTS_TRAVEL;
        }

        return self::OK;
    }

    protected function checkResult(): ActionResult
    {
        $actionStatus = self::getActionStatus($this->player->getDaedalus(), $this->gameEquipmentService);
        $result = match ($actionStatus) {
            self::ARACK_PREVENTS_TRAVEL => new ArackPreventsTravel(),
            self::EMERGENCY_REACTOR_BROKEN => new Fail(),
            self::NO_FUEL => new NoFuel(),
            default => new Success(),
        };

        return $result;
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Fail) {
            return;
        }

        $travelLaunchedEvent = new DaedalusEvent(
            daedalus: $this->player->getDaedalus(),
            tags: $this->action->getActionTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($travelLaunchedEvent, DaedalusEvent::TRAVEL_LAUNCHED);
    }
}
