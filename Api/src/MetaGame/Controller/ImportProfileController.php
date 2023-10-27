<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\MetaGame\Enum\TwinoidURLEnum;
use Mush\MetaGame\Service\ImportProfileService;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ImportProfileController.
 *
 * @Route("/import")
 */
class ImportProfileController extends AbstractFOSRestController
{
    public function __construct(
        private ImportProfileService $importProfileService
    ) {
    }

    /**
     * @OA\Parameter(
     *      name="serverLanguage",
     *      in="path",
     *      description="server language to recover data from (fr or en)",
     *
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *      name="sid",
     *      in="path",
     *      description="sid for session",
     *
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Parameter(
     *     name="code",
     *     in="path",
     *     description="code for session",
     *
     *    @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="Import")
     *
     * @Rest\Post(path="/{serverLanguage}/{sid}/{code}")
     *
     * @Rest\View()
     */
    public function legacyUser(string $serverLanguage, string $sid, string $code): View
    {
        $legacyUser = $this->importProfileService->getLegacyUser(TwinoidURLEnum::getMushServerFromLanguage($serverLanguage), $sid, $code);

        return $this->view($legacyUser, Response::HTTP_OK);
    }
}
