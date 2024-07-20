<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Status\UseCase;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\FakeStatusService;
use Mush\Status\Service\MakePlayerInactiveService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MakePlayerInactiveUseCaseTest extends TestCase
{
    private MakePlayerInactiveService $makePlayerInactiveService;
    private FakeStatusService $statusService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->statusService = new FakeStatusService();
        $this->makePlayerInactiveService = new MakePlayerInactiveService($this->statusService);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        $this->statusService->statuses->clear();
    }

    public function testShouldCreateInactiveStatus(): void
    {
        $player = $this->givenAPlayer();

        $this->givenPlayerHasAllTheirActionPoints($player);

        $this->givenPlayerLastActionIsFrom($player, new \DateTime('yesterday'));

        $this->whenIMakePlayerInactive($player);

        $this->thenPlayerShouldHaveInactiveStatus($player);
    }

    public function testShouldNotCreateInactiveStatusIfPlayerDoesNotHaveAllTheirActionPoints(): void
    {
        $player = $this->givenAPlayer();

        $this->givenPlayerLastActionIsFrom($player, new \DateTime('yesterday'));

        $this->givenPlayerDoesNotHaveAllTheirActionPoints($player);

        $this->whenIMakePlayerInactive($player);

        $this->thenPlayerShouldNotHaveInactiveStatus($player);
    }

    public function testShouldNotCreateInactiveStatusIfPlayerLastActionIsNotFromYesterday(): void
    {
        $player = $this->givenAPlayer();

        $this->givenPlayerHasAllTheirActionPoints($player);

        $this->givenPlayerLastActionIsFrom(player: $player, date: new \DateTime());

        $this->whenIMakePlayerInactive($player);

        $this->thenPlayerShouldNotHaveInactiveStatus($player);
    }

    public function testShouldCreateHighlyInactiveStatus(): void
    {
        $player = $this->givenAPlayer();

        $this->givenPlayerHasAllTheirActionPoints($player);

        $this->givenPlayerLastActionIsFrom($player, new \DateTime('2 days ago'));

        $this->whenIMakePlayerInactive($player);

        $this->thenPlayerShouldHaveHighlyInactiveStatus($player);
    }

    public function testShouldRemoveInactiveStatusWhenCreatingHighlyInactiveStatus(): void
    {
        $player = $this->givenAPlayer();

        $this->givenPlayerHasAllTheirActionPoints($player);

        $this->givenPlayerLastActionIsFrom($player, new \DateTime('2 days ago'));

        $this->givenPlayerHasInactiveStatus($player);

        $this->whenIMakePlayerInactive($player);

        $this->thenPlayerShouldNotHaveInactiveStatus($player);

        $this->thenPlayerShouldHaveHighlyInactiveStatus($player);
    }

    private function givenAPlayer(): Player
    {
        return PlayerFactory::createPlayerByName(CharacterEnum::CHUN);
    }

    private function givenPlayerHasAllTheirActionPoints(Player $player): void
    {
        $player->setActionPoint($player->getCharacterConfig()->getMaxActionPoint());
    }

    private function givenPlayerDoesNotHaveAllTheirActionPoints(Player $player): void
    {
        $player->setActionPoint(0);
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

    private function whenIMakePlayerInactive(Player $player): void
    {
        $this->makePlayerInactiveService->execute($player);
    }

    private function thenPlayerShouldHaveInactiveStatus(Player $player): void
    {
        self::assertTrue($player->hasStatus(PlayerStatusEnum::INACTIVE));
    }

    private function thenPlayerShouldNotHaveInactiveStatus(Player $player): void
    {
        self::assertFalse($player->hasStatus(PlayerStatusEnum::INACTIVE));
    }

    private function thenPlayerShouldHaveHighlyInactiveStatus(Player $player): void
    {
        self::assertTrue($player->hasStatus(PlayerStatusEnum::HIGHLY_INACTIVE));
    }
}
