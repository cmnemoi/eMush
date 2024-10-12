<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RoboticsExpertCest extends AbstractFunctionalTest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->addSkillToPlayer(SkillEnum::ROBOTICS_EXPERT, $I);
    }

    public function shouldSpawnOneSupportDroneBlueprintInPlayerInventory(FunctionalTester $I): void
    {
        $I->assertCount(
            expectedCount: 1,
            haystack: $this->player->getEquipments()->filter(static fn (GameItem $equipment) => $equipment->getName() === ItemEnum::BLUEPRINT)
        );

        $blueprint = $this->player->getEquipmentByNameOrThrow(ItemEnum::BLUEPRINT);
        $I->assertEquals(ItemEnum::SUPPORT_DRONE, $blueprint->getMechanicByNameOrThrow(EquipmentMechanicEnum::BLUEPRINT)->getCraftedEquipmentName());
    }
}
