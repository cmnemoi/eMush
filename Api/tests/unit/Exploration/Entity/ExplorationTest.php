<?php

namespace Mush\Tests\unit\Exploration\Entity;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Exploration\Entity\Exploration;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass \Mush\Exploration\Entity\Exploration
 */
final class ExplorationTest extends TestCase
{
    public function testExplorationSkillStepLogic(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $player = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $player2 = PlayerFactory::createPlayerWithDaedalus($daedalus);
        $this->setPlayerId($player);
        $this->setPlayerId($player2, 2);

        Skill::createByNameForPlayer(SkillEnum::SPRINTER, $player);
        Skill::createByNameForPlayer(SkillEnum::PILOT, $player);

        $exploration = $this->createPartialMock(Exploration::class, ['getPlanet', 'getExplorators']);
        $exploration
            ->method('getExplorators')
            ->willReturn(new PlayerCollection([$player, $player2]));

        self::assertContains(SkillEnum::SPRINTER, $player->getSkills()->map(static fn (Skill $skill) => $skill->getName())->toArray());
        self::assertEquals(1, $exploration->countSkillExtendingExploration());
    }

    private function setPlayerId(Player $player, int $id = 1): void
    {
        $reflection = new \ReflectionClass($player);
        $reflection->getProperty('id')->setValue($player, $id);
    }
}
