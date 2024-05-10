<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Entity;

use Mush\Daedalus\Enum\NeronCpuPriorityEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\SkillEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Factory\ProjectFactory;
use Mush\Project\ValueObject\PlayerEfficiency;
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
        $this->setPlayerId($player, 1);

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
        $this->setPlayerId($player, 1);

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
        $this->setPlayerId($player, 1);

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
        $this->setPlayerId($player, 1);

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
        $heatLamp = ProjectFactory::createHeatLampProject();
        $fireSensor = ProjectFactory::createNeronProjectByName(ProjectName::FIRE_SENSOR);

        return [
            'PILGRED, 0 participations' => [$pilgred, 0, new PlayerEfficiency(1, 1)],
            'PILGRED, 1 participations' => [$pilgred, 1, new PlayerEfficiency(0, 0)],
            'PILGRED, 2 participations' => [$pilgred, 2, new PlayerEfficiency(0, 0)],
            'PILGRED, 3 participations' => [$pilgred, 3, new PlayerEfficiency(0, 0)],
            'Heat Lamps, 0 participations' => [$heatLamp, 0, new PlayerEfficiency(3, 4)],
            'Heat Lamps, 1 participations' => [$heatLamp, 1, new PlayerEfficiency(1, 1)],
            'Heat Lamps, 2 participations' => [$heatLamp, 2, new PlayerEfficiency(0, 0)],
            'Trail Reducer, 0 participations' => [$trailReducer, 0, new PlayerEfficiency(6, 9)],
            'Trail Reducer, 1 participations' => [$trailReducer, 1, new PlayerEfficiency(4, 6)],
            'Trail Reducer, 2 participations' => [$trailReducer, 2, new PlayerEfficiency(2, 3)],
            'Trail Reducer, 3 participations' => [$trailReducer, 3, new PlayerEfficiency(0, 0)],
            'Fire sensors, 0 participations' => [$fireSensor, 0, new PlayerEfficiency(18, 27)],
            'Fire sensors, 1 participations' => [$fireSensor, 1, new PlayerEfficiency(16, 24)],
            'Fire sensors, 2 participations' => [$fireSensor, 2, new PlayerEfficiency(14, 21)],
            'Fire sensors, 3 participations' => [$fireSensor, 3, new PlayerEfficiency(12, 18)],
            'Fire sensors, 4 participations' => [$fireSensor, 4, new PlayerEfficiency(10, 15)],
            'Fire sensors, 5 participations' => [$fireSensor, 5, new PlayerEfficiency(8, 12)],
            'Fire sensors, 6 participations' => [$fireSensor, 6, new PlayerEfficiency(6, 9)],
            'Fire sensors, 7 participations' => [$fireSensor, 7, new PlayerEfficiency(4, 6)],
            'Fire sensors, 8 participations' => [$fireSensor, 8, new PlayerEfficiency(2, 3)],
            'Fire sensors, 9 participations' => [$fireSensor, 9, new PlayerEfficiency(0, 0)],
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
        $heatLamp = ProjectFactory::createHeatLampProject();
        $trailReducer = ProjectFactory::createTrailReducerProject();
        $fireSensor = ProjectFactory::createNeronProjectByName(ProjectName::FIRE_SENSOR);

        return [
            'Plasma shield, 0 participations' => [$plasmaShield, 0, new PlayerEfficiency(2, 3)],
            'Plasma Shield, 1 participations' => [$plasmaShield, 1, new PlayerEfficiency(1, 1)],
            'Plasma Shield, 2 participations' => [$plasmaShield, 2, new PlayerEfficiency(1, 1)],
            'Heat Lamps, 0 participations' => [$heatLamp, 0, new PlayerEfficiency(4, 6)],
            'Heat Lamps, 1 participations' => [$heatLamp, 1, new PlayerEfficiency(2, 3)],
            'Heat Lamps, 2 participations' => [$heatLamp, 2, new PlayerEfficiency(1, 1)],
            'Heat Lamps, 3 participations' => [$heatLamp, 3, new PlayerEfficiency(1, 1)],
            'Trail Reducer, 0 participations' => [$trailReducer, 0, new PlayerEfficiency(7, 10)],
            'Trail Reducer, 1 participations' => [$trailReducer, 1, new PlayerEfficiency(5, 7)],
            'Trail Reducer, 2 participations' => [$trailReducer, 2, new PlayerEfficiency(3, 4)],
            'Trail Reducer, 3 participations' => [$trailReducer, 3, new PlayerEfficiency(1, 1)],
            'Trail Reducer, 4 participations' => [$trailReducer, 4, new PlayerEfficiency(1, 1)],
            'Fire sensors, 0 participations' => [$fireSensor, 0, new PlayerEfficiency(19, 28)],
            'Fire sensors, 1 participations' => [$fireSensor, 1, new PlayerEfficiency(17, 25)],
            'Fire sensors, 2 participations' => [$fireSensor, 2, new PlayerEfficiency(15, 22)],
            'Fire sensors, 3 participations' => [$fireSensor, 3, new PlayerEfficiency(13, 19)],
            'Fire sensors, 4 participations' => [$fireSensor, 4, new PlayerEfficiency(11, 16)],
            'Fire sensors, 5 participations' => [$fireSensor, 5, new PlayerEfficiency(9, 13)],
            'Fire sensors, 6 participations' => [$fireSensor, 6, new PlayerEfficiency(7, 10)],
            'Fire sensors, 7 participations' => [$fireSensor, 7, new PlayerEfficiency(5, 7)],
            'Fire sensors, 8 participations' => [$fireSensor, 8, new PlayerEfficiency(3, 4)],
            'Fire sensors, 9 participations' => [$fireSensor, 9, new PlayerEfficiency(1, 1)],
            'Fire sensors, 10 participations' => [$fireSensor, 10, new PlayerEfficiency(1, 1)],
        ];
    }

    private function setPlayerId(Player $player, int $id): void
    {
        $reflection = new \ReflectionClass($player);
        $reflection->getProperty('id')->setValue($player, $id);
    }

    private function setPlayerNumberOfParticipations(Player $player, Project $project, int $number): void
    {
        $project->resetPlayerParticipations($player);
        for ($i = 0; $i < $number; ++$i) {
            $project->addPlayerParticipation($player);
        }
    }
}
