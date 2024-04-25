<?php

declare(strict_types=1);

namespace Mush\Project\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;

final class ProjectEvent extends AbstractGameEvent
{
    public const string PROJECT_ADVANCED = 'project.advanced';

    private Project $project;

    public function __construct(
        Project $project,
        ?Player $author = null,
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
}
