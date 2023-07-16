<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Charged;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NumberOfAttackingHunters;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShootHunter extends AttemptAction
{
    protected string $name = ActionEnum::SHOOT_HUNTER;

    private const SHOOT_HUNTER_LOG_MAP = [
        ActionEnum::SHOOT_HUNTER => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
        ActionEnum::SHOOT_HUNTER_PATROL_SHIP => ActionLogEnum::SHOOT_HUNTER_PATROL_SHIP_SUCCESS,
    ];
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
    ) {
        parent::__construct($eventService, $actionService, $validator, $randomService);
        $this->roomLogService = $roomLogService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus(['status' => EquipmentStatusEnum::BROKEN, 'contain' => false, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Charged(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::UNLOADED_WEAPON]));
        $metadata->addConstraint(new NumberOfAttackingHunters([
            'mode' => NumberOfAttackingHunters::EQUAL,
            'number' => 0,
            'groups' => ['visibility'],
        ]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        if (!$result instanceof Success) {
            return;
        }

        $daedalus = $this->player->getDaedalus();
        /** @var GameEquipment $equipment */
        $equipment = $this->parameter;

        /** @var Weapon $weapon */
        $weapon = $this->getWeaponMechanic($equipment);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($weapon->getBaseDamageRange());

        $hunter = $daedalus->getAttackingHunters()->first();
        if (!$hunter) {
            throw new \Exception('There should be attacking hunters if ShootHunter action is available.');
        }

        $shotDoesntKillHunter = $damage < $hunter->getHealth();
        if ($shotDoesntKillHunter) {
            $this->logShootHunterSuccess($hunter);
        }

        $hunterVariableEvent = new HunterVariableEvent(
            $hunter,
            HunterVariableEnum::HEALTH,
            -$damage,
            $this->getAction()->getActionTags(),
            new \DateTime()
        );
        $hunterVariableEvent->setAuthor($this->player);
        $this->eventService->callEvent($hunterVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function getWeaponMechanic(GameEquipment $parameter): Weapon
    {
        /** @var Weapon $weapon */
        $weapon = $parameter->getEquipment()->getMechanics()->filter(fn (Mechanic $mechanic) => $mechanic instanceof Weapon)->first();
        if (!$weapon instanceof Weapon) {
            throw new \Exception("Shoot hunter action : {$weapon->getName()} should have a weapon mechanic");
        }

        return $weapon;
    }

    private function logShootHunterSuccess(Hunter $hunter): void
    {
        $logParameters = [
            $this->player->getLogKey() => $this->player->getLogName(),
            $hunter->getLogKey() => $hunter->getLogName(),
        ];
        $this->roomLogService->createLog(
            logKey: self::SHOOT_HUNTER_LOG_MAP[$this->name],
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'actions_log',
            player: $this->player,
            parameters: $logParameters,
            dateTime: new \DateTime(),
        );
    }
}
