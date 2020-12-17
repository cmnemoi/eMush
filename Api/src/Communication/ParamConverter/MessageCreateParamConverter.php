<?php

namespace Mush\Communication\ParamConverter;

use Mush\Communication\Entity\Dto\CreateMessage;
use Mush\Communication\Services\MessageServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageCreateParamConverter implements ParamConverterInterface
{
    private MessageServiceInterface $messageService;

    public function __construct(MessageServiceInterface $messageService)
    {
        $this->messageService = $messageService;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $message = $request->get('message');
        $parent = $request->get('parent');

        $messageCreate = new CreateMessage();
        $parentMessage = null;
        if ($parent) {
            $parentMessage = $this->messageService->getMessageById($parent);
            if ($parentMessage === null) {
                throw new NotFoundHttpException('Parent message not found');
            }
        }

        $messageCreate
            ->setParent($parentMessage)
            ->setMessage($message)
        ;

        $request->attributes->set($configuration->getName(), $messageCreate);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return CreateMessage::class === $configuration->getClass();
    }
}
