<?php

declare(strict_types=1);

namespace Mush\tests\unit\Player\Normalizer;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\PlayerNotification;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Factory\PlayerFactory;
use Mush\Player\Normalizer\PlayerNotificationNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerNotificationNormalizerTest extends TestCase
{
    private PlayerNotificationNormalizer $normalizer;
    private TranslationServiceInterface $translationService;

    protected function setUp(): void
    {
        $this->translationService = \Mockery::mock(TranslationServiceInterface::class);
        $this->normalizer = new PlayerNotificationNormalizer($this->translationService);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testShouldNormalizeNotificationWithImageParameter(): void
    {
        // given I have a player notification with image parameter
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
        $notification = new PlayerNotification(
            $player,
            PlayerNotificationEnum::WELCOME_MUSH,
            ['quantity' => 5, 'image' => 'mush_stamp.png']
        );

        $this->givenTranslationServiceReturnsTranslations();

        // when I normalize the notification
        $result = $this->normalizer->normalize($notification);

        // then the result should include the image
        self::assertEquals('mush_stamp.png', $result['image']);
        self::assertEquals('Title', $result['title']);
        self::assertEquals('Subtitle', $result['subTitle']);
        self::assertEquals('Description', $result['description']);
    }

    public function testShouldNormalizeNotificationWithoutImage(): void
    {
        // given I have a player notification without image
        $player = PlayerFactory::createPlayerWithDaedalus(DaedalusFactory::createDaedalus());
        $notification = new PlayerNotification(
            $player,
            PlayerNotificationEnum::MISSION_ACCEPTED,
            ['mission' => 'test_mission']
        );

        $this->givenTranslationServiceReturnsTranslations();

        // when I normalize the notification
        $result = $this->normalizer->normalize($notification);

        // then the result should have empty image
        self::assertEquals('', $result['image']);
    }

    private function givenTranslationServiceReturnsTranslations(): void
    {
        $this->translationService
            ->shouldReceive('translate')
            ->andReturnUsing(static fn ($key) => match (true) {
                str_contains($key, '.title') => 'Title',
                str_contains($key, '.subTitle') => 'Subtitle',
                str_contains($key, '.description') => 'Description',
                default => $key,
            });
    }
}
