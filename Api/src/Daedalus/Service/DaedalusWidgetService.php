<?php

namespace Mush\Daedalus\Service;

use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Place\Entity\Place;
use Mush\Status\Enum\StatusEnum;

class DaedalusWidgetService implements DaedalusWidgetServiceInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService,
    ) {
        $this->alertService = $alertService;
    }

    public function getMinimap(Daedalus $daedalus): array
    {
        $equipmentsProject = true;
        $doorsProject = true;

        $brokenEquipments = $this->getDisplayedBrokenEquipments($daedalus, AlertEnum::BROKEN_EQUIPMENTS, $equipmentsProject);
        $brokenDoors = $this->getDisplayedBrokenEquipments($daedalus, AlertEnum::BROKEN_DOORS, $doorsProject);

        $minimap = [];
        foreach ($daedalus->getRooms() as $room) {
            $roomName = $room->getName();

            if (isset($brokenEquipments[$roomName])) {
                $brokenEquipmentsList = $brokenEquipments[$roomName];
            } else {
                $brokenEquipmentsList = [];
            }
            if (isset($brokenDoors[$roomName])) {
                $brokenDoorsList = $brokenDoors[$roomName];
            } else {
                $brokenDoorsList = [];
            }

            $minimap[$roomName] = [
                'players_count' => $room->getPlayers()->count(),
                'actopi' => [],
                'fire' => $this->isFireDisplayed($room),
                'broken_count' => count($brokenEquipmentsList) + count($brokenDoorsList),
                'broken_doors' => $brokenDoorsList,
                'broken_equipments' => $brokenEquipmentsList,
                'name' => $roomName,
                // 'broken_doors' => $doorsProject ? $brokenDoorsList : [],
                // 'broken_equipments' => $equipmentsProject ? $brokenEquipmentsList : [],
            ];
        }

        return $minimap;
    }

    private function isFireDisplayed(Place $room): bool
    {
        if ($room->getStatusByName(StatusEnum::FIRE) === null) {
            return false;
        }
        //add fire detector project

        //reported fires are now displayed
        return $this->alertService->isFireReported($room);
    }

    private function getDisplayedBrokenEquipments(Daedalus $daedalus, string $alertName, bool $isProject): array
    {
        //get all equipment broken on the ship
        $brokenAlert = $this->alertService->findByNameAndDaedalus($alertName, $daedalus);

        if ($brokenAlert === null) {
            return [];
        }

        $displayedBrokenEquipments = [];

        /** @var AlertElement $alertElement */
        foreach ($brokenAlert->getAlertElements() as $alertElement) {
            //if there is no project only gather reported elements
            if (($isProject || $alertElement->getPlayer() !== null) && ($equipment = $alertElement->getEquipment()) !== null) {
                $roomName = $equipment->getPlace()->getName();
                $equipmentName = $equipment->getName();

                $displayedBrokenEquipments[$roomName][] = $equipmentName;
            }
        }

        return $displayedBrokenEquipments;
    }
}
