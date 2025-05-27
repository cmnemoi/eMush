<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\DoTheThing;
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
final class DoTheThingCest extends AbstractFunctionalTest
{
    private ActionConfig $doTheThingConfig;
    private DoTheThing $doTheThingAction;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private PlaceServiceInterface $placeService;

    private Player $derek;
    private Player $andie;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->doTheThingConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::DO_THE_THING]);
        $this->doTheThingAction = $I->grabService(DoTheThing::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->placeService = $I->grabService(PlaceServiceInterface::class);

        $this->derek = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
        $this->players->add($this->derek);
        $this->andie = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::ANDIE);
        $this->players->add($this->andie);

        $this->givenFreeLoveIs(false);
    }

    public function shouldNotDTTIfPlayersSameGenderAndFreeLoveFalse(FunctionalTester $I)
    {
        $this->whenATriesToDTTWithB($this->derek, $this->kuanTi);

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldNotDTTIfPlayersDidntFlirt(FunctionalTester $I)
    {
        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DO_THE_THING_NOT_INTERESTED, $I);
    }

    public function shouldNotDTTIfPlayerLyingDown(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsLyingDown($this->kuanTi);

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DO_THE_THING_ASLEEP, $I);
    }

    public function shouldNotDTTIfCameraPresent(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);

        $this->givenCameraInRoom($this->chun->getPlace());

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DO_THE_THING_CAMERA, $I);
    }

    public function shouldNotDTTIfWitnessPresent(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::FRONT_CORRIDOR, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::FRONT_CORRIDOR, $I);
        $this->givenPlayerIsIn($this->derek, RoomEnum::FRONT_CORRIDOR, $I);

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DO_THE_THING_WITNESS, $I);
    }

    public function shouldNotDTTIfNoBed(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::FRONT_CORRIDOR, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::FRONT_CORRIDOR, $I);

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);

        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldDTTIfAllConditionsFilled(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);

        $this->givenBedInRoom($this->chun->getPlace());

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldBeExecutable($I);
    }

    public function canDTTOnASofa(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);

        $this->givenSofaInRoom($this->chun->getPlace());

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldBeExecutable($I);
    }

    public function canDTTWithAndieAsFemaleWithFreeLoveFalse(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->andie, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->andie, RoomEnum::MEDLAB, $I);

        $this->givenBedInRoom($this->chun->getPlace());

        $this->whenATriesToDTTWithB($this->chun, $this->andie);
        $this->thenActionShouldBeExecutable($I);
    }

    public function canDTTWithAndieAsMaleWithFreeLoveFalse(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->andie, $this->kuanTi);

        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->andie, RoomEnum::MEDLAB, $I);

        $this->givenBedInRoom($this->kuanTi->getPlace());

        $this->whenATriesToDTTWithB($this->kuanTi, $this->andie);
        $this->thenActionShouldBeExecutable($I);
    }

    public function canDTTSameGenderWithFreeLoveTrue(FunctionalTester $I)
    {
        $this->givenFreeLoveIs(true);

        $this->givenTargetHasFlirtedWithPlayer($this->derek, $this->kuanTi);

        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->derek, RoomEnum::MEDLAB, $I);

        $this->givenBedInRoom($this->kuanTi->getPlace());

        $this->whenATriesToDTTWithB($this->kuanTi, $this->derek);
        $this->thenActionShouldBeExecutable($I);
    }

    public function shouldNotDTTIfTargetAlreadyDoneToday(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);
        $this->givenPlayerHasAlreadyDTTToday($this->kuanTi);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);

        $this->givenBedInRoom($this->chun->getPlace());

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DO_THE_THING_ALREADY_DONE, $I);
    }

    public function shouldNotDTTIfPlayerAlreadyDoneToday(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);
        $this->givenPlayerHasAlreadyDTTToday($this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);

        $this->givenBedInRoom($this->chun->getPlace());

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldNotBeExecutableWithMessage(ActionImpossibleCauseEnum::DO_THE_THING_ALREADY_DONE, $I);
    }

    public function shouldNotDTTOnASofaIfSofaBroken(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);

        $this->givenBrokenSofaInRoom($this->chun->getPlace());

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldNotBeVisible($I);
    }

    public function shouldInfectPlayerIfTargetIsMushWithSpore(FunctionalTester $I)
    {
        $this->givenPlayerIsMushWithSpores($this->kuanTi, 1);

        $this->givenPlayerIsIn($this->andie, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->andie->getPlace());

        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->andie);

        $this->whenATriesToDTTWithB($this->andie, $this->kuanTi);
        $this->doTheThingAction->execute();

        $this->thenPlayerHasSpore($this->andie, 1, $I);
        $this->thenPlayerHasSpore($this->kuanTi, 0, $I);

        $I->seeInRepository(
            Message::class,
            ['message' => MushMessageEnum::INFECT_STD]
        );
    }

    public function mushPlayerShouldWasteSporeIfHumanPlayerImmune(FunctionalTester $I)
    {
        $this->givenPlayerIsMushWithSpores($this->kuanTi, 1);
        $this->givenChunIsImmune();

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->chun->getPlace());

        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);
        $this->doTheThingAction->execute();

        $this->thenPlayerHasSpore($this->chun, 0, $I);
        $this->thenPlayerHasSpore($this->kuanTi, 0, $I);

        $I->dontSeeInRepository(
            Message::class,
            ['message' => MushMessageEnum::INFECT_STD]
        );
    }

    public function shouldNotInfectPlayerIfMushTargetHasNoSpore(FunctionalTester $I)
    {
        $this->givenPlayerIsMushWithSpores($this->kuanTi, 0);

        $this->givenPlayerIsIn($this->andie, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->andie->getPlace());

        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->andie);

        $this->whenATriesToDTTWithB($this->andie, $this->kuanTi);
        $this->doTheThingAction->execute();

        $this->thenPlayerHasSpore($this->andie, 0, $I);
        $this->thenPlayerHasSpore($this->kuanTi, 0, $I);

        $I->dontSeeInRepository(
            Message::class,
            ['message' => MushMessageEnum::INFECT_STD]
        );
    }

    public function noSporeChangeIfBothPlayersMush(FunctionalTester $I)
    {
        $this->givenPlayerIsMushWithSpores($this->andie, 1);
        $this->givenPlayerIsMushWithSpores($this->kuanTi, 0);

        $this->givenPlayerIsIn($this->andie, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->andie->getPlace());

        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->andie);

        $this->whenATriesToDTTWithB($this->andie, $this->kuanTi);
        $this->doTheThingAction->execute();

        $this->thenPlayerHasSpore($this->andie, 1, $I);
        $this->thenPlayerHasSpore($this->kuanTi, 0, $I);

        $I->dontSeeInRepository(
            Message::class,
            ['message' => MushMessageEnum::INFECT_STD]
        );
    }

    public function theDeadDoNotCountAsWitness(FunctionalTester $I)
    {
        $this->givenTargetHasFlirtedWithPlayer($this->kuanTi, $this->chun);

        $this->givenPlayerIsIn($this->chun, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->kuanTi, RoomEnum::MEDLAB, $I);
        $this->givenPlayerIsIn($this->derek, RoomEnum::MEDLAB, $I);
        $this->givenBedInRoom($this->kuanTi->getPlace());

        $this->derek->kill();

        $this->whenATriesToDTTWithB($this->chun, $this->kuanTi);
        $this->thenActionShouldBeExecutable($I);
    }

    private function givenFreeLoveIs(bool $bool)
    {
        $this->daedalus->getDaedalusConfig()->setFreeLove($bool);
    }

    private function givenTargetHasFlirtedWithPlayer(Player $target, Player $player)
    {
        $target->setFlirts(new PlayerCollection([$player]));
    }

    private function givenPlayerIsLyingDown(Player $player)
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenChunIsImmune()
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::IMMUNIZED,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsMushWithSpores(Player $player, int $spores)
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
        $player->setSpores($spores);
    }

    private function givenPlayerHasAlreadyDTTToday(Player $player)
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::DID_THE_THING,
            holder: $player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenCameraInRoom(Place $place)
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CAMERA_EQUIPMENT,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenBedInRoom(Place $place)
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::BED,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenSofaInRoom(Place $place)
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SWEDISH_SOFA,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenBrokenSofaInRoom(Place $place)
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

    private function givenPlayerIsIn(Player $player, string $place, FunctionalTester $I)
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

    private function whenATriesToDTTWithB(Player $player, Player $target)
    {
        $this->doTheThingAction->loadParameters(
            actionConfig: $this->doTheThingConfig,
            actionProvider: $player,
            player: $player,
            target: $target
        );
    }

    private function thenActionShouldNotBeVisible(FunctionalTester $I): void
    {
        $I->assertFalse($this->doTheThingAction->isVisible());
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->doTheThingAction->cannotExecuteReason(),
        );
    }

    private function thenActionShouldBeExecutable(FunctionalTester $I): void
    {
        $I->assertNull($this->doTheThingAction->cannotExecuteReason(), 'Action should be executable');
    }

    private function thenPlayerHasSpore(Player $player, int $spores, FunctionalTester $I)
    {
        $I->assertEquals($player->getSpores(), $spores);
    }
}
