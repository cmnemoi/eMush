<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Enum\PlanetSectorEnum;
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
        /** @var GameEquipment $artefact */
        $artefact = $this->createRandomItemsFromEvent($event)->first();

        $logParameters = $this->getLogParameters($event);
        $logParameters['target_' . $artefact->getLogKey()] = $artefact->getLogName();

        $babelWorked = $event->getPlanetSector()->getName() === PlanetSectorEnum::INTELLIGENT && $event->getExploration()->hasAFunctionalBabelModule();
        $logParameters['used_babel_module'] = $babelWorked ? 'true' : 'false';

        return $this->createExplorationLog($event, $logParameters);
    }
}
