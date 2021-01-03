<?php

namespace Mush\Action\ParamConverter;

use Mush\Action\Entity\Dto\ActionRequest;
use Mush\Communication\Services\MessageServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ActionRequestParamConverter implements ParamConverterInterface
{
    private MessageServiceInterface $messageService;

    public function __construct(MessageServiceInterface $messageService)
    {
        $this->messageService = $messageService;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $actionId = $request->get('action');
        $params = $request->get('params');

        $actionRequest = new ActionRequest();

        $actionRequest
            ->setAction($actionId)
            ->setParams($params)
        ;

        $request->attributes->set($configuration->getName(), $actionRequest);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return ActionRequest::class === $configuration->getClass();
    }
}
