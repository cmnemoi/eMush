<?php

declare(strict_types=1);

namespace Mush\Project\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusProjectsStatistics;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerHighlightSourceEventInterface;
use Mush\Player\ValueObject\PlayerHighlight;
use Mush\Player\ValueObject\PlayerHighlightTargetInterface;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectRequirement;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

final class ProjectEvent extends AbstractGameEvent implements TriumphSourceEventInterface, PlayerHighlightSourceEventInterface
{
    use TriumphSourceEventTrait;

    public const string NEXT_20_PERCENTS = 'next_20_percents';
    public const string PROJECT_ADVANCED = 'project.advanced';
    public const string PROJECT_FINISHED = 'project.finished';

    private Project $project;

    public function __construct(
        Project $project,
        Player $author,
        array $tags = [],
        \DateTime $time = new \DateTime(),
    ) {
        parent::__construct($tags, $time);
        $this->author = $author;
        $this->project = $project;
        $this->addTags([
            $project->getName(),
            $project->getType()->toString(),
            ...$project->getRequirements()->map(static fn (ProjectRequirement $requirement) => $requirement->getName())->toArray(),
        ]);

        if ($project->hasCrossedProgressStepForThreshold(20)) {
            $this->addTag(self::NEXT_20_PERCENTS);
        }
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function getAuthor(): Player
    {
        return $this->author ?? Player::createNull();
    }

    public function getDaedalus(): Daedalus
    {
        return $this->project->getDaedalus();
    }

    public function getDaedalusProjectsStatistics(): DaedalusProjectsStatistics
    {
        return $this->getDaedalus()->getDaedalusInfo()->getDaedalusProjectsStatistics();
    }

    public function isNotAboutFinishedProject(): bool
    {
        return !$this->project->isFinished();
    }

    public function isNotAboutNeronProject(): bool
    {
        return !$this->project->isNeronProject();
    }

    public function toArray(): array
    {
        return [
            'project' => $this->project,
            'author' => $this->author,
            'tags' => $this->tags,
            'time' => $this->time,
        ];
    }

    public function shouldPrintResearchCompletedLog(): bool
    {
        return $this->project->isResearchProject() && $this->project->isFinished();
    }

    public function getHighlightName(): string
    {
        return $this->getEventName() . '_' . $this->getProject()->getType()->toString();
    }

    public function getHighlightResult(): string
    {
        return PlayerHighlight::SUCCESS;
    }

    public function getHighlightTarget(): PlayerHighlightTargetInterface
    {
        return $this->getProject();
    }

    public function hasHighlightTarget(): bool
    {
        return true;
    }

    public function getLanguage(): string
    {
        return $this->getDaedalus()->getLanguage();
    }
}
