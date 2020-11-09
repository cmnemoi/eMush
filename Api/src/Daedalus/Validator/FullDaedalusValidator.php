<?php


namespace Mush\Daedalus\Validator;


use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\GameConfigService;
use Mush\Player\Entity\Dto\PlayerRequest;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FullDaedalusValidator extends ConstraintValidator
{
    private GameConfigService $gameConfigService;

    public function __construct(GameConfigService $gameConfigService)
    {
        $this->gameConfigService = $gameConfigService;
    }

    public function validate($daedalus, Constraint $constraint)
    {
        if (!$daedalus instanceof Daedalus) {
            throw new \InvalidArgumentException();
        }

        if ($daedalus->getPlayers()->count() >= $this->gameConfigService->getConfig()->getMaxPlayer()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setCode(FullDaedalus::FULL_DAEDALUS_ERROR)
                ->addViolation()
            ;
        }
    }
}