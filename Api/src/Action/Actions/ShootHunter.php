<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\NumberOfAttackingHunters;
use Mush\Action\Validator\PlaceType;
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
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShootHunter extends AttemptAction
{
    private const array SHOOT_HUNTER_LOG_MAP = [
        ActionEnum::SHOOT_HUNTER->value => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
        ActionEnum::SHOOT_HUNTER_PATROL_SHIP->value => ActionLogEnum::SHOOT_HUNTER_PATROL_SHIP_SUCCESS,
        ActionEnum::SHOOT_RANDOM_HUNTER->value => ActionLogEnum::SHOOT_HUNTER_SUCCESS,
        ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP->value => ActionLogEnum::SHOOT_HUNTER_PATROL_SHIP_SUCCESS,
    ];
    protected ActionEnum $name = ActionEnum::SHOOT_HUNTER;
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

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::SPACE_BATTLE, 'groups' => ['visibility']]));
        $metadata->addConstraint(new NumberOfAttackingHunters([
            'mode' => NumberOfAttackingHunters::EQUAL,
            'number' => 0,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Hunter || $target instanceof GameEquipment;
    }

    protected function applyEffect(ActionResult $result): void
    {
        if (!$result instanceof Success) {
            $result->addDetail('hunterIsAlive', true);

            return;
        }

        /** @var GameEquipment $shootingEquipment */
        $shootingEquipment = $this->getActionProvider();

        /** @var Weapon $weapon */
        $weapon = $this->getWeaponMechanic($shootingEquipment);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($weapon->getBaseDamageRange());

        /** @var Hunter $hunter */
        $hunter = $this->selectHunterToShoot();

        $shotDoesNotKillHunter = $damage < $hunter->getHealth();
        if ($shotDoesNotKillHunter) {
            $this->logShootHunterSuccess($hunter);
        }

        // Add some extra info to enable hunter hit/death animations in front-end
        $result->addDetail('hunterIsAlive', $shotDoesNotKillHunter);
        $result->addDetail('targetedHunterId', $hunter->getId());

        $hunterVariableEvent = new HunterVariableEvent(
            $hunter,
            HunterVariableEnum::HEALTH,
            -$damage,
            $this->getActionConfig()->getActionTags(),
            new \DateTime()
        );
        $hunterVariableEvent->setAuthor($this->player);
        $this->eventService->callEvent($hunterVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function getWeaponMechanic(GameEquipment $shootingEquipment): Weapon
    {
        /** @var Weapon $weapon */
        $weapon = $shootingEquipment->getEquipment()->getMechanics()->filter(static fn (Mechanic $mechanic) => $mechanic instanceof Weapon)->first();
        if (!$weapon instanceof Weapon) {
            throw new \Exception("Shoot hunter action : {$shootingEquipment->getName()} should have a weapon mechanic");
        }

        return $weapon;
    }

    private function logShootHunterSuccess(Hunter $hunter): void
    {
        $logKey = $this->getActionName();
        $logParameters = [
            $this->player->getLogKey() => $this->player->getLogName(),
            $hunter->getLogKey() => $hunter->getLogName(),
        ];
        $this->roomLogService->createLog(
            logKey: self::SHOOT_HUNTER_LOG_MAP[$logKey],
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
