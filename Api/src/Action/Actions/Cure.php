<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * implement cure action.
 * For 1 ap Points, a player holding Retro Fungal Serum can cure another player
 *  - Target player loses the mush status
 *  - Target player loses all their human skills
 *  - Retro fungal serum is destroyed.
 */
class Cure extends AbstractAction
{
    public const string PLAYER_VACCINATED = 'player_vaccinated';
    protected ActionEnum $name = ActionEnum::CURE;
    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->statusService = $statusService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasEquipment([
            'reach' => ReachEnum::INVENTORY,
            'equipments' => [ToolItemEnum::RETRO_FUNGAL_SERUM],
            'contains' => true,
            'checkIfOperational' => true,
            'target' => HasEquipment::PLAYER,
            'groups' => ['visibility'],
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $target = $this->playerTarget();
        $mushCured = $target->isMush();

        $this->statusService->removeStatus(
            PlayerStatusEnum::MUSH,
            $target,
            $this->getTags(),
            new \DateTime(),
        );

        $this->destroySerum($mushCured);
    }

    private function destroySerum(bool $mushCured)
    {
        $serum = $this->getPlayer()->getEquipmentByNameOrThrow(ToolItemEnum::RETRO_FUNGAL_SERUM);
        $tags = $mushCured ? array_merge($this->getTags(), [self::PLAYER_VACCINATED]) : $this->getTags();

        $equipmentEvent = new InteractWithEquipmentEvent(
            $serum,
            $this->player,
            VisibilityEnum::HIDDEN,
            $tags,
            new \DateTime()
        );

        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
