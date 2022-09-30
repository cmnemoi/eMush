<?php

namespace Mush\Test\Modifier\Service;

use Mockery;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierCollection;
use Mush\Modifier\Entity\Trash\ModifierCondition;
use Mush\Modifier\Entity\Trash\ModifierConfig;
use Mush\Modifier\Enum\ModifierConditionEnum;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Service\ModifierConditionService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use PHPUnit\Framework\TestCase;

class ModifierConditionServiceTest extends TestCase
{
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    private ModifierConditionService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->service = new ModifierConditionService(
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testRandomConditionModifier()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $modifierCondition = new ModifierCondition(ModifierConditionEnum::RANDOM);
        $modifierCondition->setValue(50);

        // create a gear with daedalus modifier
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($modifierCondition)
        ;

        $modifier = new Modifier($room, $modifierConfig1);

        $modifierCollection = new ModifierCollection([$modifier]);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(true);
        $result = $this->service->getActiveModifiers($modifierCollection, 'reason', $room);
        $this->assertEquals($result, $modifierCollection);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(false);
        $result = $this->service->getActiveModifiers($modifierCollection, 'reason', $room);
        $this->assertEmpty($result);
    }

    public function testReasonConditionModifier()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $modifierCondition = new ModifierCondition(ModifierConditionEnum::REASON);
        $modifierCondition->setCondition(ActionEnum::HIDE);

        // create a gear with daedalus modifier
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($modifierCondition)
        ;

        $modifier = new Modifier($room, $modifierConfig1);

        $modifierCollection = new ModifierCollection([$modifier]);

        $result = $this->service->getActiveModifiers($modifierCollection, ActionEnum::HIDE, $room);
        $this->assertEquals($result, $modifierCollection);

        $result = $this->service->getActiveModifiers($modifierCollection, ActionEnum::DROP, $room);
        $this->assertEmpty($result);
    }

    public function testPlayerInRoomConditionModifier()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player1 = new Player();
        $player1->setPlace($room);

        $modifierCondition = new ModifierCondition(ModifierConditionEnum::PLAYER_IN_ROOM);
        $modifierCondition->setCondition(ModifierConditionEnum::NOT_ALONE);

        // create a gear with daedalus modifier
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setScope('action')
            ->setTarget(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->addModifierCondition($modifierCondition)
        ;

        $modifier = new Modifier($room, $modifierConfig1);

        $modifierCollection = new ModifierCollection([$modifier]);

        $result = $this->service->getActiveModifiers($modifierCollection, ActionEnum::HIDE, $player1);
        $this->assertEmpty($result);

        $player2 = new Player();
        $player2->setPlace($room);
        $result = $this->service->getActiveModifiers($modifierCollection, ActionEnum::DROP, $player1);
        $this->assertEquals($result, $modifierCollection);
    }
}
