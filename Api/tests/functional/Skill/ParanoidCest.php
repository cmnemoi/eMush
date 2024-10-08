<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ParanoidCest extends AbstractFunctionalTest
{
    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->addSkillToPlayer(SkillEnum::PARANOID, $I);
    }

    public function shouldSpawnTwoCamerasInPlayerInventory(FunctionalTester $I): void
    {
        $I->assertCount(
            expectedCount: 2,
            haystack: $this->player->getEquipments()->filter(static fn (GameEquipment $equipment) => $equipment->getName() === ItemEnum::CAMERA_ITEM)
        );
    }
}
