<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\MetaGame\Command\MarkDaedalusAsCheaterCommand;
use Mush\MetaGame\Command\MarkDaedalusAsCheaterCommandHandler;
use Mush\MetaGame\Dto\CreateEquipmentForDaedalusDto;
use Mush\MetaGame\Dto\CreateEquipmentForDaedalusesDto;
use Mush\MetaGame\Dto\CreateProjectForDaedalusDto;
use Mush\MetaGame\Dto\CreateStatusForHolderOnDaedalusDto;
use Mush\Player\Entity\Player;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Service\FinishProjectService;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use Mush\Project\UseCase\ProposeNewNeronProjectsUseCase;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Service\StatusServiceInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @OA\Tag(name="Admin")
 */
#[Route('/admin/actions')]
final class AdminActionsController extends AbstractController
{
    public function __construct(
        private readonly CreateProjectFromConfigForDaedalusUseCase $createProjectFromConfigForDaedalusUseCase,
        private readonly DaedalusRepository $daedalusRepository,
        private readonly GameEquipmentServiceInterface $gameEquipmentService,
        private readonly MarkDaedalusAsCheaterCommandHandler $handler,
        private readonly ProposeNewNeronProjectsUseCase $proposeNewNeronProjectsUseCase,
        private readonly StatusServiceInterface $statusService,
        private readonly FinishProjectService $finishProjectService,
        private readonly PlayerDiseaseServiceInterface $playerDiseaseService,
        private readonly DeletePlayerSkillService $deletePlayerSkillService,
        private readonly PlayerRepositoryInterface $playerRepository
    ) {}

    /**
     * Create all projects for on-going Daedaluses.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create-all-projects-for-on-going-daedaluses', methods: ['POST'])]
    public function createAllProjectsForDaedalusesEndpoint(): JsonResponse
    {
        /** @var array<int, Daedalus> $onGoingDaedaluses */
        $onGoingDaedaluses = $this->daedalusRepository->findNonFinishedDaedaluses();

        foreach ($onGoingDaedaluses as $daedalus) {
            /** @var ProjectConfig $projectConfig */
            foreach ($daedalus->getProjectConfigs() as $projectConfig) {
                $this->createProjectFromConfigForDaedalusUseCase->execute($projectConfig, $daedalus);
            }
        }

        return $this->json(['detail' => 'Projects created successfully.'], Response::HTTP_CREATED);
    }

    /**
     * Create pieces of equipment for all on-going Daedaluses.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create-equipment-for-on-going-daedaluses', methods: ['POST'])]
    public function createEquipmentForDaedalusesEndpoint(Request $request): JsonResponse
    {
        $dto = new CreateEquipmentForDaedalusesDto(...$request->toArray());

        $onGoingDaedaluses = $this->daedalusRepository->findNonFinishedDaedaluses();
        $count = \count($onGoingDaedaluses);

        /** @var Daedalus $daedalus */
        foreach ($onGoingDaedaluses as $daedalus) {
            $this->gameEquipmentService->createGameEquipmentsFromName(
                equipmentName: $dto->equipmentName,
                equipmentHolder: $daedalus->getPlaceByNameOrThrow($dto->place),
                reasons: ['admin_action'],
                time: new \DateTime(),
                quantity: $dto->quantity,
            );
        }

        return $this->json(
            ['detail' => "{$dto->quantity} {$dto->equipmentName} created successfully in {$dto->place} for {$count} Daedaluses."],
            Response::HTTP_CREATED
        );
    }

    /**
     * Create pieces of equipment for a given Daedalus.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create-equipment-for-daedalus', methods: ['POST'])]
    public function createEquipmentForDaedalusEndpoint(Request $request): JsonResponse
    {
        $dto = new CreateEquipmentForDaedalusDto(...$request->toArray());
        $daedalus = $this->daedalusRepository->find($dto->daedalus);
        if ($daedalus === null) {
            return $this->json(['error' => 'Daedalus not found'], Response::HTTP_NOT_FOUND);
        }

        $this->gameEquipmentService->createGameEquipmentsFromName(
            equipmentName: $dto->equipmentName,
            equipmentHolder: $daedalus->getPlaceByNameOrThrow($dto->place),
            reasons: [],
            time: new \DateTime(),
            quantity: $dto->quantity,
        );

        return $this->json(
            ['detail' => "{$dto->quantity} {$dto->equipmentName} created successfully in {$dto->place} for Daedalus {$daedalus->getId()}."],
            Response::HTTP_CREATED
        );
    }

    /**
     * Create all players init statuses for on-going Daedaluses.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create-all-players-init-statuses', methods: ['POST'])]
    public function createAllPlayersInitStatusesEndpoint(): JsonResponse
    {
        $ongoingDaedaluses = $this->daedalusRepository->findNonFinishedDaedaluses();

        /** @var Daedalus $daedalus */
        foreach ($ongoingDaedaluses as $daedalus) {
            $players = $daedalus->getPlayers()->getPlayerAlive();

            /** @var Player $player */
            foreach ($players as $player) {
                /** @var StatusConfig $initStatus */
                foreach ($player->getCharacterConfig()->getInitStatuses() as $initStatus) {
                    $this->statusService->createStatusFromConfig(
                        statusConfig: $initStatus,
                        holder: $player,
                        tags: ['admin_action'],
                        time: new \DateTime(),
                    );
                }
            }
        }

        return $this->json(['detail' => 'All init statuses created successfully.'], Response::HTTP_CREATED);
    }

    /**
     * Delete all statuses with a given name.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete-all-statuses-by-name/{name}', requirements: ['name' => '^[a-zA-Z_]+$'], methods: ['DELETE'])]
    public function deleteAllStatusesByNameEndpoint(string $name): JsonResponse
    {
        $this->statusService->deleteAllStatusesByName($name);

        return $this->json(['detail' => "All statuses with name {$name} deleted successfully."], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create-status-for-holder-on-daedalus', methods: ['POST'])]
    public function createStatusForHolderOnDaedalusEndpoint(Request $request): JsonResponse
    {
        $dto = new CreateStatusForHolderOnDaedalusDto(...$request->toArray());
        $daedalus = $this->daedalusRepository->find($dto->daedalus);
        if ($daedalus === null) {
            return $this->json(['error' => 'Daedalus not found'], Response::HTTP_NOT_FOUND);
        }
        $holder = $daedalus->getStatusHolderByNameOrThrow($dto->holder);

        $this->statusService->createStatusFromName(
            $dto->statusName,
            $holder,
            [],
            new \DateTime()
        );

        return $this->json(['detail' => "Status with name {$dto->statusName} successfully created on {$dto->holder}"], Response::HTTP_OK);
    }

    /**
     * Delete all skills with a given name.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete-all-skills-by-name/{name}', requirements: ['name' => '^[a-zA-Z_]+$'], methods: ['DELETE'])]
    public function deleteAllSkillsByNameEndpoint(string $name): JsonResponse
    {
        $skill = SkillEnum::from($name);

        $players = $this->playerRepository->getAllAlive();
        foreach ($players as $player) {
            if ($player) {
                $this->deletePlayerSkillService->execute($skill, $player);
            }
        }

        return $this->json(['detail' => "All statuses with name {$name} deleted successfully."], Response::HTTP_OK);
    }

    /**
     * Propose new Neron projects for on-going Daedaluses.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/propose-new-neron-projects-for-on-going-daedaluses', methods: ['PUT'])]
    public function proposeNewNeronProjectsForDaedalusesEndpoint(): JsonResponse
    {
        /** @var Daedalus $daedalus */
        foreach ($this->daedalusRepository->findNonFinishedDaedaluses() as $daedalus) {
            $this->proposeNewNeronProjectsUseCase->execute($daedalus, $daedalus->getNumberOfProjectsByBatch());
        }

        return $this->json(['detail' => 'Neron projects proposed successfully.'], Response::HTTP_OK);
    }

    /**
     * Finish project for a given Daedalus.
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/finish-project-for-daedalus', methods: ['POST'])]
    public function finishProjectForDaedalusEndpoint(Request $request): JsonResponse
    {
        $dto = new CreateProjectForDaedalusDto(...$request->toArray());
        $daedalus = $this->daedalusRepository->find($dto->daedalus);
        if ($daedalus === null) {
            return $this->json(['error' => 'Daedalus not found'], Response::HTTP_NOT_FOUND);
        }
        $projectName = ProjectName::tryFrom($dto->projectName);
        if ($projectName === null) {
            return $this->json(['error' => "Unknown project '{$dto->projectName}'"], Response::HTTP_NOT_FOUND);
        }
        $project = $daedalus->getProjectByName($projectName);

        $this->finishProjectService->execute($project);

        return $this->json(
            ['detail' => "{$dto->projectName} created successfully for Daedalus {$daedalus->getId()}."],
            Response::HTTP_CREATED
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/mark-daedalus-as-cheater', methods: ['POST'])]
    public function markDaedalusAsCheaterEndpoint(#[MapRequestPayload] MarkDaedalusAsCheaterCommand $markDaedalusAsCheater): JsonResponse
    {
        $this->handler->execute($markDaedalusAsCheater);

        return $this->json(['detail' => "Closed daedalus {$markDaedalusAsCheater->closedDaedalusId} marked as cheater successfully."], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/kill-disease/{disease}', methods: ['POST'])]
    public function killDisease(PlayerDisease $disease): JsonResponse
    {
        $this->playerDiseaseService->removePlayerDisease($disease, ['ADMIN ACTION'], new \DateTime(), VisibilityEnum::HIDDEN);

        return $this->json(['detail' => 'Disease Removed successfully'], Response::HTTP_OK);
    }
}
