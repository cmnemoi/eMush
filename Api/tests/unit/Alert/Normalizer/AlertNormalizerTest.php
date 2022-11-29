<?php

namespace Mush\Tests\unit\Alert\Normalizer;

use Mockery;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Normalizer\AlertNormalizer;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class AlertNormalizerTest extends TestCase
{
    private AlertNormalizer $normalizer;

    /** @var TranslationService|Mockery\Mock */
    private TranslationService $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = Mockery::mock(TranslationService::class);

        $this->normalizer = new AlertNormalizer($this->translationService);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNormalize()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $alert = new Alert();
        $alert->setName('outcast')->setDaedalus($daedalus);

        $this->translationService
            ->shouldReceive('translate')
            ->with('outcast.name', [], 'alerts', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('outcast.description', [], 'alerts', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once()
        ;

        $normalized = $this->normalizer->normalize($alert);

        $this->assertEquals([
            'key' => 'outcast',
            'name' => 'translated one',
            'description' => 'translated two',
        ], $normalized);
    }

    public function testNormalizeHullAlert()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setHull(5)->setGameConfig($gameConfig);

        $alert = new Alert();
        $alert
            ->setName(AlertEnum::LOW_HULL)
            ->setDaedalus($daedalus)
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('low_hull.name', ['quantity' => 5], 'alerts', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('low_hull.description', ['quantity' => 5], 'alerts', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once()
        ;

        $normalized = $this->normalizer->normalize($alert);

        $this->assertEquals([
            'key' => 'low_hull',
            'name' => 'translated one',
            'description' => 'translated two',
        ], $normalized);
    }

    public function testNormalizeFireAlert()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setHull(5)->setGameConfig($gameConfig);

        $characterConfig = new CharacterConfig();
        $characterConfig->setName('andie');
        $player = new Player();
        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            $characterConfig
        );

        $room1 = new Place();
        $room1->setName('room1');
        $room2 = new Place();
        $room2->setName('room2');

        $fireElement1 = new AlertElement();
        $fireElement1
            ->setPlace($room1)
            ->setPlayerInfo($playerInfo)
        ;

        $fireElement2 = new AlertElement();
        $fireElement2
            ->setPlace($room2)
        ;

        $alert = new Alert();
        $alert
            ->setName('fire')
            ->setDaedalus($daedalus)
            ->addAlertElement($fireElement1)
            ->addAlertElement($fireElement2)
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('fire.name', ['quantity' => 2], 'alerts', LanguageEnum::FRENCH)
            ->andReturn('translated one')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('fire.description', ['quantity' => 2], 'alerts', LanguageEnum::FRENCH)
            ->andReturn('translated two')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('room1.name', [], 'rooms', LanguageEnum::FRENCH)
            ->andReturn('translated three')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('room1.loc_prep', [], 'rooms', LanguageEnum::FRENCH)
            ->andReturn('translated four')
            ->once()
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('fire.report', ['character' => 'andie', 'place' => 'translated three', 'loc_prep' => 'translated four'], 'alerts', LanguageEnum::FRENCH)
            ->andReturn('translated five')
            ->once()
        ;

        $normalized = $this->normalizer->normalize($alert);

        $this->assertEquals([
            'key' => 'fire',
            'name' => 'translated one',
            'description' => 'translated two',
            'reports' => ['translated five'],
        ], $normalized);
    }
}
