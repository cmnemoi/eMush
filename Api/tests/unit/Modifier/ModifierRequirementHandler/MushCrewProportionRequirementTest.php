<?php

declare(strict_types=1);

namespace Mush\tests\unit\Modifier\ModifierRequirementHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\ModifierRequirementHandler\MushCrewProportionRequirement;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MushCrewProportionRequirementTest extends TestCase
{
    private ModifierActivationRequirement $requirement;
    private MushCrewProportionRequirement $service;
    private Daedalus $daedalus;
    private Player $player;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->service = new MushCrewProportionRequirement();

        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->daedalus->getDaedalusConfig()->setPlayerCount(4);
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::CURRENT);
        $this->player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        $this->givenRequirementForMushCrewProportion(50);
    }

    public function testShouldReturnTrueIfHalfOfCrewIsMush(): void
    {
        $this->givenAHumanPlayer();
        $this->givenMushPlayers(2);

        $result = $this->whenICheckTheRequirementForCrewMushProportion($this->requirement);

        $this->thenRequirementShouldBeVerified($result);
    }

    public function testShouldReturnTrueIfHalfOfCrewIsDead(): void
    {
        $this->givenAHumanPlayer();
        $this->givenDeadPlayers(2);

        $result = $this->whenICheckTheRequirementForCrewMushProportion($this->requirement);

        $this->thenRequirementShouldBeVerified($result);
    }

    public function testShouldCombineMushAndDeadPlayers(): void
    {
        $this->givenAHumanPlayer();
        $this->givenMushPlayers(1);
        $this->givenDeadPlayers(1);

        $result = $this->whenICheckTheRequirementForCrewMushProportion($this->requirement);

        $this->thenRequirementShouldBeVerified($result);
    }

    public function testShouldCountDeadMushOnlyOnce(): void
    {
        $this->givenAHumanPlayer();
        $this->givenAHumanPlayer();
        $this->givenDeadMushPlayer();

        $result = $this->whenICheckTheRequirementForCrewMushProportion($this->requirement);

        $this->thenRequirementShouldNotBeVerified($result);
    }

    public function testShouldCountCryogenizedSlotsOnPartiallyFilledShip(): void
    {
        $this->daedalus->getDaedalusConfig()->setPlayerCount(10);
        for ($i = 0; $i < 5; ++$i) {
            $this->givenAHumanPlayer();
        }

        $result = $this->whenICheckTheRequirementForCrewMushProportion($this->requirement);

        $this->thenRequirementShouldNotBeVerified($result);

        $this->givenPlayerIsDead($this->player);

        $result = $this->whenICheckTheRequirementForCrewMushProportion($this->requirement);

        $this->thenRequirementShouldBeVerified($result);
    }

    public function testShouldReturnFalseBeforeGameStarts(): void
    {
        $this->daedalus->getDaedalusInfo()->setGameStatus(GameStatusEnum::STARTING);

        $this->givenMushPlayers(3);

        $result = $this->whenICheckTheRequirementForCrewMushProportion($this->requirement);

        $this->thenRequirementShouldNotBeVerified($result);
    }

    private function givenDeadPlayers(int $number): void
    {
        for ($i = 0; $i < $number; ++$i) {
            $this->givenPlayerIsDead($this->givenAHumanPlayer());
        }
    }

    private function givenDeadMushPlayer(): void
    {
        $player = $this->givenMushPlayer();
        $this->givenPlayerIsDead($player);
    }

    private function givenAHumanPlayer(): Player
    {
        return PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    private function givenMushPlayers(int $number): void
    {
        for ($i = 0; $i < $number; ++$i) {
            $this->givenMushPlayer();
        }
    }

    private function givenMushPlayer(): Player
    {
        $mushPlayer = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
        StatusFactory::createChargeStatusFromStatusName(
            name: PlayerStatusEnum::MUSH,
            holder: $mushPlayer,
        );

        return $mushPlayer;
    }

    private function givenPlayerIsDead(Player $player): void
    {
        $player->kill();
    }

    private function givenRequirementForMushCrewProportion(int $proportion): void
    {
        $this->requirement = new ModifierActivationRequirement(ModifierRequirementEnum::MUSH_CREW_PROPORTION);
        $this->requirement
            ->setName(ModifierRequirementEnum::MUSH_CREW_PROPORTION_50_PERCENTS)
            ->setValue($proportion);
    }

    private function whenICheckTheRequirementForCrewMushProportion(ModifierActivationRequirement $requirement): bool
    {
        return $this->service->checkRequirement($requirement, $this->player);
    }

    private function thenRequirementShouldBeVerified(bool $requirementCheck): void
    {
        self::assertTrue($requirementCheck);
    }

    private function thenRequirementShouldNotBeVerified(bool $requirementCheck): void
    {
        self::assertFalse($requirementCheck);
    }
}
