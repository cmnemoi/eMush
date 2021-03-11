<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\FullHealth;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Heal extends AbstractAction
{
    const BASE_HEAL = 2;

    protected string $name = ActionEnum::HEAL;

    /** @var Player */
    protected $parameter;

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
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new FullHealth(['target' => FullHealth::PARAMETER, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        //@TODO remove diseases

        $actionModifier = new Modifier();
        $actionModifier
            ->setDelta(self::BASE_HEAL)
            ->setTarget(ModifierTargetEnum::HEALTH_POINT)
        ;

        $playerEvent = new PlayerEvent($this->parameter);
        $playerEvent->setModifier($actionModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        $this->playerService->persist($this->parameter);

        return new Success();
    }
}
