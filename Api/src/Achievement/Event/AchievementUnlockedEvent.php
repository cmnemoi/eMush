<?php

declare(strict_types=1);

namespace Mush\Achievement\Event;

use Mush\Achievement\Entity\Achievement;
use Mush\Game\Event\AbstractGameEvent;

final class AchievementUnlockedEvent extends AbstractGameEvent
{
    public function __construct(
        private Achievement $achievement,
        private int $userId,
        private string $language,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
    }

    public function getAchievement(): Achievement
    {
        return $this->achievement;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTranslationParameters(): array
    {
        return [
            'achievement' => $this->achievement->getName()->value,
            'gender' => 'other',
        ];
    }
}
