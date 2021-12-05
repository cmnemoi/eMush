<?php

namespace Mush\Tests\Modifier\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\EventEnum;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierCondition;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Listener\CycleEventSubscriber;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerCycleEvent;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class CycleEventCest
{
    private CycleEventSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(CycleEventSubscriber::class);
    }

    public function testLieDownStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $actionPointBefore = $player->getActionPoint();

        $time = new DateTime();

        $statusConfig = new StatusConfig();
        $statusConfig->setName(PlayerStatusEnum::LYING_DOWN);
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);
        $modifier->setCause(PlayerStatusEnum::LYING_DOWN);
        $I->haveInRepository($modifier);

        $cycleEvent = new PlayerCycleEvent($player, EventEnum::NEW_CYCLE, $time);

        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($actionPointBefore + 1, $player->getActionPoint());
    }

    public function testAntisocialStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);
        /** @var Player $player2 */
        $player2 = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $moralePointBefore = $player->getMoralPoint();

        $time = new DateTime();

        $statusConfig = new StatusConfig();
        $statusConfig->setName(PlayerStatusEnum::LYING_DOWN);
        $I->haveInRepository($statusConfig);
        $status = new Status($player, $statusConfig);
        $I->haveInRepository($status);

        $notAloneCondition = new ModifierCondition(ModifierConditionEnum::PLAYER_IN_ROOM);
        $notAloneCondition->setCondition(ModifierConditionEnum::NOT_ALONE);
        $I->haveInRepository($notAloneCondition);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(EventEnum::NEW_CYCLE)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($notAloneCondition)
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new Modifier($player, $modifierConfig);
        $modifier->setCause(PlayerStatusEnum::ANTISOCIAL);
        $I->haveInRepository($modifier);

        $cycleEvent = new PlayerCycleEvent($player, EventEnum::NEW_CYCLE, $time);

        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($moralePointBefore - 1, $player->getMoralPoint());
        $I->assertEquals($moralePointBefore, $player2->getMoralPoint());

        $I->seeInRepository(RoomLog::class, [
            'player' => $player,
            'log' => PlayerModifierLogEnum::ANTISOCIAL_MORALE_LOSS,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
