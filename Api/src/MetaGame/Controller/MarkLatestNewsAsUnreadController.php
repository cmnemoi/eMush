<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\MetaGame\UseCase\MarkLatestNewsAsUnreadForAllUsersUseCase;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class for actions that can be performed by admins.
 *
 * @Route(path="/admin/actions/mark-latest-news-as-unread")
 */
final class MarkLatestNewsAsUnreadController extends AbstractFOSRestController
{
    public function __construct(
        private MarkLatestNewsAsUnreadForAllUsersUseCase $markLatestNewsAsUnreadForAllUsersUseCase
    ) {
        $this->markLatestNewsAsUnreadForAllUsersUseCase = $markLatestNewsAsUnreadForAllUsersUseCase;
    }

    /**
     * Mark latest news as unread for all users.
     *
     * @OA\Tag(name="Admin")
     *
     * @Security(name="Bearer")
     *
     * @IsGranted("ROLE_ADMIN")
     *
     * @Rest\Patch(path="")
     */
    public function markLatestNewsAsUnreadEndpoint(): View
    {
        $this->markLatestNewsAsUnreadForAllUsersUseCase->execute();

        return $this->view(['detail' => 'Latest news marked as unread for all users.'], Response::HTTP_OK);
    }
}
