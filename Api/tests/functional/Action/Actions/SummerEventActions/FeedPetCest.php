<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions\SummerEventActions;

use Mush\Action\Actions\SummerEventActions\FeedPet;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class FeedPetCest extends AbstractFunctionalTest
{
    private ActionConfig $config;
    private FeedPet $feedPet;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->config = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::FEED_THE_PET]);
        $this->feedPet = $I->grabService(FeedPet::class);
    }

    public function testFeedPet(FunctionalTester $I): void
    {
        $pet = $this->createEquipment(ItemEnum::TREASURE_HUNT_PET, $this->chun->getPlace());
        $steak = $this->createEquipment(GameRationEnum::ALIEN_STEAK, $this->chun);

        $this->feedPet->loadParameters(
            $this->config,
            $steak,
            $this->chun,
            $pet,
        );

        $I->assertTrue($this->feedPet->isVisible());
        $this->feedPet->execute();

        $status = $this->chun->getStatusByNameOrThrow(PlayerStatusEnum::PROTECTED_BY_PET);

        $I->assertEquals($pet, $status->getTarget());
        $I->assertFalse($pet->hasStatus(EquipmentStatusEnum::BABY_SKINNER_INFECTED));
    }

    public function testFeedPetWithContaminatedFood(FunctionalTester $I): void
    {
        $pet = $this->createEquipment(ItemEnum::TREASURE_HUNT_PET, $this->chun->getPlace());
        $steak = $this->createEquipment(GameRationEnum::ALIEN_STEAK, $this->chun);

        $this->createStatusOn(EquipmentStatusEnum::CONTAMINATED, $steak, $this->chun);

        $this->feedPet->loadParameters(
            $this->config,
            $steak,
            $this->chun,
            $pet,
        );

        $I->assertTrue($this->feedPet->isVisible());
        $this->feedPet->execute();

        $status = $this->chun->getStatusByNameOrThrow(PlayerStatusEnum::PROTECTED_BY_PET);

        $I->assertEquals($pet, $status->getTarget());
        $I->assertTrue($pet->hasStatus(EquipmentStatusEnum::BABY_SKINNER_INFECTED));
    }
}
