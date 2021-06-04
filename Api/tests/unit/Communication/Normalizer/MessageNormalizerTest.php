<?php

namespace Mush\Test\Communication\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Communication\Entity\Message;
use Mush\Communication\Normalizer\MessageNormalizer;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use PHPUnit\Framework\TestCase;

class MessageNormalizerTest extends TestCase
{
    /** @var TranslationServiceInterface | Mockery\Mock */
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

        $normalizedData = $this->normalizer->normalize($message);

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

        $normalizedData = $this->normalizer->normalize($message);

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

        $normalizedData = $this->normalizer->normalize($neronMessage);

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
}
