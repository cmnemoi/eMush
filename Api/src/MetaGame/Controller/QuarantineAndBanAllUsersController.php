<?php

declare(strict_types=1);

namespace Mush\MetaGame\Controller;

use Mush\MetaGame\Command\QuarantineAndBanAllUsersCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/moderation/ban-all-users', methods: ['POST'])]
#[IsGranted('ROLE_MODERATOR')]
final class QuarantineAndBanAllUsersController extends AbstractController
{
    public function __construct(private MessageBusInterface $commandBus) {}

    public function __invoke(#[MapRequestPayload] QuarantineAndBanAllUsersCommand $command): JsonResponse
    {
        $this->commandBus->dispatch($command);

        return new JsonResponse(
            ['detail' => 'Users banned and quarantined successfully.'],
            Response::HTTP_OK
        );
    }
}
