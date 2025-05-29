<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Triumph\Event;

use Mush\Tests\unit\Triumph\TestDoubles\TriumphSourceEventTraitTestClass;
use Mush\Triumph\Dto\TriumphConfigDto;
use Mush\Triumph\Entity\TriumphConfig;
use Mush\Triumph\Enum\TriumphEnum;
use Mush\Triumph\Enum\TriumphScope;
use Mush\Triumph\Enum\TriumphVisibility;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TriumphSourceEventTraitTest extends TestCase
{
    public function testShouldReturnTrueWhenNoConstraints(): void
    {
        $event = new TriumphSourceEventTraitTestClass(['foo']);
        $config = $this->givenTriumphConfig([]);
        self::assertTrue($event->hasExpectedTagsFor($config));
    }

    public function testShouldReturnTrueWhenAllTagsPresent(): void
    {
        $event = new TriumphSourceEventTraitTestClass(['foo', 'bar']);
        $config = $this->givenTriumphConfig(['foo' => 'all_tags', 'bar' => 'all_tags']);
        self::assertTrue($event->hasExpectedTagsFor($config));
    }

    public function testShouldReturnFalseWhenAnyAllTagMissing(): void
    {
        $event = new TriumphSourceEventTraitTestClass(['foo']);
        $config = $this->givenTriumphConfig(['foo' => 'all_tags', 'bar' => 'all_tags']);
        self::assertFalse($event->hasExpectedTagsFor($config));
    }

    public function testShouldReturnTrueWhenAnyTagAndOnePresent(): void
    {
        $event = new TriumphSourceEventTraitTestClass(['foo']);
        $config = $this->givenTriumphConfig(['foo' => 'any_tag', 'bar' => 'any_tag']);
        self::assertTrue($event->hasExpectedTagsFor($config));
    }

    public function testShouldReturnFalseWhenAnyTagAndNonePresent(): void
    {
        $event = new TriumphSourceEventTraitTestClass([]);
        $config = $this->givenTriumphConfig(['foo' => 'any_tag', 'bar' => 'any_tag']);
        self::assertFalse($event->hasExpectedTagsFor($config));
    }

    public function testShouldThrowOnInvalidConstraint(): void
    {
        $event = new TriumphSourceEventTraitTestClass(['foo']);
        $config = $this->givenTriumphConfig(['foo' => 'invalid']);
        $this->expectException(\LogicException::class);
        $event->hasExpectedTagsFor($config);
    }

    private function givenTriumphConfig(array $tagConstraints): TriumphConfig
    {
        $dto = new TriumphConfigDto(
            key: 'test',
            name: TriumphEnum::NONE,
            scope: TriumphScope::NONE,
            targetedEvent: '',
            quantity: 1,
            tagConstraints: $tagConstraints,
            visibility: TriumphVisibility::NONE,
            target: '',
            regressiveFactor: 0
        );

        return TriumphConfig::fromDto($dto);
    }
}
