<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Status\UseCase;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\FakeStatusService;
use Mush\Status\Service\MakePlayerActiveService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MakePlayerActiveUseCaseTest extends TestCase
{
    private MakePlayerActiveService $makePlayerActiveService;
    private FakeStatusService $statusService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->statusService = new FakeStatusService();
        $this->makePlayerActiveService = new MakePlayerActiveService($this->statusService);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->statusService->statuses->clear();
    }

    public function testShouldRemoveActiveStatusIfPlayerLastActionIsNotFromYesterday(): void
    {
        $player = $this->givenAPlayer();

        $this->givenPlayerHasInactiveStatus($player);

        $this->givenPlayerLastActionIsFrom(player: $player, date: new \DateTime());

        $this->whenIMakePlayerActive($player);

        $this->thenPlayerShouldNotHaveActiveStatus($player);
    }

    public function testShouldRemoveHighlyActiveStatusIfPlayerLastActionIsNotFromTwoDaysAgo(): void
    {
        $player = $this->givenAPlayer();

        $this->givenPlayerHasHighlyInactiveStatus($player);

        $this->givenPlayerLastActionIsFrom($player, new \DateTime());

        $this->whenIMakePlayerActive($player);

        $this->thenPlayerShouldNotHaveHighlyActiveStatus($player);
    }

    private function givenAPlayer(): Player
    {
        return PlayerFactory::createPlayerByName(CharacterEnum::CHUN);
    }

    private function givenPlayerLastActionIsFrom(Player $player, \DateTime $date): void
    {
        (new \ReflectionProperty($player, 'lastActionDate'))->setValue($player, $date);
    }

    private function givenPlayerHasInactiveStatus(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::INACTIVE,
            holder: $player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function givenPlayerHasHighlyInactiveStatus(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::HIGHLY_INACTIVE,
            holder: $player,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenIMakePlayerActive(Player $player): void
    {
        $this->makePlayerActiveService->execute($player);
    }

    private function thenPlayerShouldNotHaveActiveStatus(Player $player): void
    {
        self::assertFalse($player->hasStatus(PlayerStatusEnum::INACTIVE));
    }

    private function thenPlayerShouldNotHaveHighlyActiveStatus(Player $player): void
    {
        self::assertFalse($player->hasStatus(PlayerStatusEnum::HIGHLY_INACTIVE));
    }
}
