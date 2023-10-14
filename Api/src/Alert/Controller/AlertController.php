<?php

namespace Mush\Alert\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Controller\AbstractGameController;
use Mush\MetaGame\Service\AdminServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersController.
 *
 * @Route(path="/alert")
 */
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
     *
     * @OA\Tag (name="Alert")
     *
     * @Security (name="Bearer")
     *
     * @Rest\Get(path="/{id}/alerts", requirements={"id"="\d+"})
     */
    public function getDaedalusAlertsAction(Daedalus $daedalus): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        return $this->view($this->alertService->getAlerts($daedalus), 200);
    }
}
