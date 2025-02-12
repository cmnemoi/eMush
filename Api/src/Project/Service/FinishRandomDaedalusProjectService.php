<?php

declare(strict_types=1);

namespace Mush\Project\Service;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Project\Entity\Project;
use Mush\Project\Event\ProjectEvent;
use Mush\Project\Repository\ProjectRepositoryInterface;

final readonly class FinishRandomDaedalusProjectService
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
        private EventServiceInterface $eventService,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
        private ProjectRepositoryInterface $projectRepository,
    ) {}

    public function execute(int $daedalusId): void
    {
        $project = $this->getRandomProjectFromDaedalus($daedalusId);
        $project->finish();
        $this->projectRepository->save($project);

        $this->eventService->callEvent(
            event: new ProjectEvent($project, author: Player::createNull(), tags: [ActionEnum::UPGRADE_NERON->toString()]),
            name: ProjectEvent::PROJECT_FINISHED
        );
    }

    private function getRandomProjectFromDaedalus(int $daedalusId): Project
    {
        $daedalus = $this->daedalusRepository->findByIdOrThrow($daedalusId);

        /** @var Project $project */
        return $this->getRandomElementsFromArray->execute(
            elements: $daedalus->getAvailableNeronProjects()->toArray(),
            number: 1
        )->first();
    }
}
