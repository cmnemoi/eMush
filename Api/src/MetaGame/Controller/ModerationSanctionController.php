<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\MetaGame\Repository\ModerationSanctionRepository;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    /**
     * Get player reports.
     *
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="The player info id",
     *
     *       @OA\Schema(type="string")
     * )
     *
     * @OA\Tag(name="moderationSanction")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="/{id}/reports")
     *
     * @Rest\View()
     *
     * @OA\Tag(name="Moderation")
     *
     * @Security(name="Bearer")
     */
    public function getPlayerActiveReports(PlayerInfo $player): View
    {
        $moderator = $this->getUser();
        if (!$moderator instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Request author user not found');
        }
        if (!$moderator->isModerator()) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Only moderators can use this endpoint!');
        }

        $reports = $this->moderationSanctionRepository->findAllPlayerReports($player);

        return $this->view($reports, Response::HTTP_OK);
    }
}
