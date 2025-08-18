<?php

declare(strict_types=1);

namespace Mush\Chat\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\View\View;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Services\ChannelServiceInterface;
use Mush\Game\Controller\AbstractGameController;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\UseCase\GetUserCurrentPlayerUseCase;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
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
    #[Get(path: '', name: 'tips-channel')]
    #[Security(name: 'Bearer')]
    public function getTipsChannel(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        $user = $this->getUser();
        if (!$user) {
            throw new UnauthorizedHttpException('You should be logged in to access tips channel.');
        }

        $currentPlayer = $this->getUserCurrentPlayerUseCase->execute($user);

        $context = new Context();
        $context->setAttribute('currentPlayer', $currentPlayer);

        $view = $this->view(Channel::createTipsChannelForPlayer($currentPlayer), Response::HTTP_OK);
        $view->setContext($context);

        return $view;
    }

    /**
     * Mark the tips channel as read.
     */
    #[Patch(path: '/read')]
    #[Security(name: 'Bearer')]
    public function readTipsChannelEndpoint(): View
    {
        if ($maintenanceView = $this->denyAccessIfGameInMaintenance()) {
            return $maintenanceView;
        }

        $user = $this->getUser();
        if (!$user) {
            throw new UnauthorizedHttpException('You should be logged in to access tips channel.');
        }

        $currentPlayer = $this->getUserCurrentPlayerUseCase->execute($user);

        $this->channelService->markTipsChannelAsReadForPlayer(Channel::createTipsChannelForPlayer($currentPlayer), $currentPlayer);

        return $this->view(['detail' => 'Tips channel marked as read successfully'], Response::HTTP_OK);
    }
}
