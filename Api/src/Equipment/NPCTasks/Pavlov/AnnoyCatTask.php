<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Pavlov;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class AnnoyCatTask extends AbstractDogTask
{
    private int $annoyCatChance = 60;

    public function __construct(
        private D100RollServiceInterface $d100Roll,
        protected EventServiceInterface $eventService,
        private RoomLogServiceInterface $roomLogService,
    ) {
        parent::__construct($this->eventService);
    }

    public function setAnnoyCatChance(int $chance): void
    {
        $this->annoyCatChance = $chance;
    }

    protected function applyEffect(GameEquipment $pavlov, \DateTime $time): void
    {
        $place = $pavlov->getPlace();
        if ($place->hasEquipmentByName(ItemEnum::SCHRODINGER) && $this->d100Roll->isSuccessful(successRate: $this->annoyCatChance)) {
            $this->createAnnoyCatLog($pavlov, $place, $time);
        } else {
            $this->taskNotApplicable = true;

            return;
        }
    }

    private function createAnnoyCatLog(GameEquipment $pavlov, Place $place, \DateTime $time): void
    {
        $this->roomLogService->createLog(
            LogEnum::DOG_BOTHER_CAT,
            $place,
            VisibilityEnum::PUBLIC,
            'event_log',
            null,
            [],
            $time
        );
    }
}
