<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetUp extends AbstractAction
{
    protected string $name = ActionEnum::GET_UP;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Status([
            'status' => PlayerStatusEnum::LYING_DOWN,
            'target' => Status::PLAYER,
            'groups' => ['visibility'],
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        if ($lyingDownStatus = $this->player->getStatusByName(PlayerStatusEnum::LYING_DOWN)) {
            $this->player->removeStatus($lyingDownStatus);
        }

        return new Success();
    }
}
