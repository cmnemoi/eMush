<?php

declare(strict_types=1);

namespace Mush\Communication\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Communication\Entity\Channel;
use Mush\Game\Controller\AbstractGameController;
use Mush\MetaGame\Service\AdminServiceInterface;
use Mush\Player\UseCase\GetUserCurrentPlayerUseCase;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/channel/tips")
 */
final class TipsChannelController extends AbstractGameController
{
    public function __construct(
        protected AdminServiceInterface $adminService,
        private GetUserCurrentPlayerUseCase $getUserCurrentPlayerUseCase
    ) {
        parent::__construct($adminService);
    }

    /**
     * Get the tips channel.
     *
     * @OA\Tag(name="Channel")
     *
     * @Security(name="Bearer")
     *
     * @Rest\Get(path="")
     */
    public function getTipsChannel(): View
    {
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
}
