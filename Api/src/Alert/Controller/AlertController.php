<?php

declare(strict_types=1);

namespace Mush\Alert\Controller;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Controller\AbstractGameController;
use Mush\MetaGame\Service\AdminServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/alert')]
class AlertController extends AbstractGameController
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AdminServiceInterface $adminService,
        AlertServiceInterface $alertService,
    ) {
        parent::__construct($adminService);
        $this->alertService = $alertService;
    }

    /**
     * Display daedalus alerts.
     */
    #[Route('/{id}/alerts', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getDaedalusAlertsAction(Daedalus $daedalus): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        return $this->json($this->alertService->getAlerts($daedalus), Response::HTTP_OK);
    }
}
