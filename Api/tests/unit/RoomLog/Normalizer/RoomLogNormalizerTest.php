<?php

namespace Mush\Tests\unit\RoomLog\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\DateProviderInterface;
use Mush\Game\Service\FixedDateProvider;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\Collection\RoomLogCollection;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Normalizer\RoomLogNormalizer;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Tests\unit\RoomLog\TestDoubles\TranslationService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class RoomLogNormalizerTest extends TestCase
{
    private RoomLogNormalizer $normalizer;
    private TranslationService $translationService;
    private DateProviderInterface $dateProvider;
    private Player $player;
    private \DateTime $currentDate;
    private DaedalusInfo $daedalusInfo;
    private Place $place;

    /**
     * @before
     */
    public function before(): void
    {
        $this->translationService = new TranslationService();
        $this->currentDate = new \DateTime('2024-12-28T10:56:24+01:00');
        $this->dateProvider = new FixedDateProvider($this->currentDate);
        $this->normalizer = new RoomLogNormalizer($this->dateProvider, $this->translationService);

        $gameConfig = new GameConfig();
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setLanguage(LanguageEnum::FRENCH);

        $daedalus = new Daedalus();
        $this->daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        $this->place = new Place();
        $this->place->setDaedalus($daedalus);
        $this->place->setName('bridge');

        $this->player = new Player();
        $this->player->setPlace($this->place)->setDaedalus($daedalus);
    }

    public function testShouldTranslateLogMessage(): void
    {
        $roomLog = $this->givenARoomLog('logKey1');
        $this->givenTranslationExists('logKey1', ['is_tracker' => 'false'], 'translated log 1', 'log');

        $result = $this->whenNormalizingRoomLog($roomLog);

        $this->thenTheLogMessageShouldBe('translated log 1', $result);
    }

    public function testShouldTranslateLogMessageWithParameters(): void
    {
        $roomLog = $this->givenARoomLog('logKey2', ['player' => 'andie']);
        $this->givenTranslationExists('logKey2', ['player' => 'andie', 'is_tracker' => 'false'], 'translated log 2', 'log');

        $result = $this->whenNormalizingRoomLog($roomLog);

        $this->thenTheLogMessageShouldBe('translated log 2', $result);
    }

    public function testShouldShowDaysSinceLogCreation(): void
    {
        $roomLog = $this->givenARoomLogCreatedAt(new \DateTime('2024-12-26T10:56:24+01:00'));
        $this->givenTranslationExists('message_date.more_day', ['quantity' => 2], '2 days ago', 'chat');

        $result = $this->whenNormalizingRoomLog($roomLog);

        $this->thenTheLogDateShouldBe('2 days ago', $result);
    }

    public function testShouldShowHoursSinceLogCreation(): void
    {
        $roomLog = $this->givenARoomLogCreatedAt(new \DateTime('2024-12-28T06:56:24+01:00'));
        $this->givenTranslationExists('message_date.more_hour', ['quantity' => 4], '4 hours ago', 'chat');

        $result = $this->whenNormalizingRoomLog($roomLog);

        $this->thenTheLogDateShouldBe('4 hours ago', $result);
    }

    public function testShouldShowMinutesSinceLogCreation(): void
    {
        $roomLog = $this->givenARoomLogCreatedAt(new \DateTime('2024-12-28T10:50:24+01:00'));
        $this->givenTranslationExists('message_date.more_minute', ['quantity' => 6], '6 minutes ago', 'chat');

        $result = $this->whenNormalizingRoomLog($roomLog);

        $this->thenTheLogDateShouldBe('6 minutes ago', $result);
    }

    public function testShouldShowLessThanOneMinuteSinceLogCreation(): void
    {
        $roomLog = $this->givenARoomLogCreatedAt(new \DateTime('2024-12-28T10:56:05+01:00'));
        $this->givenTranslationExists('message_date.less_minute', [], 'less than a minute ago', 'chat');

        $result = $this->whenNormalizingRoomLog($roomLog);

        $this->thenTheLogDateShouldBe('less than a minute ago', $result);
    }

    public function testShouldMarkLogAsUnreadByDefault(): void
    {
        $roomLog = $this->givenARoomLog('logKey1');

        $result = $this->whenNormalizingRoomLog($roomLog);

        $this->thenTheLogShouldBeUnread($result);
    }

    public function testShouldMarkLogAsReadWhenPlayerIsReader(): void
    {
        $roomLog = $this->givenARoomLog('logKey1');
        $this->givenPlayerIsReader($roomLog);

        $result = $this->whenNormalizingRoomLog($roomLog);

        $this->thenTheLogShouldBeRead($result);
    }

    public function testShouldNotNormalizeDateDayAndCycleWhenPlayerPlaceIsDelogged(): void
    {
        $roomLog = $this->givenARoomLogCreatedAt(new \DateTime('2024-12-26T10:56:24+01:00'));
        $this->givenPlayerPlaceHasStatus(PlaceStatusEnum::DELOGGED->value);

        $result = $this->whenNormalizingRoomLog($roomLog);
        $logs = $this->whenNormalizingRoomLogCollection(new RoomLogCollection([$roomLog]));

        $this->thenTheLogDateShouldNotBeNormalized($result);
        $this->thenTheLogDayShouldNotBeNormalized($result);
        $this->thenTheLogCycleShouldNotBeNormalized($result);
        $this->thenTheLogsArrayShouldBeIndexedWithUnknownDayAndCycle($logs);
    }

    public function testShouldNotNormalizeDayAndCycleWhenPlayerPlaceIsNotDelogged(): void
    {
        $roomLog = $this->givenARoomLogCreatedAt(new \DateTime('2024-12-26T10:56:24+01:00'));

        $result = $this->whenNormalizingRoomLog($roomLog);

        $this->thenTheLogDayShouldNotBeNormalized($result);
        $this->thenTheLogCycleShouldNotBeNormalized($result);
    }

    private function givenARoomLog(string $logKey, array $parameters = []): RoomLog
    {
        return $this->givenARoomLogCreatedAt($this->currentDate, $logKey, $parameters);
    }

    private function givenARoomLogCreatedAt(\DateTime $createdAt, string $logKey = 'logKey1', array $parameters = []): RoomLog
    {
        $roomLog = new RoomLog();
        $this->setPrivateProperty($roomLog, 'id', 1);
        $roomLog->setLog($logKey)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setCreatedAt($createdAt)
            ->setDay(1)
            ->setCycle(3)
            ->setType('log')
            ->setPlace('bridge')
            ->setDaedalusInfo($this->daedalusInfo);

        if ($parameters) {
            $roomLog->setParameters($parameters);
        }

        return $roomLog;
    }

    private function givenTranslationExists(string $key, array $parameters, string $translation, string $domain): void
    {
        $this->translationService->setTranslation(
            $key,
            $parameters,
            $domain,
            LanguageEnum::FRENCH,
            $translation
        );
    }

    private function givenPlayerIsReader(RoomLog $roomLog): void
    {
        $roomLog->addReader($this->player);
    }

    private function givenPlayerPlaceHasStatus(string $statusName): void
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setStatusName($statusName);

        $status = new Status($this->place, $statusConfig);
        $this->place->addStatus($status);
    }

    private function whenNormalizingRoomLog(RoomLog $roomLog): array
    {
        $logs = $this->whenNormalizingRoomLogCollection(new RoomLogCollection([$roomLog]));

        if ($this->player->getPlace()->hasStatus(PlaceStatusEnum::DELOGGED->value)) {
            return $logs['?']['?'][0];
        }

        return $logs[$roomLog->getDay()][$roomLog->getCycle()][0];
    }

    private function whenNormalizingRoomLogCollection(RoomLogCollection $collection): array
    {
        return $this->normalizer->normalize($collection, null, ['currentPlayer' => $this->player]);
    }

    private function thenTheLogMessageShouldBe(string $expected, array $log): void
    {
        self::assertEquals($expected, $log['log']);
    }

    private function thenTheLogDateShouldBe(string $expected, array $log): void
    {
        self::assertEquals($expected, $log['date']);
    }

    private function thenTheLogDateShouldNotBeNormalized(array $log): void
    {
        self::assertArrayNotHasKey('date', $log);
    }

    private function thenTheLogDayShouldBeUnknown(array $log): void
    {
        self::assertEquals('?', $log['day']);
    }

    private function thenTheLogCycleShouldBeUnknown(array $log): void
    {
        self::assertEquals('?', $log['cycle']);
    }

    private function thenTheLogsArrayShouldBeIndexedWithUnknownDayAndCycle(array $logs): void
    {
        self::assertArrayHasKey('?', $logs);
        self::assertArrayHasKey('?', $logs['?']);
        self::assertCount(1, $logs['?']['?']);
    }

    private function thenTheLogShouldBeUnread(array $log): void
    {
        self::assertTrue($log['isUnread']);
    }

    private function thenTheLogShouldBeRead(array $log): void
    {
        self::assertFalse($log['isUnread']);
    }

    private function thenTheLogDayShouldNotBeNormalized(array $log): void
    {
        self::assertArrayNotHasKey('day', $log);
    }

    private function thenTheLogCycleShouldNotBeNormalized(array $log): void
    {
        self::assertArrayNotHasKey('cycle', $log);
    }

    private function setPrivateProperty(object $object, string $propertyName, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setValue($object, $value);
    }
}
