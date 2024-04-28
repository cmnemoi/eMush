<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Entity;

use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\SkillEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Entity\Project;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\ValueObject\PlayerEfficiency;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerTest extends TestCase
{
    /**
     * @dataProvider provideShouldReturnPlayerEfficiencyCases
     */
    public function testShouldReturnPlayerEfficiency(
        Project $project,
        int $numberOfParticipations,
        PlayerEfficiency $expectedEfficiency
    ): void {
        // Given I have a player
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());

        // Given this player has a certain number of participations in a project
        $this->setPlayerNumberOfParticipations($player, $project, $numberOfParticipations);

        // When I ask for the player's efficiency in this project
        $actualEfficiency = $player->getEfficiencyForProject($project);

        // Then I should get the expected efficiency
        self::assertEquals(
            expected: $expectedEfficiency,
            actual: $actualEfficiency
        );
    }

    /**
     * @dataProvider provideShouldReturnPlayerEfficiencyWithCpuPriorityCases
     */
    public function testShouldReturnPlayerEfficiencyWithCpuPriority(
        Project $project,
        int $numberOfParticipations,
        PlayerEfficiency $expectedEfficiency
    ): void {
        // Given I have a player
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // Given this player has a certain number of participations in a project
        $this->setPlayerNumberOfParticipations($player, $project, $numberOfParticipations);

        // Given CPU priority is set to projects
        $daedalus->getDaedalusInfo()->getNeron()->setCpuPriority(NeronCpuPriorityEnum::PROJECTS);

        // When I ask for the player's efficiency in this project
        $actualEfficiency = $player->getEfficiencyForProject($project);

        // Then I should get the expected efficiency
        self::assertEquals(
            expected: $expectedEfficiency,
            actual: $actualEfficiency
        );
    }

    public function testShouldReturnPlayerEfficiencyWithOneBonusSkill(): void
    {
        // Given I have a player
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // Given player has the Pilot skill
        StatusFactory::createStatusByNameForHolder(SkillEnum::PILOT, holder: $player);

        // when I ask for the player's efficiency in Trail Reducer project
        $actualEfficiency = $player->getEfficiencyForProject(ProjectFactory::createTrailReducerProject());

        // Then I should get the expected efficiency
        self::assertEquals(
            expected: new PlayerEfficiency(10, 15),
            actual: $actualEfficiency
        );
    }

    public function testShouldReturnPlayerEfficiencyWithTwoBonusSkills(): void
    {
        // Given I have a player
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);

        // Given player has the Pilot and Technician skills
        StatusFactory::createStatusByNameForHolder(SkillEnum::PILOT, holder: $player);
        StatusFactory::createStatusByNameForHolder(SkillEnum::TECHNICIAN, holder: $player);

        // when I ask for the player's efficiency in Trail Reducer project
        $actualEfficiency = $player->getEfficiencyForProject(ProjectFactory::createTrailReducerProject());

        // Then I should get the expected efficiency
        self::assertEquals(
            expected: new PlayerEfficiency(14, 21),
            actual: $actualEfficiency
        );
    }

    /**
     * Test cases for the getEfficiencyForProject method from Twinpedia: http://twin.tithom.fr/mush/stats/efficacite/.
     *
     * Example of a test case:
     *
     * [$pilgred, 0, new PlayerEfficiency(1, 1)] => For Pilgred project, with 0 participations,
     * the efficiency should be 1%-1%.
     */
    public static function provideShouldReturnPlayerEfficiencyCases(): iterable
    {
        $pilgred = ProjectFactory::createPilgredProject();
        $trailReducer = ProjectFactory::createTrailReducerProject();
        $autoWatering = ProjectFactory::createAutoWateringProject();

        return [
            [$pilgred, 0, new PlayerEfficiency(1, 1)],
            [$pilgred, 1, new PlayerEfficiency(0, 0)],
            [$pilgred, 2, new PlayerEfficiency(0, 0)],
            [$pilgred, 3, new PlayerEfficiency(0, 0)],
            [$trailReducer, 0, new PlayerEfficiency(6, 9)],
            [$trailReducer, 1, new PlayerEfficiency(4, 6)],
            [$trailReducer, 2, new PlayerEfficiency(2, 3)],
            [$trailReducer, 3, new PlayerEfficiency(0, 0)],
            [$autoWatering, 0, new PlayerEfficiency(3, 4)],
            [$autoWatering, 1, new PlayerEfficiency(1, 1)],
            [$autoWatering, 2, new PlayerEfficiency(0, 0)],
        ];
    }

    /**
     * Test cases for the getEfficiencyForProject with CPU priority method from Twinpedia: http://twin.tithom.fr/mush/stats/efficacite/.
     *
     * Example of a test case:
     *
     * [$traileReducer, 0, new PlayerEfficiency(7, 10)] => For Trail Reducuer project, with 0 participations,
     * the efficiency should be 7%-10%.
     */
    public static function provideShouldReturnPlayerEfficiencyWithCpuPriorityCases(): iterable
    {
        $plasmaShield = ProjectFactory::createPlasmaShieldProject();
        $trailReducer = ProjectFactory::createTrailReducerProject();
        $autoWatering = ProjectFactory::createAutoWateringProject();

        return [
            [$plasmaShield, 0, new PlayerEfficiency(2, 3)],
            [$plasmaShield, 1, new PlayerEfficiency(1, 1)],
            [$plasmaShield, 2, new PlayerEfficiency(1, 1)],
            [$trailReducer, 0, new PlayerEfficiency(7, 10)],
            [$trailReducer, 1, new PlayerEfficiency(5, 7)],
            [$trailReducer, 2, new PlayerEfficiency(3, 4)],
            [$trailReducer, 3, new PlayerEfficiency(1, 1)],
            [$trailReducer, 4, new PlayerEfficiency(1, 1)],
            [$autoWatering, 0, new PlayerEfficiency(4, 6)],
            [$autoWatering, 1, new PlayerEfficiency(2, 3)],
            [$autoWatering, 2, new PlayerEfficiency(1, 1)],
            [$autoWatering, 3, new PlayerEfficiency(1, 1)],
        ];
    }

    private function setPlayerNumberOfParticipations(Player $player, Project $project, int $charge): void
    {
        $numberOfParticipationsStatus = StatusFactory::createChargeStatusWithName(PlayerStatusEnum::PROJECT_PARTICIPATIONS, $player);
        $numberOfParticipationsStatus
            ->setTarget($project)
            ->setCharge($charge);
    }
}
