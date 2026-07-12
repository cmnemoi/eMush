<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Relax;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\MushMessageEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RelaxCest extends AbstractFunctionalTest
{
    private ActionConfig $relaxConfig;
    private Relax $relaxAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private PlaceServiceInterface $placeService;

    private Player $derek;
    private Player $andie;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->relaxConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::RELAX]);
        $this->relaxAction = $I->grabService(Relax::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->placeService = $I->grabService(PlaceServiceInterface::class);

        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->players->add($this->derek);
        $this->andie = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
        $this->players->add($this->andie);
    }

    public function shouldNotRelaxIfPlayersDidntBond(FunctionalTester $I): void
    {
        $this->givenPlayerIsIn($this->chun, RoomEnum::FRONT_CORRIDOR, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::FRONT_CORRIDOR, $I);
        $this->givenBedInRoom($this->kuanTi->getPlace());

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DO_THE_THING_NOT_INTERESTED, $I);
    }

    public function shouldNotRelaxIfPlayerLyingDown(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsLyingDown($this->kuanTi);

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::RELAX_ASLEEP, $I);
    }

    public function shouldNotRelaxIfCameraPresent(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->givenCameraInRoom($this->chun->getPlace());

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::RELAX_CAMERA, $I);
    }

    public function shouldNotRelaxIfWitnessPresent(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::FRONT_CORRIDOR, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::FRONT_CORRIDOR, $I);
        $this->givenPlayerIsIn($this->derek, RoomEnum::FRONT_CORRIDOR, $I);

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::RELAX_WITNESS, $I);
    }

    public function shouldNotRelaxIfNoBed(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::FRONT_CORRIDOR, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::FRONT_CORRIDOR, $I);

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldRelaxInRefectory(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::REFECTORY, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::REFECTORY, $I);

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldBeExecutable($I);
    }

    public function shouldRelaxIfAllConditionsFilled(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);

        $this->givenBedInRoom($this->chun->getPlace());

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldBeExecutable($I);
    }

    public function canRelaxOnASofa(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);

        $this->givenSofaInRoom($this->chun->getPlace());

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldBeExecutable($I);
    }

    public function shouldNotRelaxIfTargetAlreadyDoneToday(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);
        $this->givenPlayerHasAlreadyRelaxToday($this->kuanTi);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);

        $this->givenBedInRoom($this->chun->getPlace());

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DO_THE_THING_ALREADY_DONE, $I);
    }

    public function shouldNotRelaxIfPlayerAlreadyDoneToday(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);
        $this->givenPlayerHasAlreadyRelaxToday($this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);

        $this->givenBedInRoom($this->chun->getPlace());

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DO_THE_THING_ALREADY_DONE, $I);
    }

    public function shouldNotRelaxOnASofaIfSofaBroken(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->givenBrokenSofaInRoom($this->chun->getPlace());

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldInfectPlayerIfTargetIsMushWithSpore(FunctionalTester $I): void
    {
        $this->givenPlayerIsMushWithSpores($this->kuanTi, 1);

        $this->givenPlayerIsIn($this->andie, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->andie->getPlace());

        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->andie);

        $this->whenATriesToRelaxWithB($this->andie, $this->kuanTi);
        $this->relaxAction->execute();

        $this->thenPlayerHasSpore($this->andie, 1, $I);
        $this->thenPlayerHasSpore($this->kuanTi, 0, $I);

        $I->seeInRepository(
            Message::class,
            ['message' => MushMessageEnum::INFECT_STD]
        );
    }

    public function mushPlayerShouldWasteSporeIfHumanPlayerImmune(FunctionalTester $I): void
    {
        $this->givenPlayerIsMushWithSpores($this->kuanTi, 1);
        $this->givenChunIsImmune();

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->chun->getPlace());

        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);
        $this->relaxAction->execute();

        $this->thenPlayerHasSpore($this->chun, 0, $I);
        $this->thenPlayerHasSpore($this->kuanTi, 0, $I);

        $I->dontSeeInRepository(
            Message::class,
            ['message' => MushMessageEnum::INFECT_STD]
        );
    }

    public function shouldNotInfectPlayerIfMushTargetHasNoSpore(FunctionalTester $I): void
    {
        $this->givenPlayerIsMushWithSpores($this->kuanTi, 0);

        $this->givenPlayerIsIn($this->andie, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->andie->getPlace());

        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->andie);

        $this->whenATriesToRelaxWithB($this->andie, $this->kuanTi);
        $this->relaxAction->execute();

        $this->thenPlayerHasSpore($this->andie, 0, $I);
        $this->thenPlayerHasSpore($this->kuanTi, 0, $I);

        $I->dontSeeInRepository(
            Message::class,
            ['message' => MushMessageEnum::INFECT_STD]
        );
    }

    public function noSporeChangeIfBothPlayersMush(FunctionalTester $I): void
    {
        $this->givenPlayerIsMushWithSpores($this->andie, 1);
        $this->givenPlayerIsMushWithSpores($this->kuanTi, 0);

        $this->givenPlayerIsIn($this->andie, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->andie->getPlace());

        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->andie);

        $this->whenATriesToRelaxWithB($this->andie, $this->kuanTi);
        $this->relaxAction->execute();

        $this->thenPlayerHasSpore($this->andie, 1, $I);
        $this->thenPlayerHasSpore($this->kuanTi, 0, $I);

        $I->dontSeeInRepository(
            Message::class,
            ['message' => MushMessageEnum::INFECT_STD]
        );
    }

    public function theDeadDoNotCountAsWitness(FunctionalTester $I): void
    {
        $this->givenTargetHasBondedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->derek, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->kuanTi->getPlace());

        $this->derek->kill();

        $this->whenATriesToRelaxWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldBeExecutable($I);
    }

    private function givenTargetHasBondedWithPlayer(Player $target, Player $player): void
    {
        $target->setBonds(new PlayerCollection([$player]));
    }

    private function givenPlayerIsLyingDown(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsImmune(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::IMMUNIZED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMushWithSpores(Player $player, int $spores): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
        $player->setSpores($spores);
    }

    private function givenPlayerHasAlreadyRelaxToday(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DID_THE_THING,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenCameraInRoom(Place $place): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CAMERA_EQUIPMENT,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenBedInRoom(Place $place): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::BED,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenSofaInRoom(Place $place): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SWEDISH_SOFA,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenBrokenSofaInRoom(Place $place): void
    {
        $sofa = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SWEDISH_SOFA,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $sofa,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsIn(Player $player, string $place, FunctionalTester $I): void
    {
        if ($this->daedalus->getPlaceByName($place) === null) {
            $placeConfig = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => $place]);
            $this->placeService->createPlace(
                $placeConfig,
                $this->daedalus,
                [],
                new \DateTime(),
            );
        }
        $player->changePlace($this->daedalus->getPlaceByName($place));
    }

    private function whenATriesToRelaxWithB(Player $player, Player $target): void
    {
        $this->relaxAction->loadParameters(
            actionConfig: $this->relaxConfig,
            actionProvider: $player,
            player: $player,
            target: $target
        );
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->relaxAction->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->relaxAction->cannotExecuteReason(),
        );
    }

    private function thenActionShouldBeExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->relaxAction->cannotExecuteReason(), 'Action should be executable');
    }

    private function thenPlayerHasSpore(Player $player, int $spores, FunctionalTester $I): void
    {
        $I->assertEquals($player->getSpores(), $spores);
    }
}
