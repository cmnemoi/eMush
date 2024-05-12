<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Repository\DaedalusRepository;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\MetaGame\Dto\CreateEquipmentForDaedalusesDto;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\UseCase\CreateProjectFromConfigForDaedalusUseCase;
use Mush\Project\UseCase\ProposeNewNeronProjectsUseCase;
use Mush\Status\Service\StatusServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class for actions that can be performed by admins.
 *
 * @Route(path="/admin/actions")
 */
final class AdminActionsController extends AbstractFOSRestController
{
    public function __construct(
        private readonly CreateProjectFromConfigForDaedalusUseCase $createProjectFromConfigForDaedalusUseCase,
        private readonly DaedalusRepository $daedalusRepository,
        private readonly GameEquipmentServiceInterface $gameEquipmentService,
        private readonly ProposeNewNeronProjectsUseCase $proposeNewNeronProjectsUseCase,
        private readonly StatusServiceInterface $statusService,
    ) {}

    /**
     * Create all projects for on-going Daedaluses.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Rest\Post(path="/create-all-projects-for-on-going-daedaluses")
     */
    public function createAllProjectsForDaedalusesEndpoint(): View
    {
        /** @var array<int, Daedalus> $onGoingDaedaluses */
        $onGoingDaedaluses = $this->daedalusRepository->findNonFinishedDaedaluses();

        foreach ($onGoingDaedaluses as $daedalus) {
            /** @var ProjectConfig $projectConfig */
            foreach ($daedalus->getProjectConfigs() as $projectConfig) {
                $this->createProjectFromConfigForDaedalusUseCase->execute($projectConfig, $daedalus);
            }
        }

        return $this->view(['detail' => 'Projects created successfully.'], Response::HTTP_CREATED);
    }

    /**
     * Create pieces of equipment for all on-going Daedaluses.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Rest\Post(path="/create-equipment-for-on-going-daedaluses")
     */
    public function createEquipmentForDaedalusesEndpoint(Request $request): View
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

        return $this->view(
            ['detail' => "{$dto->quantity} {$dto->equipmentName} created successfully in {$dto->place} for {$count} Daedaluses."],
            Response::HTTP_CREATED
        );
    }

    /**
     * Delete all statuses with a given name.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Rest\Delete(path="/delete-all-statuses-by-name/{name}", requirements={"name"="^[a-zA-Z_]+$"})
     */
    public function deleteAllStatusesByNameEndpoint(string $name): View
    {
        $this->statusService->deleteAllStatusesByName($name);

        return $this->view(['detail' => "All statuses with name {$name} deleted successfully."], Response::HTTP_OK);
    }

    /**
     * Propose new Neron projects for on-going Daedaluses.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Rest\Put(path="/propose-new-neron-projects-for-on-going-daedaluses")
     */
    public function proposeNewNeronProjectsForDaedalusesEndpoint(): View
    {
        /** @var Daedalus $daedalus */
        foreach ($this->daedalusRepository->findNonFinishedDaedaluses() as $daedalus) {
            $this->proposeNewNeronProjectsUseCase->execute($daedalus, $daedalus->getNumberOfProjectsByBatch());
        }

        return $this->view(['detail' => 'Neron projects proposed successfully.'], Response::HTTP_OK);
    }
}
