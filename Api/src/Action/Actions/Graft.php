<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\FruitToGraftGivesDifferentPlant;
use Mush\Action\Validator\HasSkill;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Graft extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::GRAFT;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private RandomServiceInterface $randomService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasSkill([
                'skill' => SkillEnum::BOTANIST,
                'groups' => ['visibility'],
            ]),
            new FruitToGraftGivesDifferentPlant([
                'groups' => ['visibility'],
            ]),
        ]);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem && $target->hasMechanicByName(EquipmentMechanicEnum::PLANT);
    }

    protected function checkResult(): ActionResult
    {
        /** @var GameItem $plant */
        $plant = $this->target;

        if ($this->player->hasStatus(PlayerStatusEnum::DIRTY) || $plant->isPlantUnhealthy()) {
            return new Fail();
        }

        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result->isASuccess()) {
            $this->createGraftedFruitPlant();
        }

        $this->destroyPlant();
        $this->destroyGraftedFruit();
    }

    private function createGraftedFruitPlant(): void
    {
        /** @var GameItem $graftedFruit */
        $graftedFruit = $this->actionProvider;

        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $graftedFruit->getPlantNameOrThrow(),
            equipmentHolder: $this->player,
            reasons: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function destroyPlant(): void
    {
        /** @var GameItem $plantToDestroy */
        $plantToDestroy = $this->target;

        $equipmentEvent = new InteractWithEquipmentEvent(
            $plantToDestroy,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getTags(),
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function destroyGraftedFruit(): void
    {
        /** @var GameItem $graftedFruit */
        $graftedFruit = $this->actionProvider;

        $equipmentEvent = new InteractWithEquipmentEvent(
            $graftedFruit,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getTags(),
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
