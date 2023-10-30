<?php

namespace Mush\Daedalus\Service;

use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\StatusEnum;

class DaedalusWidgetService implements DaedalusWidgetServiceInterface
{
    private AlertServiceInterface $alertService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        AlertServiceInterface $alertService,
        TranslationServiceInterface $translationService
    ) {
        $this->alertService = $alertService;
        $this->translationService = $translationService;
    }

    public function getMinimap(Daedalus $daedalus, Player $player): array
    {
        $equipmentsProject = false;
        $doorsProject = false;

        $brokenEquipments = $this->getDisplayedBrokenEquipments($daedalus, AlertEnum::BROKEN_EQUIPMENTS, $equipmentsProject);
        $brokenDoors = $this->getDisplayedBrokenEquipments($daedalus, AlertEnum::BROKEN_DOORS, $doorsProject);

        $minimap = [];

        if (!$this->hasPlayerAccessToMinimap($player)) {
            return $minimap;
        }

        $language = $daedalus->getLanguage();

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
                'id' => $room->getId(),
                'key' => $roomName,
                'players_count' => $room->getPlayers()->getPlayerAlive()->count(),
                'actopi' => [],
                'fire' => $this->isFireDisplayed($room),
                'broken_count' => count($brokenEquipmentsList) + count($brokenDoorsList),
                'broken_doors' => $brokenDoorsList,
                'broken_equipments' => $brokenEquipmentsList,
                'name' => $this->translationService->translate($roomName . '.name', [], 'rooms', $language),
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
        // add fire detector project

        // reported fires are now displayed
        return $this->alertService->isFireReported($room);
    }

    private function getDisplayedBrokenEquipments(Daedalus $daedalus, string $alertName, bool $isProject): array
    {
        // get all equipment broken on the ship
        $brokenAlert = $this->alertService->findByNameAndDaedalus($alertName, $daedalus);

        if ($brokenAlert === null) {
            return [];
        }

        $displayedBrokenEquipments = [];

        /** @var AlertElement $alertElement */
        foreach ($brokenAlert->getAlertElements() as $alertElement) {
            // if there is no project only gather reported elements
            if (($isProject || $alertElement->getPlayerInfo() !== null) && ($equipment = $alertElement->getEquipment()) !== null) {
                $roomName = $equipment->getPlace()->getName();
                $equipmentName = $equipment->getName();

                $displayedBrokenEquipments[$roomName][] = $equipmentName;
            }
        }

        return $displayedBrokenEquipments;
    }

    private function hasPlayerAccessToMinimap(Player $player): bool
    {
        $playerHasATracker = $player->hasOperationalEquipmentByName(ItemEnum::ITRACKIE) || $player->hasOperationalEquipmentByName(ItemEnum::TRACKER);
        $playerIsInARoom = $player->getPlace()->getType() === PlaceTypeEnum::ROOM;

        return $playerHasATracker && $playerIsInARoom;
    }
}
