<?php

namespace Mush\Daedalus\ParamConverter;

use Mush\Daedalus\Entity\Dto\DaedalusCreateRequest;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Repository\GameConfigRepository;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AutoconfigureTag('controller.argument_value_resolver', ['priority' => 150])]
final class DaedalusCreateRequestConverter implements ValueResolverInterface
{
    private GameConfigRepository $gameConfigRepository;

    public function __construct(
        GameConfigRepository $gameConfigRepository
    ) {
        $this->gameConfigRepository = $gameConfigRepository;
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
        return DaedalusCreateRequest::class === $argument->getType();
    }

    /**
     * @return array<int, DaedalusCreateRequest>
     */
    private function apply(Request $request): array
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

        return [$daedalusRequest];
    }
}
