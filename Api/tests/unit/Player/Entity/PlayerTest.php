<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Entity;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Entity\Project;
use Mush\Project\Factory\ProjectFactory;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerTest extends TestCase
{
    public function testGetMinEfficiencyForPilgred(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $project = ProjectFactory::createPilgredProject();

        $this->setPlayerNumberOfParticipations($player, $project, 1);

        self::assertEquals(0, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForMediumDifficultyProject(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $project = ProjectFactory::createTrailReducerProject(); // base efficiency is 6

        $this->setPlayerNumberOfParticipations($player, $project, 1);

        self::assertEquals(4, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForMediumDifficultyProjectAfterTwoParticipations(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $project = ProjectFactory::createTrailReducerProject(); // base efficiency is 6

        $this->setPlayerNumberOfParticipations($player, $project, 2);

        self::assertEquals(2, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForMediumDifficultyProjectAfterThreeParticipations(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $project = ProjectFactory::createTrailReducerProject(); // base efficiency is 6

        $this->setPlayerNumberOfParticipations($player, $project, 3);

        self::assertEquals(0, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForHardProject(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $project = ProjectFactory::createAutoWateringProject(); // base efficiency is 3

        $this->setPlayerNumberOfParticipations($player, $project, 1);

        self::assertEquals(1, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForHardProjectForTwoParticipations(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $project = ProjectFactory::createAutoWateringProject(); // base efficiency is 3

        $this->setPlayerNumberOfParticipations($player, $project, 2);

        self::assertEquals(0, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMaxEfficiencyForMediumDifficultyProject(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $project = ProjectFactory::createTrailReducerProject(); // base min efficiency is 6

        self::assertEquals(9, $player->getMaxEfficiencyForProject($project));
    }

    public function testGetMaxEfficiencyForMediumDifficultyProjectAfterOneParticipations(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $project = ProjectFactory::createTrailReducerProject(); // base min efficiency is 6

        $this->setPlayerNumberOfParticipations($player, $project, 1);

        self::assertEquals(6, $player->getMaxEfficiencyForProject($project));
    }

    private function setPlayerNumberOfParticipations(Player $player, Project $project, int $charge): void
    {
        $numberOfParticipationsStatus = StatusFactory::createChargeStatusWithName(PlayerStatusEnum::PROJECT_PARTICIPATIONS, $player);
        $numberOfParticipationsStatus
            ->setTarget($project)
            ->setCharge($charge);
    }
}
