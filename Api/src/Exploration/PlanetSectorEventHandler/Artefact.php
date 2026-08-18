<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class Artefact extends AbstractLootItemsEventHandler
{
    protected StatusServiceInterface $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
    ) {
        parent::__construct($entityManager, $eventService, $randomService, $translationService, $gameEquipmentService);
        $this->statusService = $statusService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::ARTEFACT;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        // Artefact event creates only one item
        /** @var GameEquipment $artefact */
        $artefact = $this->createRandomItemsFromEvent($event)->first();

        $this->handleSabotage($artefact, $event);

        $logParameters = $this->getLogParameters($event);
        $logParameters['target_' . $artefact->getLogKey()] = $artefact->getLogName();

        $babelWorked = $event->getPlanetSector()->getName() === PlanetSectorEnum::INTELLIGENT && $event->getExploration()->hasAFunctionalBabelModule();
        $logParameters['used_babel_module'] = $babelWorked ? 'true' : 'false';

        return $this->createExplorationLog($event, $logParameters);
    }

    private function handleSabotage(GameEquipment $gameEquipment, PlanetSectorEvent $event): void
    {
        $exploration = $event->getExploration();

        if ($gameEquipment->getName() === ItemEnum::TREASURE_HUNT_PET && $exploration->isSabotaged()) {
            $this->statusService->createStatusFromName(
                EquipmentStatusEnum::BABY_SKINNER_INFECTED,
                $gameEquipment,
                $event->getTags(),
                $event->getTime(),
                $exploration->getActiveExplorators()->getMushPlayer()->getFirstOrThrow()
            );
        }
    }
}
