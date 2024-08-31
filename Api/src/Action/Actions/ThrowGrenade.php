<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\GrenadeInhibit;
use Mush\Action\Validator\NumberPlayersAliveInRoom;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ThrowGrenade extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::THROW_GRENADE;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RandomServiceInterface $randomService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new NumberPlayersAliveInRoom([
                'mode' => NumberPlayersAliveInRoom::EQUAL,
                'number' => 1,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::LAUNCH_GRENADE_ALONE,
            ]),
            new GrenadeInhibit([
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DMZ_CORE_PEACE,
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->destroyGrenade();
        $this->removeHealthToPlayersInRoom();
    }

    private function destroyGrenade(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            $this->grenade(),
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getTags(),
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function removeHealthToPlayersInRoom(): void
    {
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection($this->grenadeMechanic()->getBaseDamageRange());

        foreach ($this->player->getAlivePlayersInRoomExceptSelf() as $player) {
            $playerVariableEvent = new PlayerVariableEvent(
                $player,
                PlayerVariableEnum::HEALTH_POINT,
                -$damage,
                $this->getTags(),
                new \DateTime(),
            );
            $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }

    private function grenade(): GameItem
    {
        $grenade = $this->actionProvider;

        return $grenade instanceof GameItem ? $grenade : throw new \RuntimeException('Action provider is not a GameItem');
    }

    private function grenadeMechanic(): Weapon
    {
        return $this->grenade()->getWeaponMechanicOrThrow();
    }
}
