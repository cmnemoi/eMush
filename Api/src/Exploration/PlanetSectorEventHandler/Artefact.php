<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Equipment\Enum\ItemEnum;
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
        $artefact = $this->createRandomItemsFromEvent($event)->first();

        $logParameters = $this->getLogParameters($event);
        $logParameters['target_' . $artefact->getLogKey()] = $artefact->getLogName();

        if ($event->getPlanetSector()->getName() === PlanetSectorEnum::INTELLIGENT) {
            $logParameters['has_babel_module'] = $event->getExploration()->hasAFunctionalBabelModule() ? 'true' : 'false';
        }

        // Potential fix to : https://discord.com/channels/693082011484684348/1222470645238071327
        if ($event->hasTag(ItemEnum::BABEL_MODULE)) {
            $logParameters['has_babel_module'] = '////' . $this->translationService->translate(
                'has_babel_module',
                    ['item' => ItemEnum::BABEL_MODULE],
                    'planet_sector_event',
                    $event->getExploration()->getDaedalus()->getLanguage()
                );
        }

        return $this->createExplorationLog($event, $logParameters);
    }
}
