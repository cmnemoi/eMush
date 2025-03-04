<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Normalizer;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Normalizer\CommanderMissionNormalizer;
use Mush\Tests\unit\Player\TestDoubles\FakeTranslationService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CommanderMissionNormalizerTest extends TestCase
{
    private CommanderMissionNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new CommanderMissionNormalizer(new FakeTranslationService());
    }

    public function testShouldNormalizeCommanderMission(): void
    {
        // given
        $daedalus = DaedalusFactory::createDaedalus();
        $commanderMission = new CommanderMission(
            commander: PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::JIN_SU, $daedalus),
            subordinate: PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::KUAN_TI, $daedalus),
            mission: 'Mission'
        );
        $commanderMission->setCreatedAt(new \DateTime());
        $this->setId($commanderMission, 1);

        // when
        $normalizedCommanderMission = $this->normalizer->normalize($commanderMission);

        // then
        self::assertEquals([
            'id' => 1,
            'commander' => [
                'id' => $commanderMission->getCommander()->getId(),
                'key' => 'jin_su',
                'name' => 'Jin Su',
            ],
            'mission' => 'Mission',
            'date' => 'à l\'instant',
            'isPending' => true,
            'isCompleted' => false,
        ], $normalizedCommanderMission);
    }

    private function setId(CommanderMission $commanderMission, int $id): void
    {
        (new \ReflectionProperty(CommanderMission::class, 'id'))->setValue($commanderMission, $id);
    }
}
