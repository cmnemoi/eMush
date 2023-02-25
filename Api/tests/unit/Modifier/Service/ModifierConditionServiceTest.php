<?php

namespace Mush\Test\Modifier\Service;

use Mockery;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\Service\ModifierRequirementService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use PHPUnit\Framework\TestCase;

class ModifierActivationRequirementServiceTest extends TestCase
{
    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    private ModifierRequirementService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->service = new ModifierRequirementService(
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testRandomActivationRequirementModifier()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $modifierActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::RANDOM);
        $modifierActivationRequirement->setValue(50);

        // create a gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig();
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->addModifierRequirement($modifierActivationRequirement)
        ;

        $modifier = new GameModifier($room, $modifierConfig1);

        $modifierCollection = new ModifierCollection([$modifier]);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(true);
        $result = $this->service->getActiveModifiers($modifierCollection, ['reason'], $room);
        $this->assertEquals($result, $modifierCollection);

        $this->randomService->shouldReceive('isSuccessful')->with(50)->once()->andReturn(false);
        $result = $this->service->getActiveModifiers($modifierCollection, ['reason'], $room);
        $this->assertEmpty($result);
    }

    public function testReasonActivationRequirementModifier()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);

        $modifierActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $modifierActivationRequirement->setActivationRequirement(ActionEnum::HIDE);

        // create a gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig();
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->addModifierRequirement($modifierActivationRequirement)
        ;

        $modifier = new GameModifier($room, $modifierConfig1);

        $modifierCollection = new ModifierCollection([$modifier]);

        $result = $this->service->getActiveModifiers($modifierCollection, [ActionEnum::HIDE], $room);
        $this->assertEquals($result, $modifierCollection);

        $result = $this->service->getActiveModifiers($modifierCollection, [ActionEnum::DROP], $room);
        $this->assertEmpty($result);
    }

    public function testPlayerInRoomActivationRequirementModifier()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player1 = new Player();
        $player1->setPlace($room);

        $modifierActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_IN_ROOM);
        $modifierActivationRequirement->setActivationRequirement(ModifierRequirementEnum::NOT_ALONE);

        // create a gear with daedalus modifier
        $modifierConfig1 = new VariableEventModifierConfig();
        $modifierConfig1
            ->setModifierRange(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent('action')
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setDelta(1)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->addModifierRequirement($modifierActivationRequirement)
        ;

        $modifier = new GameModifier($room, $modifierConfig1);

        $modifierCollection = new ModifierCollection([$modifier]);

        $result = $this->service->getActiveModifiers($modifierCollection, [ActionEnum::HIDE], $player1);
        $this->assertEmpty($result);

        $player2 = new Player();
        $player2->setPlace($room);
        $result = $this->service->getActiveModifiers($modifierCollection, [ActionEnum::DROP], $player1);
        $this->assertEquals($result, $modifierCollection);
    }
}
