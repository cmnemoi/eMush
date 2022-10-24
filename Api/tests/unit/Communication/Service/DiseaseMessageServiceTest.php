<?php

namespace Mush\Test\Communication\Service;

use Mockery;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Communication\Services\DiseaseMessageService;
use Mush\Communication\Services\DiseaseMessageServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationService;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Config\CharacterConfigCollection;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogDeclinationEnum;
use PHPUnit\Framework\TestCase;

class DiseaseMessageServiceTest extends TestCase
{
    private DiseaseMessageServiceInterface $service;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;
    /** @var TranslationServiceInterface|Mockery\Mock */
    private TranslationServiceInterface $translationService;

    /**
     * @before
     */
    public function before()
    {
        $this->translationService = Mockery::mock(TranslationService::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->service = new DiseaseMessageService(
            $this->randomService,
            $this->translationService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testDeafPlayer()
    {
        $player = new Player();

        $symptomConfig = new SymptomConfig(SymptomEnum::DEAF);
        $symptomConfig->setTrigger(EventEnum::ON_NEW_MESSAGE);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $message = new Message();
        $message->setAuthor($player)->setMessage('some message');

        $modifiedMessage = $this->service->applyDiseaseEffects($message);

        $this->assertEquals('SOME MESSAGE', $modifiedMessage->getMessage());
    }

    public function testCoprolaliaPlayerNoTrigger()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $symptomConfig = new SymptomConfig(SymptomEnum::COPROLALIA_MESSAGES);
        $symptomConfig->setTrigger(EventEnum::ON_NEW_MESSAGE);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $message = new Message();
        $message->setAuthor($player)->setMessage('some message');

        $this->randomService->shouldReceive('isSuccessful')->andReturn(false)->once();

        $modifiedMessage = $this->service->applyDiseaseEffects($message);

        $this->assertEquals($message, $modifiedMessage);
    }

    public function testCoprolaliaPlayerTriggerReplace()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $symptomConfig = new SymptomConfig(SymptomEnum::COPROLALIA_MESSAGES);
        $symptomConfig->setTrigger(EventEnum::ON_NEW_MESSAGE);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $message = new Message();
        $message->setAuthor($player)->setMessage('some message');

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

        $modifiedMessage = $this->service->applyDiseaseEffects($message);

        $this->assertEquals('modified message', $modifiedMessage->getMessage());
        $this->assertEquals([], $modifiedMessage->getTranslationParameters());
    }

    public function testCoprolaliaPlayerTriggerPre()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $symptomConfig = new SymptomConfig(SymptomEnum::COPROLALIA_MESSAGES);
        $symptomConfig->setTrigger(EventEnum::ON_NEW_MESSAGE);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $message = new Message();
        $message->setAuthor($player)->setMessage('Some message');

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

        $modifiedMessage = $this->service->applyDiseaseEffects($message);

        $this->assertEquals('prefix, some message', $modifiedMessage->getMessage());
        $this->assertEquals([], $modifiedMessage->getTranslationParameters());
    }

    public function testParanoiaPlayerTriggerReplaceAware()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $symptomConfig = new SymptomConfig(SymptomEnum::PARANOIA_MESSAGES);
        $symptomConfig->setTrigger(EventEnum::ON_NEW_MESSAGE);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $message = new Message();
        $message->setAuthor($player)->setMessage('some message');

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

        $modifiedMessage = $this->service->applyDiseaseEffects($message);

        $this->assertEquals('modified message', $modifiedMessage->getMessage());
        $this->assertEquals([], $modifiedMessage->getTranslationParameters());
    }

    public function testParanoiaPlayerTriggerReplaceNotAware()
    {
        $gameConfig = new GameConfig();
        $gameConfig->setLanguage(LanguageEnum::FRENCH);
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $player = new Player();
        $player->setDaedalus($daedalus);

        $symptomConfig = new SymptomConfig(SymptomEnum::PARANOIA_MESSAGES);
        $symptomConfig->setTrigger(EventEnum::ON_NEW_MESSAGE);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $message = new Message();
        $message->setAuthor($player)->setMessage('Some message');

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

        $modifiedMessage = $this->service->applyDiseaseEffects($message);

        $this->assertEquals('modified message', $modifiedMessage->getMessage());
        $this->assertEquals(
            [
                DiseaseMessagesEnum::ORIGINAL_MESSAGE => 'Some message',
                DiseaseMessagesEnum::MODIFICATION_CAUSE => SymptomEnum::PARANOIA_MESSAGES,
            ], $modifiedMessage->getTranslationParameters()
        );
    }

    public function testParanoiaPlayerTriggerAccuse()
    {
        $characterConfig1 = new CharacterConfig();
        $characterConfig1->setName(CharacterEnum::ANDIE);
        $characterConfig2 = new CharacterConfig();
        $characterConfig2->setName(CharacterEnum::TERRENCE);

        $gameConfig = new GameConfig();
        $gameConfig
            ->setCharactersConfig(new CharacterConfigCollection([$characterConfig1, $characterConfig2]))
            ->setLanguage(LanguageEnum::FRENCH)
        ;

        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);

        $player = new Player();
        $player->setCharacterConfig($characterConfig1)->setDaedalus($daedalus);

        $symptomConfig = new SymptomConfig(SymptomEnum::PARANOIA_MESSAGES);
        $symptomConfig->setTrigger(EventEnum::ON_NEW_MESSAGE);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));

        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $message = new Message();
        $message->setAuthor($player)->setMessage('Some message');

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();
        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->randomService->shouldReceive('isSuccessful')->andReturn(true)->once();

        $this->randomService->shouldReceive('random')->andReturn(1)->times(3);
        $this->randomService
            ->shouldReceive('getRandomElements')
            ->with([CharacterEnum::TERRENCE])
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

        $modifiedMessage = $this->service->applyDiseaseEffects($message);

        $this->assertEquals('modified message', $modifiedMessage->getMessage());
        $this->assertEquals([], $modifiedMessage->getTranslationParameters());
    }
}
