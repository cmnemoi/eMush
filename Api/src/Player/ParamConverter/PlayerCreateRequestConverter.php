<?php

namespace Mush\Player\ParamConverter;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\User\Service\UserServiceInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AutoconfigureTag('controller.argument_value_resolver', ['priority' => 150])]
final class PlayerCreateRequestConverter implements ValueResolverInterface
{
    private DaedalusServiceInterface $daedalusService;
    private UserServiceInterface $userService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        UserServiceInterface $userService
    ) {
        $this->daedalusService = $daedalusService;
        $this->userService = $userService;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($this->supports($argument)) {
            return $this->apply($request);
        }

        return [];
    }

    public function supports(ArgumentMetadata $argument): bool
    {
        return PlayerCreateRequest::class === $argument->getType();
    }

    /**
     * @return array<int, PlayerCreateRequest>
     */
    private function apply(Request $request): array
    {
        $user = null;
        $daedalus = null;
        $character = $request->request->get('character');

        if (($daedalusId = $request->request->get('daedalus')) !== null) {
            $daedalus = $this->daedalusService->findById((int) $daedalusId);
        }

        if (($userId = $request->request->get('user')) !== null) {
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
