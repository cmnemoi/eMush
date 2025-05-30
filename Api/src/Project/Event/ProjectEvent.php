<?php

declare(strict_types=1);

namespace Mush\Project\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectRequirement;
use Mush\Triumph\Event\TriumphSourceEventInterface;
use Mush\Triumph\Event\TriumphSourceEventTrait;

final class ProjectEvent extends AbstractGameEvent implements TriumphSourceEventInterface
{
    use TriumphSourceEventTrait;

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
            ...$project->getRequirements()->map(static fn (ProjectRequirement $requirement) => $requirement->getName())->toArray(),
        ]);
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
}
