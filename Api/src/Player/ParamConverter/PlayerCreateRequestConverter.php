<?php

namespace Mush\Player\ParamConverter;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Mush\User\Service\UserServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class PlayerCreateRequestConverter implements ParamConverterInterface
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

    public function apply(Request $request, ParamConverter $configuration)
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

        $request->attributes->set($configuration->getName(), $playerRequest);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return PlayerCreateRequest::class === $configuration->getClass();
    }
}
