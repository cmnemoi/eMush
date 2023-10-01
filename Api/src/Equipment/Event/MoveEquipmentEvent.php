<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\EquipmentHolderInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;

final class MoveEquipmentEvent extends InteractWithEquipmentEvent
{
    private EquipmentHolderInterface $newHolder;

    public function __construct(
        GameEquipment $equipment,
        EquipmentHolderInterface $newHolder,
        ?Player $author,
        string $visibility,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($equipment, $author, $visibility, $tags, $time);

        $this->newHolder = $newHolder;
    }

    public function getLogParameters(): array
    {
        $logParameters = parent::getLogParameters();

        $newHolderPlace = $this->newHolder->getPlace();
        $logParameters[$newHolderPlace->getLogKey()] = $newHolderPlace->getLogName();

        return $logParameters;
    }

    public function getNewHolder(): EquipmentHolderInterface
    {
        return $this->newHolder;
    }
}
