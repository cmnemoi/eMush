<?php

declare(strict_types=1);

namespace Mush\tests\functional\Player\Service;

use Mush\Player\Service\RemoveHealthFromPlayerService;
use Mush\Player\Service\RemoveHealthFromPlayerServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RemoveHealthFromPlayerCest extends AbstractFunctionalTest
{
    private RemoveHealthFromPlayerServiceInterface $removeHealthFromPlayer;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->removeHealthFromPlayer = $I->grabService(RemoveHealthFromPlayerService::class);
    }

    public function shouldRemoveHealthPointsFromPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerHasHealthPoints(10);

        $this->whenRemoveHealthFromPlayer(5);

        $this->thenPlayerShouldHaveHealthPoints(5, $I);
    }

    private function givenPlayerHasHealthPoints(int $healthPoints): void
    {
        $this->player->setHealthPoint($healthPoints);
    }

    private function whenRemoveHealthFromPlayer(int $healthToRemove): void
    {
        $this->removeHealthFromPlayer->execute($healthToRemove, $this->player);
    }

    private function thenPlayerShouldHaveHealthPoints(int $healthPoints, FunctionalTester $I): void
    {
        $I->assertEquals($healthPoints, $this->player->getHealthPoint());
    }
}
