<?php

declare(strict_types=1);

namespace Mush\Chat\Controller;

use Mush\Chat\Entity\Channel;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Game\Controller\AbstractGameController;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\UseCase\GetUserCurrentPlayerUseCase;
use Mush\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/channel/tips')]
#[OA\Tag(name: 'Channel')]
final class TipsChannelController extends AbstractGameController
{
    public function __construct(
        protected AdminServiceInterface $adminService,
        private ChannelServiceInterface $channelService,
        private GetUserCurrentPlayerUseCase $getUserCurrentPlayerUseCase
    ) {
        parent::__construct($adminService);
    }

    /**
     * Get the tips channel.
     */
    #[Route(path: '', name: 'tips-channel', methods: ['GET'])]
    public function getTipsChannel(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new UnauthorizedHttpException('You should be logged in to access tips channel.');
        }

        $currentPlayer = $this->getUserCurrentPlayerUseCase->execute($user);

        return $this->json(Channel::createTipsChannelForPlayer($currentPlayer), Response::HTTP_OK, [], ['currentPlayer' => $currentPlayer]);
    }

    /**
     * Mark the tips channel as read.
     */
    #[Route(path: '/read', methods: ['PATCH'])]
    public function readTipsChannelEndpoint(): JsonResponse
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            throw new UnauthorizedHttpException('You should be logged in to access tips channel.');
        }

        $currentPlayer = $this->getUserCurrentPlayerUseCase->execute($user);

        $this->channelService->markTipsChannelAsReadForPlayer(Channel::createTipsChannelForPlayer($currentPlayer), $currentPlayer);

        return $this->json(['detail' => 'Tips channel marked as read successfully'], Response::HTTP_OK);
    }
}
