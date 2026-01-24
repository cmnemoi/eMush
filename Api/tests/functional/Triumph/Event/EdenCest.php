<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Triumph\Event;

use Mush\Action\Actions\Graft;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractExplorationTester;
use Mush\Tests\FunctionalTester;
use Mush\Triumph\Enum\TriumphEnum;

/**
 * @internal
 */
final class EdenCest extends AbstractExplorationTester
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // Given Chun and Kuan Ti in the ship
    }

    public function testTwoHumanMenEden(FunctionalTester $I): void
    {
        $this->givenPlayerDies($this->chun);

        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);

        $this->givenEveryoneHasTriumph(4);

        $this->whenDaedalusTravelsToEden();

        $aliveHumansTriumph = [
            $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph(),
            $gioele->getPlayerInfo()->getClosedPlayer()->getTriumph(),
        ];
        // triumph: 4 initial + 6 (eden_at_least) + 2 (eden_one_man) - 4 (eden_no_cat) + 8 to random person (lander)
        $I->assertEqualsCanonicalizing([16, 8], $aliveHumansTriumph);
        $I->assertEquals(4, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testTwoMushMenEden(FunctionalTester $I): void
    {
        $this->givenPlayerDies($this->chun);

        $gioele = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::GIOELE);
        $this->convertPlayerToMush($I, $this->kuanTi);
        $this->convertPlayerToMush($I, $gioele);

        $this->givenEveryoneHasTriumph(0);

        $this->whenDaedalusTravelsToEden();

        $aliveMushTriumph = [
            $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph(),
            $gioele->getPlayerInfo()->getClosedPlayer()->getTriumph(),
        ];
        // triumph: 32 (eden_mush_invasion) + 8 to random person (lander)
        $I->assertEqualsCanonicalizing([40, 32], $aliveMushTriumph);
        $I->assertEquals(0, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testHumanAndCatEden(FunctionalTester $I): void
    {
        $this->givenCatInShip();
        $this->givenPlayerDies($this->chun);

        $this->whenDaedalusTravelsToEden();

        // triumph: 6 (eden_at_least) + 1 (eden_one_man) + 4 (eden_cat) + 8 (lander)
        $I->assertEquals(19, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(0, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testHumanAndDeadCatEden(FunctionalTester $I): void
    {
        $cat = $this->givenCatInShip();
        $this->givenPlayerDies($this->chun);
        $this->givenCatIsShot($cat);

        $this->givenEveryoneHasTriumph(4);

        $this->whenDaedalusTravelsToEden();

        // triumph: 4 initial + 6 (eden_at_least) + 1 (eden_one_man) - 4 (eden_no_cat) + 8 (lander)
        $I->assertEquals(15, $this->kuanTi->getTriumph());
        $I->assertEquals(4, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testHumanAndInfectedCatEden(FunctionalTester $I): void
    {
        $cat = $this->givenCatInShip();
        $this->givenPlayerDies($this->chun);
        $this->givenCatIsInfected($cat);

        $this->givenEveryoneHasTriumph(8);

        $this->whenDaedalusTravelsToEden();

        // triumph: 8 initial + 6 (eden_at_least) + 1 (eden_one_man) - 8 (eden_mush_cat) + 8 (lander)
        $I->assertEquals(15, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(8, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testPregnantEden(FunctionalTester $I): void
    {
        $this->givenNoLanderTriumphGain();

        $paola = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::PAOLA);
        $this->convertPlayerToMush($I, $paola);

        $this->givenPregnant($this->chun);
        $this->givenPregnant($paola);

        $this->givenEveryoneHasTriumph(20);

        $this->whenDaedalusTravelsToEden();

        // human triumph: 20 initial - 4 (eden_no_cat) - 16 (eden_mush_intruder)
        //                + 6 (eden_at_least) + 3 (eden_one_man) + 16 (pregnant_in_eden) + 4 (eden_sexy)
        // Chun triumph: human triumph + 4 (eden_pregnant) + 4 (remedy)
        // mush triumph: 20 initial + 32 (eden_mush_invasion) + 4 (eden_pregnant)
        $I->assertEquals(37, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(29, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(56, $paola->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testSickEden(FunctionalTester $I): void
    {
        $this->givenNoLanderTriumphGain();

        $this->givenPlayerGetsActiveMedicalCondition($this->chun, DiseaseEnum::FLU->toString());
        $this->givenPlayerGetsActiveMedicalCondition($this->chun, DiseaseEnum::GASTROENTERIS->toString());
        $this->givenPlayerGetsActiveMedicalCondition($this->chun, DiseaseEnum::ACID_REFLUX->toString());
        $this->givenPlayerGetsActiveMedicalCondition($this->kuanTi, DiseaseEnum::ACID_REFLUX->toString());

        $this->givenEveryoneHasTriumph(12);

        $this->whenDaedalusTravelsToEden();

        // human triumph: 12 initial - 4 (eden_no_cat) - 8 (eden_microbes)
        //                + 6 (eden_at_least) + 2 (eden_one_man) + 4 (eden_sexy) + 4 for Chun (remedy)
        $I->assertEquals(16, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(12, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testIllnessCuredAndDisorderInjuryEden(FunctionalTester $I): void
    {
        $this->givenNoLanderTriumphGain();

        $chunFlu = $this->givenPlayerGetsActiveMedicalCondition($this->chun, DiseaseEnum::FLU->toString());
        $this->givenPlayerGetsActiveMedicalCondition($this->kuanTi, DisorderEnum::AGORAPHOBIA->toString());
        $this->givenPlayerGetsActiveMedicalCondition($this->kuanTi, InjuryEnum::BROKEN_FINGER->toString());
        $this->givenCuredMedicalCondition($chunFlu); // Chun no longer ill

        $this->givenEveryoneHasTriumph(12);

        $this->whenDaedalusTravelsToEden();

        // human triumph: 12 initial - 4 (eden_no_cat)
        //                + 6 (eden_at_least) + 2 (eden_one_man) + 4 (eden_sexy) + 4 for Chun (remedy)
        $I->assertEquals(24, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(20, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testSilentIllnessEden(FunctionalTester $I): void
    {
        $this->givenNoLanderTriumphGain();

        $this->givenPlayerGetsIncubatingMedicalCondition($this->chun, DiseaseEnum::FLU->toString());

        $this->givenEveryoneHasTriumph(12);

        $this->whenDaedalusTravelsToEden();

        // human triumph: 12 initial - 4 (eden_no_cat) - 4 (eden_microbes)
        //                + 6 (eden_at_least) + 2 (eden_one_man) + 4 (eden_sexy) + 4 for Chun (remedy)
        $I->assertEquals(20, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(16, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    public function testAlienPlantEden(FunctionalTester $I): void
    {
        $this->givenNoLanderTriumphGain();
        $this->givenPlayerDies($this->chun);
        $ian = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::IAN);

        // Given alien plants that will count
        $this->givenItemInDaedalus(GamePlantEnum::CREEPIST);
        $this->givenItemInDaedalus(GamePlantEnum::PRECATUS);

        // Given items that don't count (duplicate alien plants, non-alien plant, alien fruit)
        $this->givenItemInDaedalus(GamePlantEnum::CREEPIST);
        $this->givenItemInDaedalus(GamePlantEnum::CREEPIST);
        $this->givenItemInDaedalus(GamePlantEnum::CREEPIST);
        $this->givenItemInDaedalus(GamePlantEnum::BANANA_TREE);
        $this->givenItemInDaedalus(GameFruitEnum::KUBINUS);
        $this->givenItemInDaedalus(GameFruitEnum::CALEBOOT);
        $this->givenItemInDaedalus(GameFruitEnum::CREEPNUT);
        $this->givenItemInDaedalus(GameFruitEnum::FILANDRA);

        // Given third alien plant that gets grafted to banana tree
        $this->addSkillToPlayer(SkillEnum::BOTANIST, $I, $this->kuanTi);
        $thirdAlienPlant = $this->givenItemInDaedalus(GamePlantEnum::CACTAX, $this->kuanTi);
        $banana = $this->givenItemInDaedalus(GameFruitEnum::BANANA, $this->kuanTi);
        $this->givenPlantGraftedByPlayer($thirdAlienPlant, $banana, $this->kuanTi, $I);

        $this->givenEveryoneHasTriumph(4);

        $this->whenDaedalusTravelsToEden();

        // human triumph: 4 initial - 4 (eden_no_cat) + 6 (eden_at_least) + 2 (eden_one_man) + 2 (eden_alien_plant) + 6 for Ian (eden_alien_plant_plus)
        $I->assertEquals(4, $this->chun->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(10, $this->kuanTi->getPlayerInfo()->getClosedPlayer()->getTriumph());
        $I->assertEquals(16, $ian->getPlayerInfo()->getClosedPlayer()->getTriumph());
    }

    private function givenPlayerDies(Player $player): void
    {
        $this->playerService->killPlayer(
            player: $player,
            endReason: EndCauseEnum::ALIEN_ABDUCTED,
        );
        $player->setTriumph(0);
    }

    private function givenEveryoneHasTriumph(int $quantity): void
    {
        /** @var Player $player */
        foreach ($this->daedalus->getPlayers() as $player) {
            $player->setTriumph($quantity);
        }
    }

    private function givenCatInShip(): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SCHRODINGER,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: [],
            time: new \DateTime(),
            author: $this->chun,
        );
    }

    private function givenCatIsShot(GameItem $cat): void
    {
        $interactEvent = new InteractWithEquipmentEvent(
            $cat,
            $this->kuanTi,
            VisibilityEnum::PUBLIC,
            [ActionEnum::SHOOT_CAT->toString()],
            new \DateTime(),
        );
        $interactEvent->addTag('cat_death_tag');
        $this->eventService->callEvent($interactEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    private function givenCatIsInfected(GameItem $cat): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::CAT_INFECTED,
            holder: $cat,
            tags: [],
            time: new \DateTime(),
            target: $this->kuanTi,
        );
    }

    private function givenNoLanderTriumphGain(): void
    {
        $this->daedalus->getGameConfig()->getTriumphConfig()->getByNameOrThrow(TriumphEnum::LANDER)->setQuantity(0);
    }

    private function givenPregnant(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::PREGNANT,
            holder: $player,
            tags: [ActionEnum::DO_THE_THING->toString()],
            time: new \DateTime(),
        );
    }

    private function givenPlayerGetsIncubatingMedicalCondition(Player $player, string $diseaseName): PlayerDisease
    {
        return $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: $diseaseName,
            player: $player,
            reasons: [],
            delayMin: 3,
            delayLength: 0,
        );
    }

    private function givenPlayerGetsActiveMedicalCondition(Player $player, string $diseaseName): PlayerDisease
    {
        return $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: $diseaseName,
            player: $player,
            reasons: [],
        );
    }

    private function givenCuredMedicalCondition(PlayerDisease $disease): void
    {
        $this->playerDiseaseService->removePlayerDisease(
            playerDisease: $disease,
            causes: [],
            time: new \DateTime(),
            visibility: VisibilityEnum::PRIVATE
        );
    }

    private function givenItemInDaedalus(string $itemName, ?Player $holder = null): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $itemName,
            equipmentHolder: $holder ?? $this->daedalus->getSpace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlantGraftedByPlayer(GameItem $plant, GameItem $fruit, Player $player, FunctionalTester $I): void
    {
        /** @var Graft $graftAction */
        $graftAction = $I->grabService(Graft::class);
        $graftActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::GRAFT]);
        $graftAction->loadParameters(
            actionConfig: $graftActionConfig,
            actionProvider: $fruit,
            player: $player,
            target: $plant,
        );
        $graftAction->execute();
    }

    private function whenDaedalusTravelsToEden(): void
    {
        $event = new DaedalusEvent(
            daedalus: $this->daedalus,
            tags: [ActionEnum::TRAVEL_TO_EDEN->toString()],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($event, DaedalusEvent::FINISH_DAEDALUS);
    }
}
