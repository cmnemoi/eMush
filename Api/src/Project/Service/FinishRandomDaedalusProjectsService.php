<?php

declare(strict_types=1);

namespace Mush\Project\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Repository\ProjectRepositoryInterface;

final readonly class FinishRandomDaedalusProjectsService
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private EventServiceInterface $eventService,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
        private ProjectRepositoryInterface $projectRepository,
    ) {}

    public function execute(int $daedalusId, int $quantity = 1): void
    {
        $projects = $this->getRandomProjectsFromDaedalus($daedalusId, $quantity);

        foreach ($projects as $project) {
            $project->finish();
            $this->projectRepository->save($project);

            $this->eventService->callEvent(
                event: new ProjectEvent($project, author: Player::createNull()),
                name: ProjectEvent::PROJECT_FINISHED
            );
        }
    }

    private function getRandomProjectsFromDaedalus(int $daedalusId, int $quantity): ArrayCollection
    {
        $daedalus = $this->daedalusRepository->findByIdOrThrow($daedalusId);

        $projects = $this->getRandomElementsFromArray->execute(
            elements: $daedalus->getAvailableNeronProjects()->toArray(),
            number: $quantity
        );

        if ($projects->isEmpty()) {
            throw new \RuntimeException('There should be at least one NERON project available');
        }

        return $projects;
    }
}
