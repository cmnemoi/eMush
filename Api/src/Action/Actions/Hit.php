<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PreMush;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\SkillMushEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Hit extends AttemptAction
{
    protected string $name = ActionEnum::HIT;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator,
            $randomService
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
        $metadata->addConstraint(new PreMush(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;

        $result = $this->makeAttempt();

        if ($result instanceof Success) {
            $damage = $this->randomService->random(1, 3);

            if (in_array(SkillEnum::SOLID, $this->player->getSkills())) {
                ++$damage;
            }
            if (in_array(SkillEnum::WRESTLER, $this->player->getSkills())) {
                $damage += 2;
            }
            if (in_array(SkillMushEnum::HARD_BOILED, $parameter->getSkills())) {
                --$damage;
            }
            if ($parameter->hasItemByName(GearItemEnum::PLASTENITE_ARMOR)) {
                --$damage;
            }
            if ($damage <= 0) {
                // TODO:
            } else {
                $playerModifierEvent = new PlayerModifierEvent($parameter, -$damage, new \DateTime());
                $playerModifierEvent->setReason(EndCauseEnum::ASSASSINATED);
                $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::HEALTH_POINT_MODIFIER);

                $this->playerService->persist($parameter);
            }
        }

        $result->setActionParameter($parameter);

        return $result;
    }
}
