<?php

namespace Mush\Test\Communication\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\DiseaseMessagesEnum;
use Mush\Communication\Normalizer\MessageNormalizer;
use Mush\Daedalus\Entity\Neron;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
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
        $this->translationService = Mockery::mock(TranslationServiceInterface::class);

        $this->normalizer = new MessageNormalizer(
            $this->translationService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testNormalizePlayerMessage()
    {
        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $player->setCharacterConfig($playerConfig);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($player)
            ->setMessage('message')
            ->setCreatedAt($createdAt)
        ;

        $this->translationService->shouldReceive('translate')->andReturn('translatedName');

        $context = ['currentPlayer' => new Player()];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => 'message',
            'createdAt' => $createdAt->format(\DateTime::ATOM),
            'child' => [],
        ], $normalizedData);
    }

    public function testNormalizeNeronMessage()
    {
        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $neron = new Neron();

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
            ->with('message', $message->getTranslationParameters(), 'neron')
            ->andReturn('translatedMessage')
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with(CharacterEnum::NERON . '.name', [], 'characters')
            ->andReturn('translatedName')
        ;

        $context = ['currentPlayer' => new Player()];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => CharacterEnum::NERON, 'value' => 'translatedName'],
            'message' => 'translatedMessage',
            'createdAt' => $createdAt->format(\DateTime::ATOM),
            'child' => [],
        ], $normalizedData);
    }

    public function testNormalizeNeronMessageWithChild()
    {
        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $neron = new Neron();

        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $player->setCharacterConfig($playerConfig);

        $createdAt = new \DateTime();

        $playerMessage = new Message();
        $playerMessage
            ->setAuthor($player)
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
            ->with(CharacterEnum::NERON . '.name', [], 'characters')
            ->andReturn('translatedName')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('name' . '.name', [], 'characters')
            ->andReturn('translated player name')
            ->once()
        ;
        $this->translationService
            ->shouldReceive('translate')
            ->with('message parent', [], 'neron')
            ->andReturn('translated message parent')
            ->once()
        ;

        $context = ['currentPlayer' => new Player()];
        $normalizedData = $this->normalizer->normalize($neronMessage, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => CharacterEnum::NERON, 'value' => 'translatedName'],
            'message' => 'translated message parent',
            'createdAt' => $createdAt->format(\DateTime::ATOM),
            'child' => [[
                'id' => null,
                'character' => ['key' => 'name', 'value' => 'translated player name'],
                'message' => 'message child',
                'createdAt' => $createdAt->format(\DateTime::ATOM),
                'child' => [],
            ]],
        ], $normalizedData);
    }

    public function testNormalizeDeafPlayerMessage()
    {
        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $player->setCharacterConfig($playerConfig);

        $symptomConfig = new SymptomConfig(SymptomEnum::DEAF);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($player)
            ->setMessage('message')
            ->setCreatedAt($createdAt)
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters')
            ->andReturn('translatedName')
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with(DiseaseMessagesEnum::DEAF, [], 'disease_message')
            ->andReturn('...')
        ;

        $context = ['currentPlayer' => $player];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => '...',
            'createdAt' => $createdAt->format(\DateTime::ATOM),
            'child' => [],
        ], $normalizedData);
    }

    public function testNormalizeParanoiacPlayerMessage()
    {
        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $player->setCharacterConfig($playerConfig);

        $otherPlayer = new Player();
        $otherPlayer->setCharacterConfig($playerConfig);

        $symptomConfig = new SymptomConfig(SymptomEnum::PARANOIA_MESSAGES);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($otherPlayer)
            ->setMessage('modified message')
            ->setCreatedAt($createdAt)
            ->setTranslationParameters([
                DiseaseMessagesEnum::MODIFICATION_CAUSE => SymptomEnum::PARANOIA_MESSAGES,
                DiseaseMessagesEnum::ORIGINAL_MESSAGE => 'original message',
            ])
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters')
            ->andReturn('translatedName')
        ;

        $context = ['currentPlayer' => $player];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => 'modified message',
            'createdAt' => $createdAt->format(\DateTime::ATOM),
            'child' => [],
        ], $normalizedData);
    }

    public function testNormalizeParanoiacPlayerMessageSelf()
    {
        $playerConfig = new CharacterConfig();
        $playerConfig->setName('name');

        $player = new Player();
        $player->setCharacterConfig($playerConfig);

        $symptomConfig = new SymptomConfig(SymptomEnum::PARANOIA_MESSAGES);
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setSymptomConfigs(new SymptomConfigCollection([$symptomConfig]));
        $playerDisease = new PlayerDisease();
        $playerDisease
            ->setDiseaseConfig($diseaseConfig)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
        ;

        $player->addMedicalCondition($playerDisease);

        $createdAt = new \DateTime();

        $message = new Message();
        $message
            ->setAuthor($player)
            ->setMessage('modified message')
            ->setCreatedAt($createdAt)
            ->setTranslationParameters([
                DiseaseMessagesEnum::MODIFICATION_CAUSE => SymptomEnum::PARANOIA_MESSAGES,
                DiseaseMessagesEnum::ORIGINAL_MESSAGE => 'original message',
            ])
        ;

        $this->translationService
            ->shouldReceive('translate')
            ->with('name.name', [], 'characters')
            ->andReturn('translatedName')
        ;

        $context = ['currentPlayer' => $player];
        $normalizedData = $this->normalizer->normalize($message, null, $context);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => 'name', 'value' => 'translatedName'],
            'message' => 'original message',
            'createdAt' => $createdAt->format(\DateTime::ATOM),
            'child' => [],
        ], $normalizedData);
    }
}
