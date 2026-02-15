<?php

namespace Mush\Player\ParamConverter;

use Mush\Player\Entity\Dto\UpdatePersonalNotesTabsRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class UpdatePersonalNotesTabsRequestConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $tabs = $data['tabs'] ?? null;

        $dto = new UpdatePersonalNotesTabsRequest();
        if ($tabs !== null) {
            $dto->setTabs($tabs);
        }

        $request->attributes->set($configuration->getName(), $dto);

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return UpdatePersonalNotesTabsRequest::class === $configuration->getClass();
    }
}
