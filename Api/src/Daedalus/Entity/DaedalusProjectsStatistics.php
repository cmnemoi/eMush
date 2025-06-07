<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectType;

#[ORM\Embeddable]
class DaedalusProjectsStatistics
{
    #[ORM\Column(type: 'array', nullable: false, options : ['default' => '[]'])]
    private array $neronProjetsCompleted = [];
    #[ORM\Column(type: 'array', nullable: false, options : ['default' => '[]'])]
    private array $researchProjetsCompleted = [];
    #[ORM\Column(type: 'array', nullable: false, options : ['default' => '[]'])]
    private array $pilgredProjetsCompleted = [];

    public function getNeronProjectsCompleted(): array
    {
        return $this->neronProjetsCompleted;
    }

    public function getResearchProjetsCompleted(): array
    {
        return $this->researchProjetsCompleted;
    }

    public function getPilgredProjetsCompleted(): array
    {
        return $this->pilgredProjetsCompleted;
    }

    public function addCompletedProject(Project $project): static
    {
        if ($project->getType() === ProjectType::NERON_PROJECT) {
            $this->addNeronProjectCompleted($project->getName());
        } elseif ($project->getType() === ProjectType::RESEARCH) {
            $this->addResearchProjectCompleted($project->getName());
        } elseif ($project->getType() === ProjectType::PILGRED) {
            $this->addPilgredProjectCompleted($project->getName());
        }

        return $this;
    }

    public function toArray(): array
    {
        return [
            'neronProjects' => $this->neronProjetsCompleted,
            'researchProjects' => $this->researchProjetsCompleted,
            'pilgredProjects' => $this->pilgredProjetsCompleted,
        ];
    }

    private function addNeronProjectCompleted(string $projectName): static
    {
        if (\in_array($projectName, $this->neronProjetsCompleted, true) === false) {
            $this->neronProjetsCompleted[] = $projectName;
        }

        return $this;
    }

    private function addResearchProjectCompleted(string $projectName): static
    {
        if (\in_array($projectName, $this->researchProjetsCompleted, true) === false) {
            $this->researchProjetsCompleted[] = $projectName;
        }

        return $this;
    }

    private function addPilgredProjectCompleted(string $projectName): static
    {
        if (\in_array($projectName, $this->pilgredProjetsCompleted, true) === false) {
            $this->pilgredProjetsCompleted[] = $projectName;
        }

        return $this;
    }
}
