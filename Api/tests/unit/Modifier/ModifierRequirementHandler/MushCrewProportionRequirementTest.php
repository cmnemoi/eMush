<?php

declare(strict_types=1);

namespace Mush\tests\unit\Modifier\ModifierRequirementHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
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
    public function before()
    {
        $this->service = new MushCrewProportionRequirement();

        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->player = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    public function testShouldReturnTrueIfRequiredProportionOfMushCrewIsMet(): void
    {
        // given 1 human player

        $this->givenMushPlayers(2);

        $this->givenRequirementForMushCrewProportion(50);

        $result = $this->whenICheckTheRequirementForCrewMushProportion($this->requirement);

        self::assertTrue($result);
    }

    public function testShouldReturnFalseIfRequiredProportionOfMushCrewIsNotMet(): void
    {
        // given 1 human player

        $this->givenMushPlayers(1);

        $this->givenRequirementForMushCrewProportion(50);

        $result = $this->whenICheckTheRequirementForCrewMushProportion($this->requirement);

        self::assertFalse($result);
    }

    private function givenAHumanPlayer(): Player
    {
        return PlayerFactory::createPlayerWithDaedalus($this->daedalus);
    }

    private function givenMushPlayers(int $number): void
    {
        for ($i = 0; $i < $number; ++$i) {
            $mushPlayer = PlayerFactory::createPlayerWithDaedalus($this->daedalus);
            StatusFactory::createChargeStatusFromStatusName(
                name: PlayerStatusEnum::MUSH,
                holder: $mushPlayer,
            );
        }
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
