<?php

declare(strict_types=1);

namespace Mush\tests\functional\Project;

use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class TrashLoadCest extends AbstractFunctionalTest
{
    private Place $storage;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->storage = $this->createExtraPlace(RoomEnum::FRONT_STORAGE, $I, $this->daedalus);

        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::TRASH_LOAD),
            author: $this->chun,
            I: $I
        );
    }

    public function shouldGivePlayersTwoExtraPrivateChanels(FunctionalTester $I): void
    {
        $I->assertEquals(5, $this->storage->getEquipments()->count());
    }
}
