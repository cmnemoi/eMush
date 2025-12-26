<?php

namespace Mush\Tests\functional\Disease\Listener;

use Mush\Action\Actions\Consume;
use Mush\Action\Actions\Move;
use Mush\Action\Actions\TakeCat;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\WeaponEventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SymptomsActionSubscriberCest extends AbstractFunctionalTest
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;

    private Move $move;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->move = $I->grabService(Move::class);

        $medlab = $this->createExtraPlace(RoomEnum::MEDLAB, $I, $this->daedalus);
        $door = Door::createFromRooms($this->player->getPlace(), $medlab);
        $door->setEquipment($I->grabEntityFromRepository(EquipmentConfig::class, ['name' => 'door_default']));
        $I->haveInRepository($door);

        $this->move->loadParameters(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::MOVE]),
            actionProvider: $door,
            player: $this->player,
            target: $door,
        );
    }

    public function testBreakoutsSymptom(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DiseaseEnum::SKIN_INFLAMMATION);

        // given breakouts_on_move does not have random_16 requirement
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::BREAKOUTS)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements([]);

        // when player moves
        $this->move->execute();

        $this->thenISeeTheFollowingLogInPlayerRoom(SymptomEnum::BREAKOUTS, $I);
    }

    public function testCatAllergySymptom(FunctionalTester $I)
    {
        $this->player->setHealthPoint(10);

        $this->givenPlayerHasDisease(DiseaseEnum::CAT_ALLERGY);

        $schrodinger = $this->createEquipment(ItemEnum::SCHRODINGER, $this->player->getPlace());

        $takeCat = $I->grabService(TakeCat::class);

        $takeCat->loadParameters(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::TAKE_CAT])->setInjuryRate(0),
            actionProvider: $schrodinger,
            player: $this->player,
            target: $schrodinger,
        );

        $takeCat->execute();

        $I->assertEquals(4, $this->player->getHealthPoint());

        $I->assertTrue($this->player->hasMedicalConditionByName(DiseaseEnum::QUINCKS_OEDEMA));

        $I->assertTrue($this->player->hasAnyMedicalConditionByName([InjuryEnum::BURNT_ARMS, InjuryEnum::BURNT_HAND]));

        $this->thenISeeTheFollowingLogInPlayerRoom(SymptomEnum::CAT_ALLERGY, $I);
    }

    public function testDroolingSymptom(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DiseaseEnum::SPACE_RABIES);

        // given drooling_on_move does not have random_16 requirement
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::DROOLING)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements([]);

        // when player moves
        $this->move->execute();

        $this->thenISeeTheFollowingLogInPlayerRoom(SymptomEnum::DROOLING, $I);
    }

    public function testFoamingMouthSymptom(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DiseaseEnum::SPACE_RABIES);

        // given foaming_on_move does not have random_16 requirement
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::FOAMING_MOUTH)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements([]);

        // when player moves
        $this->move->execute();

        $this->thenISeeTheFollowingLogInPlayerRoom(SymptomEnum::FOAMING_MOUTH, $I);
    }

    public function testCatSneezingSymptom(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DiseaseEnum::CAT_ALLERGY);

        // given cat_sneezing_on_move does not have random_16 requirement
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::SNEEZING)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements([
                $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => 'item_in_room_schrodinger']),
            ]);

        $this->createEquipment(ItemEnum::SCHRODINGER, $this->daedalus->getPlaceByName(RoomEnum::MEDLAB));

        $this->move->execute();

        $this->thenISeeTheFollowingLogInPlayerRoom(SymptomEnum::SNEEZING, $I);
    }

    public function testMushSneezingSymptom(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DiseaseEnum::MUSH_ALLERGY);

        // given mush_sneezing does not have random_16 requirement
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::SNEEZING)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements([
                $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => 'player_in_room_mush']),
            ]);

        // given player2 is Mush in medlab
        $this->convertPlayerToMush($I, $this->player2);
        $this->player2->setPlace($this->daedalus->getPlaceByName(RoomEnum::MEDLAB));

        $this->move->execute();

        $this->thenISeeTheFollowingLogInPlayerRoom(SymptomEnum::SNEEZING, $I);
    }

    public function testVomitingOnConsumeSymptom(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DiseaseEnum::FOOD_POISONING);

        $consume = $I->grabService(Consume::class);

        $ration = $this->createEquipment(GameRationEnum::COOKED_RATION, $this->player->getPlace());

        $consume->loadParameters(
            actionConfig: $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::CONSUME]),
            actionProvider: $ration,
            player: $this->player,
            target: $ration,
        );

        $consume->execute();

        $this->thenISeeTheFollowingLogInPlayerRoom(SymptomEnum::VOMITING, $I);
    }

    public function testVomitingOnMoveSymptom(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DiseaseEnum::SLIGHT_NAUSEA);

        // given vomiting_move_random_40 does not have random_40 requirement
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::VOMITING)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements([]);

        $this->move->execute();

        $this->thenISeeTheFollowingLogInPlayerRoom(SymptomEnum::VOMITING, $I);
    }

    public function testFearOfCatsSymptom(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DisorderEnum::AILUROPHOBIA);

        // given fear_of_cat_on_move does not have random_50 requirement
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::FEAR_OF_CATS)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements([
                $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => 'item_in_room_schrodinger']),
            ]);

        $this->createEquipment(ItemEnum::SCHRODINGER, $this->daedalus->getPlaceByName(RoomEnum::MEDLAB));

        $this->move->execute();

        $this->thenISeeTheFollowingLogInMedlab(SymptomEnum::FEAR_OF_CATS, $I);

        $this->thenPlayerIsBackInLab($I);
    }

    public function testPsychoticAttackSymptomNeedsTarget(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DisorderEnum::PSYCHOTIC_EPISODE);
        // given a blaster in hand
        $this->createEquipment(ItemEnum::BLASTER, $this->player);

        // given psychotic_attacks_on_move does not have random_16 requirement
        $notAloneRequirement = $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => 'player_in_room_not_alone']);
        $hasWeaponRequirement = $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => ModifierRequirementEnum::PLAYER_ANY_WEAPON]);
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::PSYCHOTIC_ATTACKS)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements(
                [$notAloneRequirement, $hasWeaponRequirement]
            );

        $this->move->execute();
        $this->thenNothingHappens(SymptomEnum::PSYCHOTIC_ATTACKS, $I);
    }

    public function testPsychoticAttackSymptomNeedsWeapon(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DisorderEnum::PSYCHOTIC_EPISODE);
        // given a target in room
        $this->player2->setPlace($this->daedalus->getPlaceByName(RoomEnum::MEDLAB));

        // given psychotic_attacks_on_move does not have random_16 requirement
        $notAloneRequirement = $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => 'player_in_room_not_alone']);
        $hasWeaponRequirement = $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => ModifierRequirementEnum::PLAYER_ANY_WEAPON]);
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::PSYCHOTIC_ATTACKS)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements(
                [$notAloneRequirement, $hasWeaponRequirement]
            );

        $this->move->execute();
        $this->thenNothingHappens(SymptomEnum::PSYCHOTIC_ATTACKS, $I);
    }

    public function testPsychoticAttackSymptom(FunctionalTester $I)
    {
        $this->givenPlayerHasDisease(DisorderEnum::PSYCHOTIC_EPISODE);
        // given a blaster
        $blaster = $this->createEquipment(ItemEnum::BLASTER, $this->player);
        // given a target in room
        $this->player2->setPlace($this->daedalus->getPlaceByName(RoomEnum::MEDLAB));

        // given blaster always misses
        $blaster->getWeaponMechanicOrThrow()
            ->setBaseAccuracy(0)
            ->setFailedEventKeys(
                [WeaponEventEnum::BLASTER_FAILED_SHOT->toString() => 1]
            );

        // given psychotic_attacks_on_move does not have random_16 requirement
        $notAloneRequirement = $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => 'player_in_room_not_alone']);
        $hasWeaponRequirement = $I->grabEntityFromRepository(ModifierActivationRequirement::class, ['name' => ModifierRequirementEnum::PLAYER_ANY_WEAPON]);
        $this->player->getModifiers()
            ->getByModifierNameOrThrow(SymptomEnum::PSYCHOTIC_ATTACKS)->getEventModifierConfigOrThrow()
            ->setModifierActivationRequirements(
                [$notAloneRequirement, $hasWeaponRequirement]
            );

        $this->move->execute();

        $this->thenModeratorsSeeTheFollowingLogInPlayerRoom(SymptomEnum::PSYCHOTIC_ATTACKS, $I);
        $this->thenPlayerHasShotAtPlayer2($I);
    }

    // biting happens on cycle change, not on action -- no test needed here (go see Api/tests/functional/Disease/Event/PlayerEventCest.php)

    private function givenPlayerHasDisease(string $diseaseName): PlayerDisease
    {
        return $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: $diseaseName,
            player: $this->player,
            reasons: [],
        );
    }

    private function thenISeeTheFollowingLogInPlayerRoom(string $logKey, FunctionalTester $I)
    {
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo()->getId(),
                'log' => $logKey,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    private function thenModeratorsSeeTheFollowingLogInPlayerRoom(string $logKey, FunctionalTester $I)
    {
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo()->getId(),
                'log' => $logKey,
                'visibility' => VisibilityEnum::HIDDEN,
            ]
        );
    }

    private function thenNothingHappens(string $logKey, FunctionalTester $I)
    {
        $I->dontSeeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo()->getId(),
                'log' => $logKey,
                'visibility' => VisibilityEnum::HIDDEN,
            ]
        );
    }

    private function thenISeeTheFollowingLogInMedlab(string $logKey, FunctionalTester $I)
    {
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => RoomEnum::MEDLAB,
                'playerInfo' => $this->player->getPlayerInfo()->getId(),
                'log' => $logKey,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    private function thenPlayerIsBackInLab(FunctionalTester $I)
    {
        $I->assertTrue($this->player->getPlace()->getName() === RoomEnum::LABORATORY);
    }

    private function thenPlayerHasShotAtPlayer2(FunctionalTester $I)
    {
        $I->seeInRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo()->getId(),
                'log' => WeaponEventEnum::BLASTER_FAILED_SHOT->toString(),
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }
}
