<?php

namespace Mush\Test\Player\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Service\ActionModifierService;
use PHPUnit\Framework\TestCase;

class ActionModifierServiceTest extends TestCase
{
    /** @var GearToolServiceInterface | Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    private ActionModifierService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->gearToolService = Mockery::mock(GearToolServiceInterface::class);

        $this->service = new ActionModifierService(
            $this->gearToolService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testGetModifiedValue()
    {
        $room = new Place();
        $player = new Player();

        $action = new Action();
        $action
            ->setName(ActionEnum::TAKE)
            ->setScope(ActionScopeEnum::CURRENT)
        ;

        //no modifier
        $this->gearToolService->shouldReceive('GetApplicableGears')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(new ArrayCollection([]))
            ->once();
        $this->assertEquals(20, $this->service->getModifiedValue(20, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE));

        //one gear with multiplicative modifier
        $modifier1 = new Modifier();
        $modifier1
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(2)
            ->setScope(ActionEnum::TAKE)
            ->setReach(ReachEnum::INVENTORY)
            ->setIsAdditive(false)
        ;

        $gear = new Gear();
        $gear->setModifier(new arrayCollection([$modifier1]));
        $item = new ItemConfig();
        $item
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setMechanics(new arrayCollection([$gear]))
        ;
        $gameItem = new GameItem();
        $gameItem
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item)
        ;

        $this->gearToolService->shouldReceive('GetApplicableGears')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(new ArrayCollection([$gameItem]))
            ->once();
        $this->assertEquals(40, $this->service->getModifiedValue(20, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE));

        //one gear with multiplicative modifier
        $modifier1->setIsAdditive(true);
        $this->gearToolService->shouldReceive('GetApplicableGears')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(new ArrayCollection([$gameItem]))
            ->once();
        $this->assertEquals(22, $this->service->getModifiedValue(20, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE));

        //add a second irrelevant modifier
        $modifier2 = new Modifier();
        $modifier2
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(3)
            ->setScope(ModifierScopeEnum::ACTION_TECHNICIAN)
            ->setReach(ReachEnum::INVENTORY)
            ->setIsAdditive(false)
        ;
        $gear->setModifier(new arrayCollection([$modifier1, $modifier2]));

        $this->gearToolService->shouldReceive('GetApplicableGears')
            ->with($player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(new ArrayCollection([$gameItem]))
            ->once();
        $this->assertEquals(22, $this->service->getModifiedValue(20, $player, [ActionEnum::TAKE], ModifierTargetEnum::PERCENTAGE));

        //Gear with 2 relevant modifiers
        $this->gearToolService->shouldReceive('GetApplicableGears')
            ->with($player, [ActionEnum::TAKE, ModifierScopeEnum::ACTION_TECHNICIAN], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(new ArrayCollection([$gameItem]))
            ->once();
        $this->assertEquals(62, $this->service->getModifiedValue(20, $player, [ActionEnum::TAKE, ModifierScopeEnum::ACTION_TECHNICIAN], ModifierTargetEnum::PERCENTAGE));

        //2 Gear with 2 relevant modifiers
        $modifier3 = new Modifier();
        $modifier3
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(0.5)
            ->setScope(ModifierScopeEnum::ACTION_TECHNICIAN)
            ->setReach(ReachEnum::INVENTORY)
            ->setIsAdditive(false)
        ;
        $gear2 = new Gear();
        $gear2->setModifier(new arrayCollection([$modifier3]));
        $item2 = new ItemConfig();
        $item2
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setMechanics(new arrayCollection([$gear2]))
        ;
        $gameItem2 = new GameItem();
        $gameItem2
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($item2)
        ;

        $this->gearToolService->shouldReceive('GetApplicableGears')
            ->with($player, [ActionEnum::TAKE, ModifierScopeEnum::ACTION_TECHNICIAN], ModifierTargetEnum::PERCENTAGE)
            ->andReturn(new ArrayCollection([$gameItem, $gameItem2]))
            ->once();
        $this->assertEquals(32, $this->service->getModifiedValue(20, $player, [ActionEnum::TAKE, ModifierScopeEnum::ACTION_TECHNICIAN], ModifierTargetEnum::PERCENTAGE));
    }
}
