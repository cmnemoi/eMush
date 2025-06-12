<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Player\Listener\PlayerStatistics;

use Mush\Action\Actions\Hit;
use Mush\Action\Actions\LieDown;
use Mush\Action\Actions\ThrowGrenade;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\BreakableTypeEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SleepStatisticCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;

    private LieDown $lieDown;
    private ActionConfig $lieDownConfig;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->lieDown = $I->grabService(LieDown::class);
        $this->lieDownConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::LIE_DOWN]);

        $this->givenPlayerSleepsOnSofa($this->chun);
    }

    public function testSleepAsphyxia(FunctionalTester $I): void
    {
        $this->givenPlayerSleepsOnSofa($this->kuanTi);
        $this->givenPlayerHas(ItemEnum::OXYGEN_CAPSULE, $this->chun);
        $this->givenNoOxygen();
        $this->givenAllRoomEquipmentIsUnbreakable();

        $this->whenCycleAdvanced();

        $this->thenPlayerShouldBeAlive($this->chun, $I);
        $this->thenPlayerMustHaveSleptCycles(1, $this->chun, $I);
        $this->thenPlayerMustHaveDiedInSleep($this->kuanTi, $I);
        $this->thenPlayerShouldHaveKillCount(0, $this->chun, $I);
        $this->thenPlayerShouldHaveKillCount(0, $this->kuanTi, $I);

        $this->whenCycleAdvanced();

        $this->thenPlayerShouldBeAlive($this->chun, $I);
        $this->thenPlayerMustHaveSleptCycles(2, $this->chun, $I);
    }

    public function testSleepGrenade(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush($I);
        $this->givenPlayerSleepsOnSofa($this->kuanTi);
        $this->givenPlayerHas(ItemEnum::GRENADE, $this->kuanTi);
        $this->givenPlayerHasHealth(1, $this->chun);
        $this->givenPlayerHasHealth(1, $this->kuanTi);

        $this->whenKuanTiThrowsTheGrenade($I);

        $this->thenPlayerMustHaveDiedInSleep($this->chun, $I);
        $this->thenPlayerShouldBeDead($this->kuanTi, $I);
        $this->thenPlayerShouldNotBeMarkedAsDiedInSleep($this->kuanTi, $I);
        $this->thenPlayerShouldHaveKillCount(0, $this->chun, $I);
        $this->thenPlayerShouldHaveKillCount(1, $this->kuanTi, $I);
    }

    public function testForceWakeUp(FunctionalTester $I): void
    {
        $this->givenPlayerSleepsOnSofa($this->kuanTi);

        $this->whenChunHitsKuanTi($I);

        $this->thenPlayerMustHaveInteruptedSleepTimes(1, $this->chun, $I);
        $this->thenPlayerMustHaveInteruptedSleepTimes(0, $this->kuanTi, $I);
        $this->thenPlayerShouldNotBeMarkedAsDiedInSleep($this->chun, $I);
        $this->thenPlayerShouldNotBeMarkedAsDiedInSleep($this->kuanTi, $I);
        $this->thenPlayerShouldHaveKillCount(0, $this->chun, $I);
        $this->thenPlayerShouldHaveKillCount(0, $this->kuanTi, $I);
    }

    public function shouldNotCountSleepInteruptedWhenWakingUpToHit(FunctionalTester $I): void
    {
        $this->whenChunHitsKuanTi($I);

        $this->thenPlayerMustHaveInteruptedSleepTimes(0, $this->chun, $I);
        $this->thenPlayerMustHaveInteruptedSleepTimes(0, $this->kuanTi, $I);
        $this->thenPlayerShouldNotBeMarkedAsDiedInSleep($this->chun, $I);
        $this->thenPlayerShouldNotBeMarkedAsDiedInSleep($this->kuanTi, $I);
    }

    public function testAssassinateInSleep(FunctionalTester $I): void
    {
        $this->givenPlayerSleepsOnSofa($this->kuanTi);
        $this->givenPlayerHasHealth(1, $this->kuanTi);

        $this->whenChunSuccessfullyHitsKuanTi($I);

        $this->thenPlayerMustHaveInteruptedSleepTimes(1, $this->chun, $I);
        $this->thenPlayerMustHaveInteruptedSleepTimes(0, $this->kuanTi, $I);
        $this->thenPlayerShouldNotBeMarkedAsDiedInSleep($this->chun, $I);
        $this->thenPlayerMustHaveDiedInSleep($this->kuanTi, $I);
        $this->thenPlayerShouldHaveKillCount(1, $this->chun, $I);
        $this->thenPlayerShouldHaveKillCount(0, $this->kuanTi, $I);
    }

    public function shouldNotDieInSleepWhenReturningToSol(FunctionalTester $I): void
    {
        $this->whenReturningToSol();

        $this->thenPlayerShouldNotBeMarkedAsDiedInSleep($this->chun, $I);
        $this->thenPlayerShouldHaveKillCount(0, $this->chun, $I);
    }

    private function givenPlayerSleepsOnSofa(Player $player): void
    {
        $sofa = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SWEDISH_SOFA,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $this->lieDown->loadParameters(
            actionConfig: $this->lieDownConfig,
            actionProvider: $sofa,
            player: $player,
            target: $sofa,
        );
        $this->lieDown->execute();
    }

    private function givenAllRoomEquipmentIsUnbreakable(): void
    {
        /** @var GameEquipment $equipment */
        foreach ($this->chun->getPlace()->getEquipments() as $equipment) {
            $equipment->getEquipment()->setBreakableType(BreakableTypeEnum::NONE);
        }
    }

    private function givenPlayerHas(string $itemName, Player $player): GameItem
    {
        return $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $itemName,
            equipmentHolder: $player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenNoOxygen(): void
    {
        $this->daedalus->setOxygen(0);
    }

    private function givenKuanTiIsMush(FunctionalTester $I): void
    {
        $this->convertPlayerToMush($I, $this->kuanTi);
    }

    private function givenPlayerHasHealth(int $quantity, Player $player): void
    {
        $player->setHealthPoint($quantity);
    }

    private function whenCycleAdvanced(): void
    {
        $event = new DaedalusEvent($this->daedalus, [], new \DateTime());
        $this->eventService->callEvent($event, DaedalusEvent::DAEDALUS_NEW_CYCLE);
    }

    private function whenKuanTiThrowsTheGrenade(FunctionalTester $I): void
    {
        $throwGrenade = $I->grabService(ThrowGrenade::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::THROW_GRENADE]);

        $grenade = $this->kuanTi->getEquipmentByName(ItemEnum::GRENADE);

        $throwGrenade->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $grenade,
            player: $this->kuanTi,
            target: $grenade,
        );
        $throwGrenade->execute();
    }

    private function whenChunHitsKuanTi(FunctionalTester $I): void
    {
        $hit = $I->grabService(Hit::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]);

        $hit->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $hit->execute();
    }

    private function whenChunSuccessfullyHitsKuanTi(FunctionalTester $I): void
    {
        $hit = $I->grabService(Hit::class);
        $actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]);
        $actionConfig->setSuccessRate(100);

        $hit->loadParameters(
            actionConfig: $actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $hit->execute();
    }

    private function whenReturningToSol(): void
    {
        $event = new DaedalusEvent($this->daedalus, [ActionEnum::RETURN_TO_SOL->toString()], new \DateTime());
        $this->eventService->callEvent($event, DaedalusEvent::FINISH_DAEDALUS);
    }

    private function thenPlayerShouldBeAlive(Player $player, FunctionalTester $I): void
    {
        $I->assertTrue($player->isAlive());
    }

    private function thenPlayerShouldBeDead(Player $player, FunctionalTester $I): void
    {
        $I->assertFalse($player->isAlive());
    }

    private function thenPlayerMustHaveSleptCycles(int $quantity, Player $player, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $player->getPlayerInfo()->getStatistics()->getSleptCycles());
    }

    private function thenPlayerMustHaveDiedInSleep(Player $player, FunctionalTester $I): void
    {
        $this->thenPlayerShouldBeDead($player, $I);
        $I->assertTrue($player->getPlayerInfo()->getStatistics()->hasDiedDuringSleep());
    }

    private function thenPlayerShouldNotBeMarkedAsDiedInSleep(Player $player, FunctionalTester $I): void
    {
        $I->assertFalse($player->getPlayerInfo()->getStatistics()->hasDiedDuringSleep());
    }

    private function thenPlayerMustHaveInteruptedSleepTimes(int $quantity, Player $player, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $player->getPlayerInfo()->getStatistics()->getSleepInterupted());
    }

    private function thenPlayerShouldHaveKillCount(int $quantity, Player $player, FunctionalTester $I): void
    {
        $I->assertEquals($quantity, $player->getPlayerInfo()->getStatistics()->getKillCount());
    }
}
