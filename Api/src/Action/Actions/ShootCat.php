<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Special implementation of the Shoot action that targets equipments instead of players, for the purpose of shooting at Schrodinger.
 */
class ShootCat extends AttemptAction
{
    private const string CAT_DEATH_TAG = 'cat_death';
    protected ActionEnum $name = ActionEnum::SHOOT_CAT;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    private DiseaseCauseServiceInterface $diseaseCauseService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService,
        DiseaseCauseServiceInterface $diseaseCauseService,
        private PlayerServiceInterface $playerService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator,
            $randomService,
        );

        $this->diseaseCauseService = $diseaseCauseService;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem && $target->isSchrodinger();
    }

    // Special checkResult for Shoot action waiting for a refactor
    protected function checkResult(): ActionResult
    {
        $weapon = $this->getPlayerWeapon();

        $success = $this->randomService->isSuccessful($this->getSuccessRate());

        if ($success) {
            return new Success();
        }

        return new Fail();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $player = $this->player;

        if ($result instanceof Success) {
            $this->killCat();
        }
    }

    private function getPlayerWeapon(): Weapon
    {
        /** @var GameItem $weaponItem */
        $weaponItem = $this->actionProvider;

        return $weaponItem->getWeaponMechanicOrThrow();
    }

    private function killCat(): void
    {
        $cat = $this->gameItemTarget();
        $interactEvent = new InteractWithEquipmentEvent(
            $cat,
            $this->player,
            VisibilityEnum::PUBLIC,
            $this->getTags(),
            new \DateTime(),
        );
        $interactEvent->addTag(self::CAT_DEATH_TAG);
        if ($cat->hasStatus(EquipmentStatusEnum::CAT_INFECTED)) {
            $interactEvent->addTag(EquipmentStatusEnum::CAT_INFECTED);
        }

        $this->eventService->callEvent($interactEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
