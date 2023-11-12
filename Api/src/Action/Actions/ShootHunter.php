<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Charged;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NumberOfAttackingHunters;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
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
        ActionEnum::SHOOT_RANDOM_HUNTER => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
        ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP => ActionLogEnum::SHOOT_HUNTER_PATROL_SHIP_SUCCESS,
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

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Hunter || $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::SPACE_BATTLE, 'groups' => ['visibility']]));
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

        /** @var GameEquipment $shootingEquipment */
        $shootingEquipment = $this->getShootingEquipment();

        /** @var Weapon $weapon */
        $weapon = $this->getWeaponMechanic($shootingEquipment);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($weapon->getBaseDamageRange());

        /** @var Hunter $hunter */
        $hunter = $this->selectHunterToShoot();

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

    // @TODO: hack to recover shooting equipment. This has to be improved in a bigger weapon rework (and all items which "target" something)
    private function getShootingEquipment(): GameEquipment
    {
        /** @var GameEquipment $shootingEquipment */
        $shootingEquipment = $this->player->getPlace()->getEquipments()
            ->filter(fn (GameEquipment $shootingEquipment) => !$shootingEquipment instanceof GameItem) // filter items to avoid recover PvP weapons
            ->filter(fn (GameEquipment $shootingEquipment) => $shootingEquipment->getEquipment()->getMechanics()->filter(fn (Mechanic $mechanic) => $mechanic instanceof Weapon)->count() > 0)
            ->first();

        if (!$shootingEquipment instanceof GameEquipment) {
            throw new \Exception("Shoot hunter action : {$this->player->getPlace()->getName()} should have a shooting equipment (turret or patrol ship)");
        }

        return $shootingEquipment;
    }

    private function getWeaponMechanic(GameEquipment $shootingEquipment): Weapon
    {
        /** @var Weapon $weapon */
        $weapon = $shootingEquipment->getEquipment()->getMechanics()->filter(fn (Mechanic $mechanic) => $mechanic instanceof Weapon)->first();
        if (!$weapon instanceof Weapon) {
            throw new \Exception("Shoot hunter action : {$shootingEquipment->getName()} should have a weapon mechanic");
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

    private function selectHunterToShoot(): Hunter
    {
        if ($this->target instanceof Hunter) {
            return $this->target;
        }

        $hunters = $this->player->getDaedalus()->getAttackingHunters()->toArray();
        $hunterToShoot = $this->randomService->getRandomElements($hunters, number: 1);

        return reset($hunterToShoot);
    }
}
