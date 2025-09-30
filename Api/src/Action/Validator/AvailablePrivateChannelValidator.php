<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Chat\Services\ChannelServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class AvailablePrivateChannelValidator extends ConstraintValidator
{
    public function __construct(private ChannelServiceInterface $channelService) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AvailablePrivateChannel) {
            throw new UnexpectedTypeException($constraint, AvailablePrivateChannel::class);
        }

        $player = $value->getPlayer();
        $target = $value->playerTarget();

        $playerChannels = $this->channelService->getPlayerChannels($player, privateOnly: true);
        if ($playerChannels->count() >= $player->getMaxPrivateChannels()) {
            $this->context
                ->buildViolation(ActionImpossibleCauseEnum::WHISPER_PLAYER_NO_AVAILABLE_CHANNEL)
                ->addViolation();

            return;
        }

        $targetChannels = $this->channelService->getPlayerChannels($target, privateOnly: true);
        if ($targetChannels->count() >= $target->getMaxPrivateChannels()) {
            $this->context
                ->buildViolation(ActionImpossibleCauseEnum::WHISPER_TARGET_NO_AVAILABLE_CHANNEL)
                ->addViolation();
        }
    }
}
