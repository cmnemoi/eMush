<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Player\Normalizer;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Normalizer\CommanderMissionNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CommanderMissionNormalizerTest extends TestCase
{
    private CommanderMissionNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new CommanderMissionNormalizer(new FakeCommanderMissionNormalizerTranslationService());
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

final class FakeCommanderMissionNormalizerTranslationService implements TranslationServiceInterface
{
    public function translate(string $key, array $parameters, string $domain, ?string $language = null): string
    {
        return match ($key) {
            'jin_su.name' => 'Jin Su',
            'commander_mission.buttons.label' => 'Accepter ?',
            'commander_mission.buttons.accept' => ':online: Oui + 3 :pa:',
            'commander_mission.buttons.reject' => ':offline: Non',
            default => "à l'instant",
        };
    }
}
