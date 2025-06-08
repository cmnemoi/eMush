<?php

namespace Mush\Daedalus\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectType;

#[ORM\Embeddable]
class DaedalusProjectsStatistics
{
    #[ORM\Column(type: 'array', nullable: false, options : ['default' => 'a:0:{}'])]
    private array $neronProjetsCompleted = [];
    #[ORM\Column(type: 'array', nullable: false, options : ['default' => 'a:0:{}'])]
    private array $researchProjetsCompleted = [];
    #[ORM\Column(type: 'array', nullable: false, options : ['default' => 'a:0:{}'])]
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
        return match ($project->getType()) {
            ProjectType::NERON_PROJECT => $this->addNeronProjectCompleted($project->getName()),
            ProjectType::RESEARCH => $this->addResearchProjectCompleted($project->getName()),
            ProjectType::PILGRED => $this->addPilgredProjectCompleted($project->getName()),
            default => throw new \LogicException('Unsupported project type to add completed project.')
        };
    }

    public function removeCompletedProject(Project $project): static
    {
        return match ($project->getType()) {
            ProjectType::NERON_PROJECT => $this->removeNeronProjectCompleted($project->getName()),
            ProjectType::RESEARCH => $this->removeResearchProjectCompleted($project->getName()),
            ProjectType::PILGRED => $this->removePilgredProjectCompleted($project->getName()),
            default => throw new \LogicException('Unsupported project type to remove completed project.')
        };
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

    private function removeNeronProjectCompleted(string $projectName): static
    {
        $key = array_search($projectName, $this->neronProjetsCompleted, true);
        if ($key !== false) {
            array_splice($this->neronProjetsCompleted, $key, 1);
        }

        return $this;
    }

    private function removeResearchProjectCompleted(string $projectName): static
    {
        $key = array_search($projectName, $this->researchProjetsCompleted, true);
        if ($key !== false) {
            array_splice($this->researchProjetsCompleted, $key, 1);
        }

        return $this;
    }

    private function removePilgredProjectCompleted(string $projectName): static
    {
        $key = array_search($projectName, $this->pilgredProjetsCompleted, true);
        if ($key !== false) {
            array_splice($this->pilgredProjetsCompleted, $key, 1);
        }

        return $this;
    }
}
