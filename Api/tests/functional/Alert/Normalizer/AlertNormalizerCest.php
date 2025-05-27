<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Alert\Normalizer;

use Mush\Action\Event\ApplyEffectEvent;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Normalizer\AlertNormalizer;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AlertNormalizerCest extends AbstractFunctionalTest
{
    private AlertNormalizer $alertNormalizer;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Place $laboratory;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->alertNormalizer = $I->grabService(AlertNormalizer::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->laboratory = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);
    }

    public function testNormalizeBrokenEquipmentAlertReports(FunctionalTester $I): void
    {
        $labEquipment = $this->givenThereAre2PieceOfEquipmentInPlace($this->laboratory);

        $bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $bridgeEquipment = $this->givenThereAre2PieceOfEquipmentInPlace($bridge);

        $this->givenIBreakEquipment(array_merge($labEquipment, $bridgeEquipment));

        $this->givenEquipementAreReportedByPlayer($labEquipment, $this->player);

        $this->givenEquipementAreReportedByPlayer([$bridgeEquipment[0]], $this->player);

        $this->givenEquipementAreReportedByPlayer([$bridgeEquipment[1]], $this->player2);

        $normalizedAlertReports = $this->whenINormalizeBrokenEquipmentAlertReports($I);

        // then the normalized alert reports should have the expected values
        $I->assertEquals(
            [
                '**Chun** a reporté **2** équipements endommagés dans le **Laboratoire**.',
                '**Chun** a reporté **1** équipement endommagé sur le **Pont**.',
                '**Kuan Ti** a reporté **1** équipement endommagé sur le **Pont**.',
            ],
            $normalizedAlertReports,
        );
    }

    public function testNormalizeLostCrewmateAlert(FunctionalTester $I): void
    {
        // given Chun is lost
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LOST,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when I normalize the lost crewmate alert
        $alert = $I->grabEntityFromRepository(Alert::class, ['name' => AlertEnum::LOST_CREWMATE]);
        $normalizedAlert = $this->alertNormalizer->normalize($alert);

        // then the normalized alert should have the expected values
        $I->assertEquals(
            expected: [
                'prefix' => 'Alertes :',
                'key' => 'lost_crewmate',
                'name' => 'Équipier perdu',
                'description' => 'Un de vos co-équipiers est perdu sur une planète, vous devriez monter une mission pour le rapatrier !',
                'lostPlayer' => 'chun',
            ],
            actual: $normalizedAlert,
        );
    }

    private function givenThereAre2PieceOfEquipmentInPlace(Place $place): array
    {
        $laboratory = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::RESEARCH_LABORATORY,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );

        $gravitySimulator = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::GRAVITY_SIMULATOR,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );

        return [$laboratory, $gravitySimulator];
    }

    private function givenIBreakEquipment(array $equipments): void
    {
        foreach ($equipments as $equipment) {
            $this->statusService->createStatusFromName(
                statusName: EquipmentStatusEnum::BROKEN,
                holder: $equipment,
                tags: [],
                time: new \DateTime(),
            );
        }
    }

    private function givenEquipementAreReportedByPlayer(array $equipments, Player $player): void
    {
        foreach ($equipments as $equipment) {
            if ($player->getPlace() !== $equipment->getPlace()) {
                $player->changePlace($equipment->getPlace());
            }

            $reportEvent = new ApplyEffectEvent(
                $player,
                $equipment,
                VisibilityEnum::PRIVATE,
                [],
                new \DateTime(),
            );
            $this->eventService->callEvent($reportEvent, ApplyEffectEvent::REPORT_EQUIPMENT);
        }
    }

    private function whenINormalizeBrokenEquipmentAlertReports(FunctionalTester $I): array
    {
        $alert = $I->grabEntityFromRepository(Alert::class, ['name' => AlertEnum::BROKEN_EQUIPMENTS]);

        return $this->alertNormalizer->normalize($alert)['reports'];
    }
}
