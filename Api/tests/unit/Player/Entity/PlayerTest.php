<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Entity;

use Mush\Player\Factory\PlayerFactory;
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
        $player = PlayerFactory::createPlayer();
        $project = ProjectFactory::createPilgredProject();

        $numberOfParticipationsStatus = StatusFactory::createChargeStatusWithName(PlayerStatusEnum::PROJECT_PARTICIPATIONS, $player);
        $numberOfParticipationsStatus
            ->setTarget($project)
            ->setCharge(1);

        self::assertEquals(0, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForMediumDifficultyProject(): void
    {
        $player = PlayerFactory::createPlayer();
        $project = ProjectFactory::createTrailReducerProject(); // base efficiency is 6

        $numberOfParticipationsStatus = StatusFactory::createChargeStatusWithName(PlayerStatusEnum::PROJECT_PARTICIPATIONS, $player);
        $numberOfParticipationsStatus
            ->setTarget($project)
            ->setCharge(1);

        self::assertEquals(4, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForMediumDifficultyProjectAfterTwoParticipations(): void
    {
        $player = PlayerFactory::createPlayer();
        $project = ProjectFactory::createTrailReducerProject(); // base efficiency is 6

        $numberOfParticipationsStatus = StatusFactory::createChargeStatusWithName(PlayerStatusEnum::PROJECT_PARTICIPATIONS, $player);
        $numberOfParticipationsStatus
            ->setTarget($project)
            ->setCharge(2);

        self::assertEquals(2, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForMediumDifficultyProjectAfterThreeParticipations(): void
    {
        $player = PlayerFactory::createPlayer();
        $project = ProjectFactory::createTrailReducerProject(); // base efficiency is 6

        $numberOfParticipationsStatus = StatusFactory::createChargeStatusWithName(PlayerStatusEnum::PROJECT_PARTICIPATIONS, $player);
        $numberOfParticipationsStatus
            ->setTarget($project)
            ->setCharge(3);

        self::assertEquals(1, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForHardProject(): void
    {
        $player = PlayerFactory::createPlayer();
        $project = ProjectFactory::createAutoWateringProject(); // base efficiency is 3

        $numberOfParticipationsStatus = StatusFactory::createChargeStatusWithName(PlayerStatusEnum::PROJECT_PARTICIPATIONS, $player);
        $numberOfParticipationsStatus
            ->setTarget($project)
            ->setCharge(1);

        self::assertEquals(1, $player->getMinEfficiencyForProject($project));
    }

    public function testGetMinEfficiencyForHardProjectForTwoParticipations(): void
    {
        $player = PlayerFactory::createPlayer();
        $project = ProjectFactory::createAutoWateringProject(); // base efficiency is 3

        $numberOfParticipationsStatus = StatusFactory::createChargeStatusWithName(PlayerStatusEnum::PROJECT_PARTICIPATIONS, $player);
        $numberOfParticipationsStatus
            ->setTarget($project)
            ->setCharge(2);

        self::assertEquals(0, $player->getMinEfficiencyForProject($project));
    }
}
