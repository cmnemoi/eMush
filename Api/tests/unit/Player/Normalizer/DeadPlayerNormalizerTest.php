<?php

namespace Mush\Tests\unit\Player\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Normalizer\DeadPlayerNormalizer;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class DeadPlayerNormalizerTest extends TestCase
{
    private DeadPlayerNormalizer $normalizer;

    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new DeadPlayerNormalizer($this->translationService);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testNormalizer()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = $this->createMock(Daedalus::class);
        $daedalus->method('getId')->willReturn(19);
        $daedalus->method('getGameConfig')->willReturn($gameConfig);
        $daedalus->method('getCycle')->willReturn(3);
        $daedalus->method('getDay')->willReturn(56);
        $daedalus->method('getLanguage')->willReturn(LanguageEnum::FRENCH);

        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName(CharacterEnum::ELEESHA)
        ;

        $otherPlayerDead = $this->createMock(Player::class);
        $playerInformationDead = new PlayerInfo($otherPlayerDead, new User(), $characterConfig);
        $closedPlayerDead = $playerInformationDead->getClosedPlayer();
        $closedPlayerDead
            ->setMessage('yoyoyo')
            ->setDayCycleDeath($daedalus)
            ->setEndCause(EndCauseEnum::ALLERGY)
            ->addLike()
        ;

        $playerInformationDead->setGameStatus(GameStatusEnum::FINISHED);
        $otherPlayerDead->method('getId')->willReturn(3);
        $otherPlayerDead->method('getName')->willReturn(CharacterEnum::ELEESHA);
        $otherPlayerDead->method('getPlayerInfo')->willReturn($playerInformationDead);

        $otherPlayerAlive = $this->createMock(Player::class);
        $playerInformationAlive = new PlayerInfo($otherPlayerAlive, new User(), $characterConfig);

        $otherPlayerAlive->method('getId')->willReturn(4);
        $otherPlayerAlive->method('getName')->willReturn(CharacterEnum::ELEESHA);
        $otherPlayerAlive->method('getPlayerInfo')->willReturn($playerInformationAlive);

        $player = $this->createMock(Player::class);
        $playerInformation = new PlayerInfo($player, new User(), $characterConfig);
        $playerInformation->setGameStatus(GameStatusEnum::FINISHED);
        $closedPlayer = $playerInformation->getClosedPlayer();
        $closedPlayer
            ->setDayCycleDeath($daedalus)
            ->setEndCause(EndCauseEnum::INJURY)
        ;

        $player->method('getName')->willReturn(CharacterEnum::ELEESHA);
        $player->method('getId')->willReturn(2);
        $player->method('getTriumph')->willReturn(33);
        $player->method('getPlayerInfo')->willReturn($playerInformation);
        $player->method('getTargetActions')->willReturn(new ArrayCollection());

        $this->translationService
            ->shouldReceive('translate')
            ->with('eleesha.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated eleesha')
            ->times(3)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('eleesha.abstract', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated eleesha description')
            ->times(2)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('allergy.name', [], 'end_cause', LanguageEnum::FRENCH)
            ->andReturn('translated end cause')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('allergy.description', [], 'end_cause', LanguageEnum::FRENCH)
            ->andReturn('translated end cause description')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('injury.name', [], 'end_cause', LanguageEnum::FRENCH)
            ->andReturn('translated injury')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('injury.description', [], 'end_cause', LanguageEnum::FRENCH)
            ->andReturn('translated injury description')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('still_living.name', [], 'end_cause', LanguageEnum::FRENCH)
            ->andReturn('translated still living')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('still_living.description', [], 'end_cause', LanguageEnum::FRENCH)
            ->andReturn('translated still living description')
            ->times(1)
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('calendar.description', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated calendar description')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('calendar.name', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated calendar name')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('triumph.name', [], 'player', LanguageEnum::FRENCH)
            ->andReturn('translated triumph')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('triumph.description', [], 'player', LanguageEnum::FRENCH)
            ->andReturn('translated triumph description')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('day.name', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated day')
            ->times(1)
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('cycle.name', [], 'daedalus', LanguageEnum::FRENCH)
            ->andReturn('translated cycle')
            ->times(1)
        ;

        $daedalus->method('getPlayers')->willReturn(new PlayerCollection([$player, $otherPlayerDead, $otherPlayerAlive]));
        $player->method('getDaedalus')->willReturn($daedalus);

        $data = $this->normalizer->normalize($player, null, ['currentPlayer' => $player]);

        $expected = [
            'id' => 2,
            'character' => [
                'key' => CharacterEnum::ELEESHA,
                'value' => 'translated eleesha',
            ],
            'gameStatus' => 'finished',
            'triumph' => [
                'quantity' => 33,
                'name' => 'translated triumph',
                'description' => 'translated triumph description',
            ],
            'daedalus' => [
                'key' => 19,
                'calendar' => [
                    'name' => 'translated calendar name',
                    'description' => 'translated calendar description',
                    'day' => 56,
                    'dayName' => 'translated day',
                    'cycle' => 3,
                    'cycleName' => 'translated cycle',
                ],
            ],
            'endCause' => [
                'key' => EndCauseEnum::INJURY,
                'name' => 'translated injury',
                'description' => 'translated injury description',
            ],
            'players' => [
                0 => [
                    'id' => 3,
                    'character' => [
                        'key' => CharacterEnum::ELEESHA,
                        'value' => 'translated eleesha',
                        'description' => 'translated eleesha description',
                        ],
                    'deathDay' => 56,
                    'deathCycle' => 3,
                    'endCause' => [
                            'key' => EndCauseEnum::ALLERGY,
                            'name' => 'translated end cause',
                            'description' => 'translated end cause description',
                    ],
                    'likes' => 1,
                ],
                1 => [
                    'id' => 4,
                    'character' => [
                        'key' => CharacterEnum::ELEESHA,
                        'value' => 'translated eleesha',
                        'description' => 'translated eleesha description',
                    ],
                    'deathDay' => 0,
                    'deathCycle' => 0,
                    'endCause' => [
                            'key' => EndCauseEnum::STILL_LIVING,
                            'name' => 'translated still living',
                            'description' => 'translated still living description',
                    ],
                    'likes' => 0,
                    ],
                ],
            ];

        $this->assertIsArray($data);
        $this->assertEquals($expected, $data);
    }
}
