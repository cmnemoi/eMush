<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ParameterHasAction;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\SkillMushEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Hit extends AttemptAction
{
    protected string $name = ActionEnum::HIT;

    /** @var Player */
    protected $parameter;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $randomService,
            $eventDispatcher,
            $actionService
        );

        $this->playerService = $playerService;
        $this->randomService = $randomService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadVisibilityValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new ParameterHasAction());
        $metadata->addConstraint(new Reach());
    }

    public function isVisible(): bool
    {
        return parent::isVisible() &&
            $this->player->getPlace() === $this->parameter->getPlace() &&
            $this->player !== $this->parameter;
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->player->getDaedalus()->getGameStatus() === GameStatusEnum::STARTING) {
            return ActionImpossibleCauseEnum::PRE_MUSH_AGGRESSIVE;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $result = $this->makeAttempt();

        if ($result instanceof Success) {
            $damage = $this->randomService->random(1, 3);

            if (in_array(SkillEnum::SOLID, $this->player->getSkills())) {
                ++$damage;
            }
            if (in_array(SkillEnum::WRESTLER, $this->player->getSkills())) {
                $damage += 2;
            }
            if (in_array(SkillMushEnum::HARD_BOILED, $this->parameter->getSkills())) {
                --$damage;
            }
            if ($this->parameter->hasItemByName(GearItemEnum::PLASTENITE_ARMOR)) {
                --$damage;
            }
            if ($damage <= 0) {
                // TODO:
            } else {
                $actionModifier = new Modifier();
                $actionModifier
                    ->setDelta(-$damage)
                    ->setTarget(ModifierTargetEnum::HEALTH_POINT)
                ;

                $playerEvent = new PlayerEvent($this->parameter);
                $playerEvent->setModifier($actionModifier);
                $playerEvent->setReason(EndCauseEnum::ASSASSINATED);
                $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

                $this->playerService->persist($this->parameter);
            }
        }

        return $result;
    }
}
