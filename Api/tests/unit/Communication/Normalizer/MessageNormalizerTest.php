<?php

namespace Mush\Tests\unit\Communication\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Communication\Enum\MessageModificationEnum;
use Mush\Communication\Normalizer\MessageNormalizer;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class MessageNormalizerTest extends TestCase
{
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    private MessageNormalizer $normalizer;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new MessageNormalizer(
            $this->translationService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testNormalizePlayerMessage()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $playerConfig);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($playerInfo)
            ->setMessage('message')
            ->setCreatedAt($createdAt)
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once()
        ;

        $currentPlayer = new Player();
        $currentPlayer
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($currentPlayer, new User(), new CharacterConfig()))
        ;

        $context = ['currentPlayer' => $currentPlayer];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => 'message',
            'date' => 'translated date',
            'child' => [],
            'isUnread' => true,
        ], $normalizedData);
    }

    public function testNormalizeNeronMessage()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $neron = new Neron();
        $neron->setDaedalusInfo($daedalusInfo);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setNeron($neron)
            ->setMessage('message')
            ->setCreatedAt($createdAt)
            ->setTranslationParameters([
                'player' => CharacterEnum::ANDIE,
                'cause' => EndCauseEnum::ABANDONED,
                'targetEquipment' => EquipmentEnum::ANTENNA,
            ])
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('message', $message->getTranslationParameters(), 'neron', LanguageEnum::FRENCH)
            ->andReturn('translatedMessage')
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with(CharacterEnum::NERON . '.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once()
        ;

        $currentPlayer = new Player();
        $currentPlayer
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($currentPlayer, new User(), new CharacterConfig()))
        ;

        $context = ['currentPlayer' => $currentPlayer];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => CharacterEnum::NERON, 'value' => 'translatedName'],
            'message' => 'translatedMessage',
            'date' => 'translated date',
            'child' => [],
            'isUnread' => true,
        ], $normalizedData);
    }

    public function testNormalizeNeronMessageWithChild()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $neron = new Neron();
        $neron->setDaedalusInfo($daedalusInfo);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $playerConfig);

        $createdAt = new \DateTime();

        $playerMessage = new Message();
        $playerMessage
            ->setAuthor($playerInfo)
            ->setMessage('message child')
            ->setCreatedAt($createdAt)
        ;

        $neronMessage = new Message();
        $neronMessage
            ->setNeron($neron)
            ->setMessage('message parent')
            ->setCreatedAt($createdAt)
            ->setChild(new ArrayCollection([$playerMessage]))
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with(CharacterEnum::NERON . '.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated player name')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message parent', [], 'neron', LanguageEnum::FRENCH)
            ->andReturn('translated message parent')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->twice()
        ;

        $currentPlayer = new Player();
        $currentPlayer
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($currentPlayer, new User(), new CharacterConfig()))
        ;

        $context = ['currentPlayer' => $currentPlayer];
        $normalizedData = $this->normalizer->normalize($neronMessage, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => CharacterEnum::NERON, 'value' => 'translatedName'],
            'message' => 'translated message parent',
            'date' => 'translated date',
            'child' => [[
                'id' => null,
                'character' => ['key' => 'name', 'value' => 'translated player name'],
                'message' => 'message child',
                'date' => 'translated date',
                'child' => [],
                'isUnread' => true,
            ]],
            'isUnread' => true,
        ], $normalizedData);
    }

    public function testNormalizeParanoiacPlayerMessage()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $player
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($player, new User(), new CharacterConfig()))
        ;

        $otherPlayer = new Player();
        $otherPlayerInfo = new PlayerInfo($otherPlayer, new User(), $playerConfig);

        $symptomConfig = new EventModifierConfig(MessageModificationEnum::PARANOIA_MESSAGES);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setModifierConfigs([$symptomConfig]);
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($otherPlayerInfo)
            ->setMessage('modified message')
            ->setCreatedAt($createdAt)
            ->setTranslationParameters([
                DiseaseMessagesEnum::MODIFICATION_CAUSE => MessageModificationEnum::PARANOIA_MESSAGES,
                DiseaseMessagesEnum::ORIGINAL_MESSAGE => 'original message',
            ])
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once()
        ;

        $context = ['currentPlayer' => $player];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => 'modified message',
            'date' => 'translated date',
            'child' => [],
            'isUnread' => true,
        ], $normalizedData);
    }
}
