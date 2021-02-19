<?php

namespace Mush\Player\ParamConverter;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Player\Entity\Dto\PlayerCreateRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class PlayerCreateRequestConverter implements ParamConverterInterface
{
    private DaedalusServiceInterface $daedalusService;

    public function __construct(DaedalusServiceInterface $daedalusService)
    {
        $this->daedalusService = $daedalusService;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $daedalus = null;
        $character = $request->get('character');

        if (($daedalusId = $request->get('daedalus')) !== null) {
            $daedalus = $this->daedalusService->findById($daedalusId);
        }

        $playerRequest = new PlayerCreateRequest();
        $playerRequest
            ->setCharacter($character)
            ->setDaedalus($daedalus)
        ;

        $request->attributes->set($configuration->getName(), $playerRequest);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return PlayerCreateRequest::class === $configuration->getClass();
    }
}
