<?php

namespace Mush\Tests\unit\Modifier\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Modifier\ModifierRequirementHandler\RequirementRandom;
use Mush\Modifier\Service\ModifierRequirementHandlerServiceInterface;
use Mush\Modifier\Service\ModifierRequirementService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class ModifierRequirementServiceTest extends TestCase
{
    /** @var ModifierRequirementHandlerServiceInterface|Mockery\Mock */
    private ModifierRequirementHandlerServiceInterface $modifierRequirementHandlerService;

    private ModifierRequirementService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->modifierRequirementHandlerService = \Mockery::mock(ModifierRequirementHandlerServiceInterface::class);

        $this->service = new ModifierRequirementService(
            $this->modifierRequirementHandlerService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testCheckRequirementsNotMet()
    {
        // Given a player in a daedalus
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player1 = new Player();
        $player1->setPlace($room);
        new PlayerInfo($player1, new User(), new CharacterConfig());

        // Given this player has a modifier with a requirement
        $modifierActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_IN_ROOM);
        $modifierActivationRequirement->setActivationRequirement(ModifierRequirementEnum::NOT_ALONE);
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
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

        $requirementHandler = \Mockery::mock(RequirementRandom::class);

        // then modifierRequirementHandlerService should look for the handler once
        $this->modifierRequirementHandlerService
            ->shouldReceive('getModifierRequirementHandler')
            ->once()
            ->andReturn($requirementHandler)
        ;
        $requirementHandler->shouldReceive('checkRequirement')->once()->andReturn(false);

        $result = $this->service->getActiveModifiers($modifierCollection);
        $this->assertEmpty($result);
    }

    public function testCheckRequirementsMet()
    {
        // Given a player in a daedalus
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player1 = new Player();
        $player1->setPlace($room);
        new PlayerInfo($player1, new User(), new CharacterConfig());

        // Given this player has a modifier with a requirement
        $modifierActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_IN_ROOM);
        $modifierActivationRequirement->setActivationRequirement(ModifierRequirementEnum::NOT_ALONE);
        $modifierConfig1 = new VariableEventModifierConfig('unitTestVariableEventModifier');
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

        $requirementHandler = \Mockery::mock(RequirementRandom::class);

        $this->modifierRequirementHandlerService
            ->shouldReceive('getModifierRequirementHandler')
            ->once()
            ->andReturn($requirementHandler)
        ;
        $requirementHandler->shouldReceive('checkRequirement')->once()->andReturn(true);

        $result = $this->service->getActiveModifiers($modifierCollection);
        $this->assertEquals($result, $modifierCollection);
    }
}
