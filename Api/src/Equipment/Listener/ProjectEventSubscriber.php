<?php

declare(strict_types=1);

namespace Mush\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\Config\ReplaceEquipmentConfig;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Event\ProjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ProjectEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly GameEquipmentService $gameEquipmentService,
        private readonly RandomServiceInterface $randomService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProjectEvent::PROJECT_FINISHED => 'onProjectFinished',
        ];
    }

    public function onProjectFinished(ProjectEvent $projectEvent): void
    {
        $this->spawnProjectEquipment($projectEvent);
        $this->replaceProjectEquipment($projectEvent);

        if ($projectEvent->getProject()->getName() === ProjectName::TRASH_LOAD->toString()) {
            $this->handleThrashLoad($projectEvent);
        }
    }

    private function spawnProjectEquipment(ProjectEvent $projectEvent): void
    {
        $project = $projectEvent->getProject();

        foreach ($project->getSpawnEquipmentConfigs() as $spawnEquipmentConfig) {
            $holder = $projectEvent->getDaedalus()->getPlaceByNameOrThrow($spawnEquipmentConfig->getPlaceName());
            $this->gameEquipmentService->createGameEquipmentsFromName(
                equipmentName: $spawnEquipmentConfig->getEquipmentName(),
                equipmentHolder: $holder,
                reasons: $projectEvent->getTags(),
                time: $projectEvent->getTime(),
                quantity: $spawnEquipmentConfig->getQuantity(),
            );
        }
    }

    private function replaceProjectEquipment(ProjectEvent $projectEvent): void
    {
        $project = $projectEvent->getProject();

        foreach ($project->getReplaceEquipmentConfigs() as $replaceEquipmentConfig) {
            $equipmentToReplace = $this->equipmentToReplace($replaceEquipmentConfig, $projectEvent);

            foreach ($equipmentToReplace as $replacedEquipment) {
                $holder = $replacedEquipment->getHolder();
                $this->gameEquipmentService->transformGameEquipmentToEquipmentWithName(
                    newEquipmentName: $replaceEquipmentConfig->getEquipmentName(),
                    input: $replacedEquipment,
                    holder: $holder,
                    reasons: $projectEvent->getTags(),
                    time: $projectEvent->getTime(),
                );
            }
        }
    }

    private function equipmentToReplace(ReplaceEquipmentConfig $replaceEquipmentConfig, ProjectEvent $projectEvent): ArrayCollection
    {
        $daedalus = $projectEvent->getDaedalus();

        $equipmentName = $replaceEquipmentConfig->getReplacedEquipmentName();
        $quantity = $replaceEquipmentConfig->getQuantity();

        if ($replaceEquipmentConfig->shouldReplaceInSpecificPlace()) {
            return $this->findEquipmentForSpecificPlace($replaceEquipmentConfig, $projectEvent, $equipmentName, $quantity);
        }

        return $this->gameEquipmentService->findEquipmentsByNameAndDaedalus($equipmentName, $daedalus);
    }

    private function findEquipmentForSpecificPlace(
        ReplaceEquipmentConfig $replaceEquipmentConfig,
        ProjectEvent $projectEvent,
        string $equipmentName,
        int $quantity
    ): ArrayCollection {
        $place = $projectEvent->getDaedalus()->getPlaceByNameOrThrow($replaceEquipmentConfig->getPlaceName());
        $equipments = $this->gameEquipmentService->findEquipmentByNameAndPlace($equipmentName, $place, $quantity);

        if (!$equipments->isEmpty()) {
            return $equipments;
        }

        $equipments = $this->gameEquipmentService->findEquipmentByNameAndPlayer($equipmentName, $projectEvent->getAuthor(), $quantity);

        if ($equipments->isEmpty()) {
            throw new \RuntimeException("No equipment found for replacement: {$equipmentName}.");
        }

        return $equipments;
    }

    private function handleThrashLoad(ProjectEvent $projectEvent): void
    {
        $daedalus = $projectEvent->getDaedalus();
        $itemsSelected = $this->randomService->getRandomElementsFromProbaCollection($daedalus->getDaedalusConfig()->geTtrashLoadInventory(), 5);
        $storages = $daedalus->getStorages()->toArray();

        foreach ($itemsSelected as $item) {
            $position = strrpos($item, '_bis');
            if (\is_int($position)) {
                $item = substr($item, 0, $position);
            }

            $equipment = $this->gameEquipmentService->createGameEquipmentFromName(
                $item,
                $this->randomService->getRandomElement($storages),
                $projectEvent->getTags(),
                $projectEvent->getTime()
            );

            if ($equipment->hasMechanicByName(EquipmentMechanicEnum::BLUEPRINT)) {
                $daedalus->getUniqueItems()->makeStartingBlueprintsUnique([$equipment->getName()]);
            }
        }
    }
}
