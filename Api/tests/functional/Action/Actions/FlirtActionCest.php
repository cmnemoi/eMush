<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Flirt;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\DataFixtures\LocalizationConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\User\Entity\User;

class FlirtActionCest
{
    private Flirt $flirtAction;

    public function _before(FunctionalTester $I)
    {
        $this->flirtAction = $I->grabService(Flirt::class);
    }

    public function testFlirt(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);
        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $characterConfig2);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->flirtAction->isVisible());
        $I->assertNull($this->flirtAction->cannotExecuteReason());

        $this->flirtAction->execute();

        $I->assertEquals(1, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::FLIRT_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertTrue($player->HasFlirtedWith($targetPlayer));
        $I->assertFalse($targetPlayer->HasFlirtedWith($player));
    }

    public function testCoupleOfMenFlirt(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHAO,
            'characterName' => CharacterEnum::CHAO,
            'actions' => new ArrayCollection([$action]),
        ]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);
        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $characterConfig2);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertFalse($this->flirtAction->isVisible());
    }

    public function testCoupleOfWomenFlirt(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::PAOLA,
            'characterName' => CharacterEnum::PAOLA,
            'actions' => new ArrayCollection([$action]),
        ]);
        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $characterConfig2);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertFalse($this->flirtAction->isVisible());
    }

    public function testAndieAndWomanFlirt(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::ANDIE,
            'characterName' => CharacterEnum::ANDIE,
            'actions' => new ArrayCollection([$action]),
        ]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::CHUN,
            'characterName' => CharacterEnum::CHUN,
            'actions' => new ArrayCollection([$action]),
        ]);
        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $characterConfig2);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->flirtAction->isVisible());
        $I->assertNull($this->flirtAction->cannotExecuteReason());

        $this->flirtAction->execute();

        $I->assertEquals(1, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::FLIRT_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertTrue($player->HasFlirtedWith($targetPlayer));
        $I->assertFalse($targetPlayer->HasFlirtedWith($player));
    }

    public function testAndieAndManFlirt(FunctionalTester $I)
    {
        $I->loadFixtures([GameConfigFixtures::class, LocalizationConfigFixtures::class]);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['cycleStartedAt' => new \DateTime()]);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(1)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::FLIRT)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::OTHER_PLAYER)
            ->setInjuryRate(0)
            ->setActionCost($actionCost);
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::ANDIE,
            'characterName' => CharacterEnum::ANDIE,
            'actions' => new ArrayCollection([$action]), ]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, [
            'name' => CharacterEnum::DEREK,
            'characterName' => CharacterEnum::DEREK,
            'actions' => new ArrayCollection([$action]),
        ]);
        /** @var Player $targetPlayer */
        $targetPlayer = $I->have(Player::class, ['daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 6,
        ]);
        $targetPlayerInfo = new PlayerInfo($targetPlayer, $user, $characterConfig2);

        $I->haveInRepository($targetPlayerInfo);
        $targetPlayer->setPlayerInfo($targetPlayerInfo);
        $I->refreshEntities($targetPlayer);

        $this->flirtAction->loadParameters($action, $player, $targetPlayer);

        $I->assertTrue($this->flirtAction->isVisible());
        $I->assertNull($this->flirtAction->cannotExecuteReason());

        $this->flirtAction->execute();

        $I->assertEquals(1, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::FLIRT_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        $I->assertTrue($player->HasFlirtedWith($targetPlayer));
        $I->assertFalse($targetPlayer->HasFlirtedWith($player));
    }
}
