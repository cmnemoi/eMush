<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Consume;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\GameFruitEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class ConsumeFrozenFoodCest extends AbstractFunctionalTest
{
    private Consume $consumeAction;
    private StatusServiceInterface $statusService;
    private Action $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::CONSUME]);
        $this->action->setDirtyRate(0);

        $I->refreshEntities($this->action);

        $this->consumeAction = $I->grabService(Consume::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testHitSuccess(FunctionalTester $I)
    {
        $ration = new Ration();
        $ration
            ->setActions(new ArrayCollection([$this->action]))
            ->setName(GameFruitEnum::CALEBOOT . '_' . GameConfigEnum::TEST)
        ;
        $I->haveInRepository($ration);

        $effect = new ConsumableEffect();
        $effect
            ->setSatiety(1)
            ->setActionPoint(2)
            ->setMovementPoint(3)
            ->setMoralPoint(4)
            ->setHealthPoint(5)
            ->setDaedalus($this->daedalus)
            ->setRation($ration)
        ;
        $I->haveInRepository($effect);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'mechanics' => new ArrayCollection([$ration]),
            'name' => GameRationEnum::STANDARD_RATION,
        ]);

        $I->haveInRepository($equipmentConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig->addEquipmentConfig($equipmentConfig);
        $I->refreshEntities($gameConfig);

        $gameItem = new GameItem($this->player1->getPlace());
        $gameItem
            ->setEquipment($equipmentConfig)
            ->setName('ration')
        ;
        $I->haveInRepository($gameItem);

        $this->consumeAction->loadParameters($this->action, $this->player1, $gameItem);

        $costWithoutFrozen = $this->consumeAction->getActionPointCost();

        $this->statusService->createStatusFromName(
            EquipmentStatusEnum::FROZEN,
            $gameItem,
            [],
            new \DateTime()
        );

        $I->assertCount(1, $gameItem->getStatuses());
        $I->assertCount(1, $gameItem->getModifiers());

        $this->consumeAction->loadParameters($this->action, $this->player1, $gameItem);
        $costWithFrozen = $this->consumeAction->getActionPointCost();

        $I->assertEquals($costWithoutFrozen + 1, $costWithFrozen);
    }
}
