<?php

namespace Mush\Communications\Service;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;

class PrintDocumentService implements PrintDocumentServiceInterface
{
    public function __construct(
        private EventServiceInterface $eventService,
    ) {}

    public function execute(
        GameEquipment $printer,
        array $tags = [],
    ): void {
        if (!$printer->IsTabulatrix()) {
            throw new \LogicException('undefined printer');
        }

        if ($printer->isNotOperational()) {
            return;
        }

        $documentsToPrint = $printer->getDaedalus()->getTabulatrixQueue()->getEquipments();

        foreach ($documentsToPrint as $document) {
            $printEvent = new MoveEquipmentEvent(
                equipment: $document,
                newHolder: $printer->getPlace(),
                author: null,
                visibility: VisibilityEnum::PUBLIC,
                tags: $tags,
                time: new \DateTime(),
            );
            $printEvent->AddTag(EventEnum::PRINT_DOCUMENT);
            $this->eventService->callEvent($printEvent, EquipmentEvent::CHANGE_HOLDER);
        }
    }
}
