<?php

namespace Mush\Tests\Modifier\Event;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Listener\CycleEventSubscriber;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;

class CycleEventCest
{
    private CycleEventSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(CycleEventSubscriber::class);
    }

    public function testLieDownStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionPointBefore = $player->getActionPoint();

        $time = new \DateTime();

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::LYING_DOWN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('test')
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $cycleEvent = new PlayerCycleEvent($player, [EventEnum::NEW_CYCLE], $time);

        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($actionPointBefore + 1, $player->getActionPoint());
    }

    public function testAntisocialStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player2->setPlayerVariables($characterConfig);
        $player2Info = new PlayerInfo($player2, $user, $characterConfig);

        $I->haveInRepository($player2Info);
        $player2->setPlayerInfo($player2Info);
        $I->refreshEntities($player2);

        $moralePointBefore = $player->getMoralPoint();

        $time = new \DateTime();

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::LYING_DOWN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $notAloneActivationRequirement = $I->grabEntityFromRepository(
            ModifierActivationRequirement::class,
            [
                'activationRequirementName' => ModifierRequirementEnum::PLAYER_IN_ROOM,
                'activationRequirement' => ModifierRequirementEnum::NOT_ALONE,
            ]
        );

        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::ANTISOCIAL_MODIFIER)
            ->addModifierRequirement($notAloneActivationRequirement)
            ->setName('test')
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $cycleEvent = new PlayerCycleEvent($player, [EventEnum::NEW_CYCLE], $time);

        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($moralePointBefore - 1, $player->getMoralPoint());
        $I->assertEquals($moralePointBefore, $player2->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $daedalusInfo,
            'place' => $room->getName(),
            'playerInfo' => $playerInfo,
            'log' => PlayerModifierLogEnum::ANTISOCIAL_MORALE_LOSS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testFitfullSleepCycleSubscriber(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionPointBefore = $player->getActionPoint();

        $time = new \DateTime();

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::LYING_DOWN)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $modifierConfig = new VariableEventModifierConfig();
        $modifierConfig
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setName('test1')
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $fitfullModifierConfig = new VariableEventModifierConfig();
        $fitfullModifierConfig
            ->setTargetEvent(EventEnum::NEW_CYCLE)
            ->setTargetVariable(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setModifierName(ModifierNameEnum::FITFULL_SLEEP)
            ->setName('test')
        ;
        $I->haveInRepository($fitfullModifierConfig);

        $fitfullModifier = new GameModifier($player, $fitfullModifierConfig);
        $I->haveInRepository($fitfullModifier);

        $cycleEvent = new PlayerCycleEvent($player, [EventEnum::NEW_CYCLE], $time);

        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($actionPointBefore, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $playerInfo,
            'log' => PlayerModifierLogEnum::FITFULL_SLEEP,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
