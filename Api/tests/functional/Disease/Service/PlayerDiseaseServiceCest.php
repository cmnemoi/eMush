<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Disease\Service;

use Mush\Disease\Enum\DiseaseEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PlayerDiseaseServiceCest extends AbstractFunctionalTest
{
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->playerDiseaseService = $I->grabService(PlayerDiseaseServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testIncubatingDiseaseHealsSilentlyWhenPlayerTurnsOutMush(FunctionalTester $I): void
    {
        // given player has an incubating disease
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::COLD,
            player: $this->player,
            reasons: [],
            delayMin: 1,
        );

        // given player turns out mush
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when I call handleNewCycle on the disease
        $this->playerDiseaseService->handleNewCycle($disease, new \DateTime());

        // then the disease should heal silently
        $I->assertNull($this->player->getMedicalConditionByName(DiseaseEnum::COLD));

        $I->grabEntityFromRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => LogEnum::DISEASE_CURED,
                'visibility' => VisibilityEnum::HIDDEN,
            ]
        );
    }

    public function testSpontaneousDiseaseHealShouldPrintAPrivateLog(FunctionalTester $I): void
    {
        // given player has a disease
        $disease = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DiseaseEnum::COLD,
            player: $this->player,
            reasons: [],
        );

        // given the disease has 0 disease points, so it should heal spontaneously at cycle change
        $disease->setDiseasePoint(0);

        // when I call handleNewCycle on the disease
        $this->playerDiseaseService->handleNewCycle($disease, new \DateTime());

        // then I should see a private room log reporting the disease healing
        $I->grabEntityFromRepository(
            RoomLog::class,
            [
                'place' => $this->player->getPlace()->getLogName(),
                'playerInfo' => $this->player->getPlayerInfo(),
                'log' => LogEnum::DISEASE_CURED,
                'visibility' => VisibilityEnum::PRIVATE,
            ]
        );
    }

    public function shouldDecreaseDisorderDiseasePointIfPlayerSleepInAShrinkRoom(FunctionalTester $I): void
    {
        // given Chun has a depression (a disorder)
        $disorder = $this->playerDiseaseService->createDiseaseFromName(
            diseaseName: DisorderEnum::DEPRESSION,
            player: $this->chun,
            reasons: [],
        );

        // given disorder has 5 disease points
        $disorder->setDiseasePoint(5);

        // given KT is a psy
        $this->statusService->createStatusFromName(
            statusName: SkillEnum::SHRINK,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );

        // given Chun is lying down
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::LYING_DOWN,
            holder: $this->chun,
            tags: [],
            time: new \DateTime(),
        );

        // when I call handleNewCycle on the disease
        $this->playerDiseaseService->handleNewCycle($disorder, new \DateTime());

        // then the disease point should decrease by 1
        $I->assertEquals(4, $disorder->getDiseasePoint());
    }
}
