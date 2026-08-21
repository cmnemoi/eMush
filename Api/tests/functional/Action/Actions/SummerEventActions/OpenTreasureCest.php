<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions\SummerEventActions;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\PendingStatisticRepositoryInterface;
use Mush\Action\Actions\SummerEventActions\OpenTreasure;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class OpenTreasureCest extends AbstractFunctionalTest
{
    private ActionConfig $config;
    private OpenTreasure $openTreasure;
    private GameEquipment $treasure;
    private PendingStatisticRepositoryInterface $pendingStatisticRepository;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->config = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::OPEN_TREASURE]);
        $this->openTreasure = $I->grabService(OpenTreasure::class);

        $this->pendingStatisticRepository = $I->grabService(PendingStatisticRepositoryInterface::class);

        $this->treasure = $this->createEquipment(ItemEnum::TREASURE_HUNT_CHEST_CLOSED, $this->chun->getPlace());
    }

    public function testOpenTreasure(FunctionalTester $I): void
    {
        // given we load the action
        $this->openTreasure->loadParameters(
            actionConfig: $this->config,
            actionProvider: $this->treasure,
            player: $this->chun,
            target: $this->treasure
        );

        $I->assertNull($this->openTreasure->cannotExecuteReason());
        $this->openTreasure->execute();

        $roomItems = $this->chun->getPlace()->getEquipments()->toArray();
        $chunItems = $this->chun->getEquipments()->toArray();

        // we have six items in total.
        $I->assertCount(3, $roomItems);
        $I->assertCount(3, $chunItems);

        $closedDaelusid = $this->kuanTi->getDaedalus()->getDaedalusInfo()->getClosedDaedalus()->getId();

        // Kuan Ti should have a stat for the treasure opened.
        $I->assertNotNull($this->pendingStatisticRepository->findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum::TREASURE_OPENED, $this->kuanTi->getUser()->getId(), $closedDaelusid));
    }
}
