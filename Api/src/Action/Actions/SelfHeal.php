<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\FullHealth;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SelfHeal extends AbstractAction
{
    const BASE_HEAL = 2;

    protected string $name = ActionEnum::SELF_HEAL;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new FullHealth(['target' => FullHealth::PLAYER, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        //@TODO remove diseases

        $actionModifier = new Modifier();
        $actionModifier
            ->setDelta(self::BASE_HEAL)
            ->setTarget(ModifierTargetEnum::HEALTH_POINT);

        $playerEvent = new PlayerEvent($this->player);
        $playerEvent->setModifier($actionModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        $this->playerService->persist($this->player);

        return new Success();
    }
}
