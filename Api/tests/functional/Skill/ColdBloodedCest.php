<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\PlayerModifierLogEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class ColdBloodedCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::COLD_BLOODED, $I, $this->chun);
    }

    public function shouldGainThreeActionPointsWhenPlayerDies(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(0);

        $this->whenKuanTiDiesFrom(EndCauseEnum::DEPRESSION);

        $this->thenChunShouldHaveActionPoints(3, $I);
    }

    public function shouldPrintPrivateLog(FunctionalTester $I): void
    {
        $this->whenKuanTiDiesFrom(EndCauseEnum::DEPRESSION);

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Votre compétence **Sang-froid** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: PlayerModifierLogEnum::COLD_BLOODED_WORKED,
                visibility: VisibilityEnum::PRIVATE,
                inPlayerRoom: false
            ),
            I: $I
        );
    }

    public function shouldNotWorkForMushPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasActionPoints(0);

        $this->givenPlayerIsMush();

        $this->whenKuanTiDiesFrom(EndCauseEnum::DEPRESSION);

        $this->thenPlayerShouldNotHaveActionPoints(0, $I);
    }

    #[DataProvider('happyEnds')]
    public function shouldNotGainActionPointsOnHappyEnds(FunctionalTester $I, Example $example): void
    {
        $this->givenPlayerHasActionPoints(0);

        $this->whenKuanTiDiesFrom($example['endCause']);

        $this->thenPlayerShouldNotHaveActionPoints(0, $I);
    }

    public static function happyEnds(): array
    {
        return EndCauseEnum::getNotDeathEndCauses()->map(static fn (string $endCause) => ['endCause' => $endCause])->toArray();
    }

    private function givenChunHasActionPoints(int $actionPoints): void
    {
        $this->chun->setActionPoint($actionPoints);
    }

    private function givenPlayerHasActionPoints(int $actionPoints): void
    {
        $this->player->setActionPoint($actionPoints);
    }

    private function givenPlayerIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function whenKuanTiDiesFrom(string $endCause): void
    {
        $this->playerService->killPlayer(
            player: $this->kuanTi,
            endReason: $endCause,
            time: new \DateTime(),
        );
    }

    private function thenChunShouldHaveActionPoints(int $expectedActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedActionPoints, $this->chun->getActionPoint());
    }

    private function thenPlayerShouldNotHaveActionPoints(int $expectedActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedActionPoints, $this->player->getActionPoint());
    }
}
