<?php

declare(strict_types=1);

namespace Mush\Daedalus\ParamConverter;

use Mush\Daedalus\Entity\Dto\DaedalusCreateRequest;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Repository\GameConfigRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DaedalusCreateRequestConverter implements ValueResolverInterface
{
    public function __construct(
        private GameConfigRepository $gameConfigRepository
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== DaedalusCreateRequest::class) {
            return [];
        }

        $payload = $request->getPayload();
        $name = $payload->get('name');
        $language = $payload->get('language');
        $config = null;

        if (($configId = $payload->get('config')) !== null) {
            /** @var GameConfig $config */
            $config = $this->gameConfigRepository->find((int) $configId);
        }

        $daedalusRequest = new DaedalusCreateRequest();
        $daedalusRequest
            ->setName((string) $name)
            ->setConfig($config)
            ->setLanguage((string) $language);

        return [$daedalusRequest];
    }
}
