<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\MetaGame\Repository\ModerationSanctionRepository;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ModerationSanctionController.
 *
 * @Route(path="/moderation_sanctions")
 */
final class ModerationSanctionController extends AbstractFOSRestController
{
    public function __construct(
        private ModerationSanctionRepository $moderationSanctionRepository
    ) {}

    /**
     * Get user active sanctions.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The user id",
     *
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="moderationSanction")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/{id}/active-bans-and-warnings")
     *
     * @Rest\View()
     *
     * @IsGranted("IS_REQUEST_USER", subject="user", message="You cannot access other player's sanctions!")
     */
    public function getUserActiveBansAndWarnings(User $user): View
    {
        $warnings = $this->moderationSanctionRepository->findAllUserActiveBansAndWarnings($user);

        return $this->view($warnings, Response::HTTP_OK);
    }
}
