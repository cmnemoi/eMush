<?php

namespace Mush\Tests\unit\Player\Normalizer;

use Mockery;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Normalizer\OtherPlayerNormalizer;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Normalizer\SkillNormalizer;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class OtherPlayerNormalizerTest extends TestCase
{
    private OtherPlayerNormalizer $normalizer;

    /** @var GearToolServiceInterface|Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    /** @var Mockery\Mock|TranslationServiceInterface */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->gearToolService = \Mockery::mock(GearToolServiceInterface::class);
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new OtherPlayerNormalizer($this->translationService, $this->gearToolService);
        $this->normalizer->setNormalizer(new SkillNormalizer($this->translationService));
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testNormalizer()
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ELEESHA, $daedalus);

        Skill::createByNameForPlayer(SkillEnum::DETACHED_CREWMEMBER, $player);
        Skill::createByNameForPlayer(SkillEnum::TRACKER, $player);
        Skill::createByNameForPlayer(SkillEnum::SPLASHPROOF, $player);

        $this->translationService
            ->shouldReceive('translate')
            ->with('eleesha.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated eleesha')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('eleesha.description', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated eleesha description')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('detached_crewmember.name', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->andReturn('translated detached crewmember')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('detached_crewmember.description', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->andReturn('translated detached crewmember description')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('tracker.name', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->andReturn('translated tracker')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('tracker.description', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->andReturn('translated tracker description')
            ->once();

        $this->translationService
            ->shouldReceive('translate')
            ->with('splashproof.name', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->never();

        $this->translationService
            ->shouldReceive('translate')
            ->with('splashproof.description', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->never();

        $data = $this->normalizer->normalize($player, null, ['currentPlayer' => Player::createNull()]);

        $expected = [
            'id' => $player->getId(),
            'character' => [
                'key' => CharacterEnum::ELEESHA,
                'value' => 'translated eleesha',
                'description' => 'translated eleesha description',
            ],
            'statuses' => [],
            'skills' => [
                [
                    'key' => SkillEnum::DETACHED_CREWMEMBER->toString(),
                    'name' => 'translated detached crewmember',
                    'description' => 'translated detached crewmember description',
                    'isMushSkill' => false,
                ],
                [
                    'key' => SkillEnum::TRACKER->toString(),
                    'name' => 'translated tracker',
                    'description' => 'translated tracker description',
                    'isMushSkill' => false,
                ],
            ],
            'titles' => [],
            'actions' => [],
        ];

        self::assertIsArray($data);
        self::assertSame($expected, $data);
    }

    public function testShouldNormalizeMushSkillsForMushCurrentPlayer(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ELEESHA, $daedalus);
        Skill::createByNameForPlayer(SkillEnum::SPLASHPROOF, $player);
        Skill::createByNameForPlayer(SkillEnum::DETACHED_CREWMEMBER, $player);

        $currentPlayer = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::ELEESHA, $daedalus);
        StatusFactory::createChargeStatusFromStatusName(
            name: PlayerStatusEnum::MUSH,
            holder: $currentPlayer,
        );

        $this->translationService
            ->shouldReceive('translate')
            ->with('splashproof.name', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->andReturn('translated splashproof')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('splashproof.description', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->andReturn('translated splashproof description')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('detached_crewmember.name', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->andReturn('translated detached crewmember')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('detached_crewmember.description', ['character' => 'eleesha'], 'skill', LanguageEnum::FRENCH)
            ->andReturn('translated detached crewmember description')
            ->once();
        $this->translationService->shouldIgnoreMissing();

        $data = $this->normalizer->normalize($player, null, ['currentPlayer' => $currentPlayer]);

        self::assertEquals(
            expected: [
                [
                    'key' => SkillEnum::SPLASHPROOF->toString(),
                    'name' => 'translated splashproof',
                    'description' => 'translated splashproof description',
                    'isMushSkill' => true,
                ],
                [
                    'key' => SkillEnum::DETACHED_CREWMEMBER->toString(),
                    'name' => 'translated detached crewmember',
                    'description' => 'translated detached crewmember description',
                    'isMushSkill' => false,
                ],
            ],
            actual: $data['skills']
        );
    }
}
