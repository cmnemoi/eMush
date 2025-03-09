<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasRole;
use Mush\Communications\Repository\RebelBaseRepositoryInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class TriggerAllRebelContacts extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::TRIGGER_ALL_REBEL_CONTACTS;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private readonly RebelBaseRepositoryInterface $rebelBaseRepository,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasRole(['roles' => [RoleEnum::SUPER_ADMIN], 'groups' => ['visibility']]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target === null;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $rebelBases = $this->rebelBaseRepository->findAllByDaedalusId($this->player->getDaedalus()->getId());
        foreach ($rebelBases as $rebelBase) {
            $rebelBase->triggerContact();
            $this->rebelBaseRepository->save($rebelBase);
        }
    }
}
