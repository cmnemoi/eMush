<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SearchForMushGenome extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::SEARCH_FOR_MUSH_GENOME;

    public function __construct(
        EventServiceInterface $eventService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        ValidatorInterface $validator
    ) {
        parent::__construct($eventService, null, $validator);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::MUSH_GENOME_DISK,
            equipmentHolder: $this->player->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }
}
