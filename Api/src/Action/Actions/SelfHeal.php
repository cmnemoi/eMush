<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\FullHealth;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SelfHeal extends AbstractAction
{
    public const BASE_HEAL = 2;

    protected string $name = ActionEnum::SELF_HEAL;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService
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

        $initialHealth = $this->player->getHealthPoint();

        $playerModifierEvent = new PlayerModifierEvent($this->player, self::BASE_HEAL, new \DateTime());
        $playerModifierEvent->setIsDisplayedRoomLog(false);
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::HEALTH_POINT_MODIFIER);

        $healEvent = new ActionEffectEvent($this->player, $this->player);
        $this->eventDispatcher->dispatch($healEvent, ActionEffectEvent::HEAL);

        $this->playerService->persist($this->player);

        $healedQuantity = $this->player->getHealthPoint() - $initialHealth;

        return new Success(null, $healedQuantity);
    }
}
