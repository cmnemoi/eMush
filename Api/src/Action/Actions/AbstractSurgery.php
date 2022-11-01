<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\CriticalSuccess;
use Mush\Action\ActionResult\Error;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Implement surgery action
 * A medic can perform a surgery in medlab or if it holds the medikit
 * For 2 Action Points, medic can heal one injury of a player that is lying down
 * There is a chance to fail and give a septis
 * There is a chance for a critical success that grant the player extra triumph.
 *
 * More info : http://mushpedia.com/wiki/Medic
 */
abstract class AbstractSurgery extends AbstractAction
{
    protected string $name = ActionEnum::SURGERY;

    private const FAIL_CHANCES = 10;
    private const CRITICAL_SUCCESS_CHANCES = 15;

    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->randomService = $randomService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Player;
    }

    protected function checkResult(): ActionResult
    {
        $date = new \DateTime();

        $failChanceEvent = new PreparePercentageRollEvent(
            $this->player,
            $this->getFailChance(),
            $this->getActionName(),
            $date
        );
        $failChanceEvent->addReason(ActionOutputEnum::FAIL);
        $this->eventService->callEvent($failChanceEvent, PreparePercentageRollEvent::TRIGGER_ROLL_RATE);
        $failChances = $failChanceEvent->getRate();

        $criticalSuccessChancesEvent = new PreparePercentageRollEvent(
            $this->player,
            $this->getCriticalSuccessChance(),
            $this->getActionName(),
            $date
        );
        $criticalSuccessChancesEvent->addReason(ActionOutputEnum::CRITICAL_SUCCESS);
        $this->eventService->callEvent($criticalSuccessChancesEvent, PreparePercentageRollEvent::TRIGGER_ROLL_RATE);
        $criticalSuccessChances = $criticalSuccessChancesEvent->getRate();

        $result = $this->randomService->outputCriticalChances($failChances, 0, $criticalSuccessChances);

        if ($result === ActionOutputEnum::FAIL) {
            return new Fail();
        } elseif ($result === ActionOutputEnum::CRITICAL_SUCCESS) {
            return new CriticalSuccess();
        } elseif ($result === ActionOutputEnum::SUCCESS) {
            return new Success();
        }

        return new Error('this output should not exist');
    }

    public abstract function getFailChance() : int;
    public abstract function getCriticalSuccessChance() : int;

}
