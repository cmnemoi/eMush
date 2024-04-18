<?php

namespace Mush\Daedalus\ParamConverter;

use Mush\Daedalus\Entity\Dto\DaedalusCreateRequest;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Repository\GameConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class DaedalusCreateRequestConverter implements ParamConverterInterface
{
    private GameConfigRepository $gameConfigRepository;

    public function __construct(
        GameConfigRepository $gameConfigRepository
    ) {
        $this->gameConfigRepository = $gameConfigRepository;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $request->request->get('name');
        $language = $request->request->get('language');
        $config = null;

        if (($configId = $request->request->get('config')) !== null) {
            /** @var GameConfig $config */
            $config = $this->gameConfigRepository->find((int) $configId);
        }

        $daedalusRequest = new DaedalusCreateRequest();
        $daedalusRequest
            ->setName((string) $name)
            ->setConfig($config)
            ->setLanguage((string) $language);

        $request->attributes->set($configuration->getName(), $daedalusRequest);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return DaedalusCreateRequest::class === $configuration->getClass();
    }
}
