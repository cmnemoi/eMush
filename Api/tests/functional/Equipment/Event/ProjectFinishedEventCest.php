<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    public function shouldCreateEquipmentWhenProjectIsFinished(FunctionalTester $I): void
    {   
        // given Daedalus has an engine room
        $engineRoom = $this->createExtraPlace(
            placeName: RoomEnum::ENGINE_ROOM,
            I: $I,
            daedalus: $this->daedalus
        );

        // when Dismantling project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::DISMANTLING),
            author: $this->chun,
            I: $I
        );

        // then I should see 5 metal scrap in Engine Room
        $I->assertCount(
            expectedCount: 5,
            haystack: $engineRoom->getEquipments()->filter(
                fn (GameItem $item) => $item->getName() === ItemEnum::METAL_SCRAPS
            )
        );
    }
}
