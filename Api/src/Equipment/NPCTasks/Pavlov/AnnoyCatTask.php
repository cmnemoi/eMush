<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Pavlov;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\AnnoyCatEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Place\Entity\Place;

class AnnoyCatTask extends AbstractDogTask
{
    public function __construct(
        private D100RollServiceInterface $d100Roll,
        protected EventServiceInterface $eventService,
    ) {
        parent::__construct($this->eventService);
    }

    protected function applyEffect(GameEquipment $pavlov, \DateTime $time): void
    {
        $place = $pavlov->getPlace();
        if ($place->hasEquipmentByName(ItemEnum::SCHRODINGER) && $this->d100Roll->isSuccessful(successRate: 60)) {
            $this->createAnnoyCatEvent($pavlov, $place, $time);
        } else {
            $this->taskNotApplicable = true;

            return;
        }
    }

    private function createAnnoyCatEvent(GameEquipment $pavlov, Place $place, \DateTime $time): void
    {
        $dogEvent = new AnnoyCatEvent(
            NPC: $pavlov,
            place: $place,
            time: $time
        );
        $this->eventService->callEvent($dogEvent, AnnoyCatEvent::class);
    }
}
