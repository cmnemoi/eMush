<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;

final class Artefact extends AbstractLootItemsEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::ARTEFACT;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        // Artefact event creates only one item
        $artefact = $this->createRandomItemsFromEvent($event)->first();

        $logParameters = $this->getLogParameters($event);
        $logParameters['target_' . $artefact->getLogKey()] = $artefact->getLogName();

        return $this->createExplorationLog($event, $logParameters);
    }
}
