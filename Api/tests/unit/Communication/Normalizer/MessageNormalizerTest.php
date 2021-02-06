<?php

namespace Mush\Test\Communication\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Communication\Entity\Message;
use Mush\Communication\Normalizer\MessageNormalizer;
use Mush\Daedalus\Entity\Neron;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageNormalizerTest extends TestCase
{
    /** @var TranslatorInterface | Mockery\Mock */
    private TranslatorInterface $translator;

    private MessageNormalizer $normalizer;

    /**
     * @before
     */
    public function before()
    {
        $this->translator = Mockery::mock(TranslatorInterface::class);

        $this->normalizer = new MessageNormalizer(
            $this->translator
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

        $this->translator->shouldReceive('trans')->andReturn('translatedName');

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
        ;

        $this->translator->shouldReceive('trans')->andReturn('translatedName');

        $normalizedData = $this->normalizer->normalize($message);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => CharacterEnum::NERON, 'value' => 'translatedName'],
            'message' => 'message',
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

        $this->translator->shouldReceive('trans')->andReturn('translatedName')->twice();

        $normalizedData = $this->normalizer->normalize($neronMessage);

        $this->assertEquals([
            'id' => null,
            'character' => ['key' => CharacterEnum::NERON, 'value' => 'translatedName'],
            'message' => 'message parent',
            'createdAt' => $createdAt->format(\DateTime::ATOM),
            'child' => [[
                'id' => null,
                'character' => ['key' => 'name', 'value' => 'translatedName'],
                'message' => 'message child',
                'createdAt' => $createdAt->format(\DateTime::ATOM),
                'child' => [],
            ]],
        ], $normalizedData);
    }
}
