<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Game\Enum\TitleEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class OpportunistCest extends AbstractFunctionalTest
{
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->playerService = $I->grabService(PlayerServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->addSkillToPlayer(SkillEnum::OPPORTUNIST, $I, $this->chun);
    }

    public function shouldGainFiveActionPointsWhenReceiveTitleFirstTime(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(0);
        $this->whenChunReceivesCommanderTitle();
        $this->thenChunShouldHaveActionPoints(5, $I);
    }

    public function shouldNotGainActionPointsWhenReceiveTitleSecondTime(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(0);

        $this->whenChunReceivesCommanderTitle();
        $this->whenChunLosesCommanderTitle();
        $this->whenChunReceivesCommanderTitle();

        $this->thenChunShouldHaveActionPoints(5, $I);
    }

    public function shouldGainTenActionPointsInTotalWhenReceiveATitleAndThenAnotherTitle(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(0);

        $this->whenChunReceivesCommanderTitle();
        $this->whenChunLosesCommanderTitle();
        $this->whenChunReceivesNeronManagerTitle();

        $this->thenChunShouldHaveActionPoints(10, $I);
    }

    public function aNonOpportunistPlayerShouldNotGainActionPointsWhenReceiveTitle(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(0);
        $this->whenKuanTiReceivesCommanderTitle();
        $this->thenKuanTiShouldHaveActionPoints(0, $I);
    }

    public function shouldNotGiveAnyActionPointsIfPlayerIsMush(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(0);
        $this->givenChunWasConvertedToMush($I);
        $this->whenChunReceivesCommanderTitle();
        $this->thenChunShouldHaveActionPoints(0, $I);
    }

    private function givenChunWasConvertedToMush($I)
    {
        $this->convertPlayerToMush($I, $this->chun);
    }

    private function givenKuanTiHasActionPoints(int $actionPoints): void
    {
        $this->kuanTi->setActionPoint($actionPoints);
    }

    private function givenChunHasActionPoints(int $actionPoints): void
    {
        $this->chun->setActionPoint($actionPoints);
    }

    private function givenPlayer2HasActionPoints(int $actionPoints): void
    {
        $this->player2->setActionPoint($actionPoints);
    }

    private function whenChunReceivesNeronManagerTitle()
    {
        $this->whenPlayerReceivesTitle($this->chun, TitleEnum::NERON_MANAGER);
    }

    private function whenChunLosesCommanderTitle()
    {
        $this->whenPlayerLosesCommanderTitle($this->chun);
    }

    private function whenKuanTiReceivesCommanderTitle()
    {
        $this->whenPlayerReceivesTitle($this->kuanTi, TitleEnum::COMMANDER);
    }

    private function whenChunReceivesCommanderTitle()
    {
        $this->whenPlayerReceivesTitle($this->chun, TitleEnum::COMMANDER);
    }

    private function whenPlayerLosesCommanderTitle(Player $player)
    {
        $playerEvent = new PlayerEvent(
            $player,
            [TitleEnum::COMMANDER],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::TITLE_REMOVED);
    }

    private function whenPlayerReceivesTitle(Player $player, string $title)
    {
        $playerEvent = new PlayerEvent(
            $player,
            [$title],
            new \DateTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::TITLE_ATTRIBUTED);
    }

    private function thenKuanTiShouldHaveActionPoints(int $expectedActionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedActionPoints, $this->kuanTi->getActionPoint());
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
