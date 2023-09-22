<?php

namespace Mush\Tests\unit\Communication\Service;

use Mockery;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Communication\Enum\MessageModificationEnum;
use Mush\Communication\Services\MessageModifierService;
use Mush\Communication\Services\MessageModifierServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationService;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Enum\LogDeclinationEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class DiseaseMessageServiceTest extends TestCase
{
    private MessageModifierServiceInterface $service;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = \Mockery::mock(TranslationService::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->service = new MessageModifierService(
            $this->randomService,
            $this->translationService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testDeafPlayer()
    {
        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $message = new Message();
        $message->setAuthor($playerInfo)->setMessage('some message');

        $modifiedMessage = $this->service->applyModifierEffects($message, $player, MessageModificationEnum::DEAF_SPEAK);

        $this->assertEquals('SOME MESSAGE', $modifiedMessage->getMessage());
    }

    public function testCoprolaliaPlayerNoTrigger()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $player = new Player();
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());
        $player->setDaedalus($daedalus);

        $message = new Message();
        $message->setAuthor($playerInfo)->setMessage('some message');

        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        $modifiedMessage = $this->service->applyModifierEffects($message, $player, MessageModificationEnum::COPROLALIA_MESSAGES);

        $this->assertEquals($message, $modifiedMessage);
    }

    public function testCoprolaliaPlayerTriggerReplace()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $message = new Message();
        $message->setAuthor($playerInfo)->setMessage('some message');

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->randomService->shouldReceive('random')->andReturn(1)->times(6);
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                DiseaseMessagesEnum::REPLACE_COPROLALIA, [
                    LogDeclinationEnum::VERSION => 1,
                    LogDeclinationEnum::WORD_COPROLALIA => 1,
                    LogDeclinationEnum::ANIMAL_COPROLALIA => 1,
                    LogDeclinationEnum::PREFIX_COPROLALIA => 1,
                    LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
                    LogDeclinationEnum::BALLS_COPROLALIA => 1,
                    ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('modified message')
            ->once()
        ;

        $modifiedMessage = $this->service->applyModifierEffects($message, $player, MessageModificationEnum::COPROLALIA_MESSAGES);

        $this->assertEquals('modified message', $modifiedMessage->getMessage());
        $this->assertEquals([], $modifiedMessage->getTranslationParameters());
    }

    public function testCoprolaliaPlayerTriggerPre()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $message = new Message();
        $message->setAuthor($playerInfo)->setMessage('Some message');

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->randomService->shouldReceive('random')->andReturn(1)->times(6);
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                DiseaseMessagesEnum::PRE_COPROLALIA, [
                LogDeclinationEnum::VERSION => 1,
                LogDeclinationEnum::WORD_COPROLALIA => 1,
                LogDeclinationEnum::ANIMAL_COPROLALIA => 1,
                LogDeclinationEnum::PREFIX_COPROLALIA => 1,
                LogDeclinationEnum::ADJECTIVE_COPROLALIA => 1,
                LogDeclinationEnum::BALLS_COPROLALIA => 1,
            ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('prefix, ')
            ->once()
        ;

        $modifiedMessage = $this->service->applyModifierEffects($message, $player, MessageModificationEnum::COPROLALIA_MESSAGES);

        $this->assertEquals('prefix, some message', $modifiedMessage->getMessage());
        $this->assertEquals([], $modifiedMessage->getTranslationParameters());
    }

    public function testParanoiaPlayerTriggerReplaceAware()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $message = new Message();
        $message->setAuthor($playerInfo)->setMessage('some message');

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->randomService->shouldReceive('random')->andReturn(1)->times(3);
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                DiseaseMessagesEnum::REPLACE_PARANOIA, [
                LogDeclinationEnum::VERSION => 1,
                LogDeclinationEnum::PARANOIA_VERSION_4 => 1,
                LogDeclinationEnum::PARANOIA_VERSION_6 => 1,
            ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('modified message')
            ->once()
        ;

        $modifiedMessage = $this->service->applyModifierEffects($message, $player, MessageModificationEnum::PARANOIA_MESSAGES);

        $this->assertEquals('modified message', $modifiedMessage->getMessage());
        $this->assertEquals([], $modifiedMessage->getTranslationParameters());
    }

    public function testParanoiaPlayerTriggerReplaceNotAware()
    {
        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);
        $playerInfo = new PlayerInfo($player, new User(), new CharacterConfig());

        $message = new Message();
        $message->setAuthor($playerInfo)->setMessage('Some message');

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        $this->randomService->shouldReceive('random')->andReturn(1)->times(3);
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                DiseaseMessagesEnum::REPLACE_PARANOIA, [
                LogDeclinationEnum::VERSION => 1,
                LogDeclinationEnum::PARANOIA_VERSION_4 => 1,
                LogDeclinationEnum::PARANOIA_VERSION_6 => 1,
            ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('modified message')
            ->once()
        ;

        $modifiedMessage = $this->service->applyModifierEffects($message, $player, MessageModificationEnum::PARANOIA_MESSAGES);

        $this->assertEquals('modified message', $modifiedMessage->getMessage());
        $this->assertEquals(
            [
                DiseaseMessagesEnum::ORIGINAL_MESSAGE => 'Some message',
                DiseaseMessagesEnum::MODIFICATION_CAUSE => MessageModificationEnum::PARANOIA_MESSAGES,
            ], $modifiedMessage->getTranslationParameters()
        );
    }

    public function testParanoiaPlayerTriggerAccuse()
    {
        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setCharacterName(CharacterEnum::ANDIE);
        $characterConfig2 = new CharacterConfig();
        $characterConfig2->setCharacterName(CharacterEnum::TERRENCE);

        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $gameConfig
            ->setCharactersConfig(new CharacterConfigCollection([$characterConfig1, $characterConfig2]))
        ;

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $player = new Player();

        $playerInfo = new PlayerInfo($player, new User(), $characterConfig1);

        $player->setDaedalus($daedalus)->setPlayerInfo($playerInfo);

        $message = new Message();
        $message->setAuthor($playerInfo)->setMessage('Some message');

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->randomService->shouldReceive('random')->andReturn(1)->times(3);
        $this->randomService
            ->shouldReceive('getRandomElements')
            ->with([CharacterEnum::TERRENCE], 1)
            ->andReturn([CharacterEnum::TERRENCE])
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with(
                DiseaseMessagesEnum::ACCUSE_PARANOIA, [
                'character' => CharacterEnum::TERRENCE,
                LogDeclinationEnum::VERSION => 1,
                LogDeclinationEnum::PARANOIA_VERSION_4 => 1,
                LogDeclinationEnum::PARANOIA_VERSION_6 => 1,
            ],
                'disease_message',
                LanguageEnum::FRENCH
            )
            ->andReturn('modified message')
            ->once()
        ;

        $modifiedMessage = $this->service->applyModifierEffects($message, $player, MessageModificationEnum::PARANOIA_MESSAGES);

        $this->assertEquals('modified message', $modifiedMessage->getMessage());
        $this->assertEquals([], $modifiedMessage->getTranslationParameters());
    }
}
