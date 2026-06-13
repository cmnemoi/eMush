<?php

declare(strict_types=1);

namespace Mush\Player\ParamConverter;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\User\Service\UserServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PlayerCreateRequestConverter implements ValueResolverInterface
{
    public function __construct(
        private DaedalusServiceInterface $daedalusService,
        private UserServiceInterface $userService
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== PlayerCreateRequest::class) {
            return [];
        }

        $daedalus = null;
        $user = null;
        $payload = $request->getPayload();
        $character = $payload->get('character');

        if (($daedalusId = $payload->get('daedalus')) !== null) {
            $daedalus = $this->daedalusService->findById((int) $daedalusId);
        }

        if (($userId = $payload->get('user')) !== null) {
            $user = $this->userService->findUserByUserId((string) $userId);
        }

        $playerRequest = new PlayerCreateRequest();
        $playerRequest
            ->setCharacter((string) $character)
            ->setDaedalus($daedalus)
            ->setUser($user);

        return [$playerRequest];
    }
}
