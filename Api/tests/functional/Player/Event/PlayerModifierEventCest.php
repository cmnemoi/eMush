<?php

namespace Mush\Tests\functional\Player\Event;

use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerModifierEventCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testDispatchMoralChange(FunctionalTester $I)
    {
        $this->player->setMoralPoint(7);

        // remove morale above demoralized threshold
        $this->callAnEventThatGivesMorale(-1);

        $I->assertEquals(6, $this->player->getMoralPoint());
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::DEMORALIZED));
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::SUICIDAL));

        // remove morale below demoralized threshold
        $this->callAnEventThatGivesMorale(-1);

        $I->assertEquals(5, $this->player->getMoralPoint());
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::DEMORALIZED));
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::SUICIDAL));

        // remove morale below suicidal threshold
        $this->callAnEventThatGivesMorale(-4);

        $I->assertEquals(1, $this->player->getMoralPoint());
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::DEMORALIZED));
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::SUICIDAL));

        // remove morale within suicidal threshold
        $this->callAnEventThatGivesMorale(-1);

        $I->assertEquals(0, $this->player->getMoralPoint());
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::DEMORALIZED));
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::SUICIDAL));

        // remove morale below 0 won't do anything
        $this->callAnEventThatGivesMorale(-1);

        $I->assertEquals(0, $this->player->getMoralPoint());
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::DEMORALIZED));
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::SUICIDAL));

        // go back above suicidal range
        $this->callAnEventThatGivesMorale(2);

        $I->assertEquals(2, $this->player->getMoralPoint());
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::DEMORALIZED));
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::SUICIDAL));

        // go back above demoralized range
        $this->callAnEventThatGivesMorale(5);

        $I->assertEquals(7, $this->player->getMoralPoint());
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::DEMORALIZED));
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::SUICIDAL));
    }

    public function testDispatchSatietyChange(FunctionalTester $I)
    {
        $this->player->setSatiety(0);

        $this->callAnEventThatGivesSatiety(-1);
        $I->assertEquals(-1, $this->player->getSatiety());
        $I->assertCount(0, $this->player->getStatuses());

        // check if satiety is negative, it is reset to 0 before being added to
        $this->callAnEventThatGivesSatiety(2);

        $I->assertEquals(2, $this->player->getSatiety());
        $I->assertCount(0, $this->player->getStatuses());

        // if satiety is positive, it is simply added to. 3 is the threshold for full stomach
        $this->callAnEventThatGivesSatiety(1);

        $I->assertEquals(3, $this->player->getSatiety());
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::FULL_STOMACH));

        // simulate a new cycle
        $this->callAnEventThatGivesSatiety(-1);

        $I->assertEquals(2, $this->player->getSatiety());
        $I->assertCount(0, $this->player->getStatuses());

        // test starving warning
        $this->callAnEventThatGivesSatiety(-26);

        $I->assertEquals(-24, $this->player->getSatiety());
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::STARVING_WARNING));

        // test starving
        $this->callAnEventThatGivesSatiety(-1);

        $I->assertEquals(-25, $this->player->getSatiety());
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::STARVING));
    }

    public function testDispatchMushSatietyChange(FunctionalTester $I)
    {
        $this->player->setSatiety(0);
        $this->convertPlayerToMush($I, $this->player);

        $this->callAnEventThatGivesSatiety(-1);
        $I->assertEquals(-1, $this->player->getSatiety());
        $I->assertCount(1, $this->player->getStatuses());

        // check if satiety is negative, it is reset to 0 before being added to
        $this->callAnEventThatGivesSatiety(2);

        $I->assertEquals(2, $this->player->getSatiety());
        $I->assertCount(1, $this->player->getStatuses());

        // if satiety is positive, it is simply added to. 3 is the threshold for full stomach
        $this->callAnEventThatGivesSatiety(1);

        $I->assertEquals(3, $this->player->getSatiety());
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::FULL_STOMACH));

        // simulate a new cycle
        $this->callAnEventThatGivesSatiety(-1);

        $I->assertEquals(2, $this->player->getSatiety());
        $I->assertCount(1, $this->player->getStatuses());

        // test no starving warning for mush
        $this->callAnEventThatGivesSatiety(-26);

        $I->assertEquals(-24, $this->player->getSatiety());
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::STARVING_WARNING));

        // test no starving for mush
        $this->callAnEventThatGivesSatiety(-1);

        $I->assertEquals(-25, $this->player->getSatiety());
        $I->assertFalse($this->player->hasStatus(PlayerStatusEnum::STARVING));
    }

    private function callAnEventThatGivesMorale(int $morale)
    {
        $playerEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::MORAL_POINT,
            $morale,
            [EventEnum::PLAYER_DEATH],
            new \DateTime()
        );

        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function callAnEventThatGivesSatiety(int $satiety)
    {
        $playerEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::SATIETY,
            $satiety,
            [EventEnum::PLAYER_DEATH],
            new \DateTime()
        );

        $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
