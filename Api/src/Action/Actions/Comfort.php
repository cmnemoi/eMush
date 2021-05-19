<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Comfort extends AbstractAction
{
    const BASE_CONFORT = 2;

    protected string $name = ActionEnum::COMFORT;

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
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        //@TODO add validator on shrink skill ?
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;

        $playerModifierEvent = new PlayerModifierEvent($parameter, self::BASE_CONFORT, new \DateTime());
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);

        $this->playerService->persist($parameter);

        return new Success($parameter);
    }
}
