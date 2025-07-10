<?php

namespace Mush\Tests\unit\Chat\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Chat\Enum\DiseaseMessagesEnum;
use Mush\Chat\Enum\MessageModificationEnum;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Chat\Normalizer\MessageNormalizer;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Entity\Neron;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\Random\FakeGetRandomIntegerService;
use Mush\Game\Service\Random\RandomString;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MessageNormalizerTest extends TestCase
{
    /** @var Mockery\Mock|TranslationServiceInterface */
    private TranslationServiceInterface $translationService;

    private MessageNormalizer $normalizer;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new MessageNormalizer(
            new RandomString(new FakeGetRandomIntegerService(result: 0)),
            $this->translationService,
        );
    }

    /**
     * @after
     */
    protected function tearDown(): void
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
        $playerConfig->setName('name')->setCharacterName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $playerConfig);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($playerInfo)
            ->setChannel(new Channel())
            ->setMessage('message')
            ->setCreatedAt($createdAt);

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once();

        $currentPlayer = new Player();
        $currentPlayer
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($currentPlayer, new User(), new CharacterConfig()));

        $context = ['currentPlayer' => $currentPlayer];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        self::assertSame([
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
        $playerConfig->setName('name')->setCharacterName('name');

        $neron = new Neron();
        $neron->setDaedalusInfo($daedalusInfo);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setNeron($neron)
            ->setMessage('message')
            ->setChannel(new Channel())
            ->setCreatedAt($createdAt)
            ->setTranslationParameters([
                'player' => CharacterEnum::ANDIE,
                'cause' => EndCauseEnum::ABANDONED,
                'targetEquipment' => EquipmentEnum::ANTENNA,
            ]);

        $this->translationService
            ->shouldReceive('translate')
            ->with('message', $message->getTranslationParameters(), 'neron', LanguageEnum::FRENCH)
            ->andReturn('translatedMessage');
        $this->translationService
            ->shouldReceive('translate')
            ->with(CharacterEnum::NERON . '.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName');
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once();

        $currentPlayer = new Player();
        $currentPlayer
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($currentPlayer, new User(), new CharacterConfig()));

        $context = ['currentPlayer' => $currentPlayer];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        self::assertSame([
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
        $playerConfig->setName('name')->setCharacterName('name');

        $neron = new Neron();
        $neron->setDaedalusInfo($daedalusInfo);

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name')->setCharacterName('name');

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), $playerConfig);

        $createdAt = new \DateTime();

        $playerMessage = new Message();
        $playerMessage
            ->setAuthor($playerInfo)
            ->setChannel(new Channel())
            ->setMessage('message child')
            ->setCreatedAt($createdAt);

        $neronMessage = new Message();
        $neronMessage
            ->setNeron($neron)
            ->setMessage('message parent')
            ->setChannel(new Channel())
            ->setCreatedAt($createdAt)
            ->setChild(new ArrayCollection([$playerMessage]));

        $this->translationService
            ->shouldReceive('translate')
            ->with(CharacterEnum::NERON . '.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translated player name')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('message parent', [], 'neron', LanguageEnum::FRENCH)
            ->andReturn('translated message parent')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->twice();

        $currentPlayer = new Player();
        $currentPlayer
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($currentPlayer, new User(), new CharacterConfig()));

        $context = ['currentPlayer' => $currentPlayer];
        $normalizedData = $this->normalizer->normalize($neronMessage, null, $context);

        self::assertSame([
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
        $playerConfig->setName('name')->setCharacterName('name');

        $player = new Player();
        $player
            ->setDaedalus($daedalus)
            ->setPlayerInfo(new PlayerInfo($player, new User(), new CharacterConfig()));

        $otherPlayer = new Player();
        $otherPlayerInfo = new PlayerInfo($otherPlayer, new User(), $playerConfig);

        $symptomConfig = new EventModifierConfig(MessageModificationEnum::PARANOIA_MESSAGES);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setModifierConfigs([$symptomConfig]);
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE);

        $player->addMedicalCondition($playerDisease);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($otherPlayerInfo)
            ->setMessage('modified message')
            ->setChannel(new Channel())
            ->setCreatedAt($createdAt)
            ->setTranslationParameters([
                DiseaseMessagesEnum::MODIFICATION_CAUSE => MessageModificationEnum::PARANOIA_MESSAGES,
                DiseaseMessagesEnum::ORIGINAL_MESSAGE => 'original message',
            ]);

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('translatedName');
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('translated date')
            ->once();

        $context = ['currentPlayer' => $player];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        self::assertSame([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => 'modified message',
            'date' => 'translated date',
            'child' => [],
            'isUnread' => true,
        ], $normalizedData);
    }

    public function testShouldNormalizeVocodedAnnouncement(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $publicChannel = new Channel();
        $publicChannel
            ->setDaedalus($daedalus->getDaedalusInfo())
            ->setScope(ChannelScopeEnum::PUBLIC);

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);

        $message = new Message();
        $message->setMessage('azaza');
        $message->setChannel($publicChannel);
        $message->setNeron($daedalus->getNeron());
        $message->setCreatedAt(new \DateTime());
        $message->setUpdatedAt(new \DateTime());
        $message->setCycle(1);
        $message->setDay(1);
        $message->setAuthor($player->getPlayerInfo());

        $this->translationService
            ->shouldReceive('translate')
            ->with(CharacterEnum::NERON . '.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('NERON')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('à l\'instant')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('azaza', [], 'neron', LanguageEnum::FRENCH)
            ->andReturn('azaza')
            ->once();

        $normalizedData = $this->normalizer->normalize($message, null, ['currentPlayer' => $player]);

        self::assertSame([
            'id' => null,
            'character' => ['key' => 'neron', 'value' => 'NERON'],
            'message' => 'azaza',
            'date' => 'à l\'instant',
            'child' => [],
            'isUnread' => true,
        ], $normalizedData);
    }

    public function testShouldNormalizeRefusedVocodedAnnouncement(): void
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $publicChannel = new Channel();
        $publicChannel
            ->setDaedalus($daedalus->getDaedalusInfo())
            ->setScope(ChannelScopeEnum::PUBLIC);

        $player = PlayerFactory::createPlayerByNameAndDaedalus(CharacterEnum::CHUN, $daedalus);

        $message = new Message();
        $message->setMessage(NeronMessageEnum::COMMAND_REFUSED);
        $message->setChannel($publicChannel);
        $message->setNeron($daedalus->getNeron());
        $message->setCreatedAt(new \DateTime());
        $message->setUpdatedAt(new \DateTime());
        $message->setCycle(1);
        $message->setDay(1);
        $message->setAuthor($player->getPlayerInfo());

        $this->translationService
            ->shouldReceive('translate')
            ->with(CharacterEnum::NERON . '.name', [], 'characters', LanguageEnum::FRENCH)
            ->andReturn('NERON')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('message_date.less_minute', [], 'chat', LanguageEnum::FRENCH)
            ->andReturn('à l\'instant')
            ->once();
        $this->translationService
            ->shouldReceive('translate')
            ->with('command_refused', [], 'neron', LanguageEnum::FRENCH)
            ->andReturn('Rien ne se passe, la commande a été refusée par NERON.')
            ->once();

        $normalizedData = $this->normalizer->normalize($message, null, ['currentPlayer' => $player]);

        self::assertSame([
            'id' => null,
            'character' => ['key' => 'neron', 'value' => 'NERON'],
            'message' => 'Rien ne se passe, la commande a été refusée par NERON.',
            'date' => 'à l\'instant',
            'child' => [],
            'isUnread' => true,
        ], $normalizedData);
    }
}
