<?php

declare(strict_types=1);

namespace Mush\Project\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;

final class ProjectEvent extends AbstractGameEvent
{
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
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     */
    public function getAuthor(): Player
    {
        return $this->author;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->project->getDaedalus();
    }

    public function isNotAboutFinishedProject(): bool
    {
        return $this->project->isFinished() === false;
    }

    public function isNotAboutNeronProject(): bool
    {
        return $this->project->isNeronProject() === false;
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
}
