<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Place\Service\PlaceServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Takeoff extends AbstractAction
{
    protected string $name = ActionEnum::TAKEOFF;

    private PlayerServiceInterface $playerService;
    private PlaceServiceInterface $placeService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        PlaceServiceInterface $placeService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
        $this->placeService = $placeService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new PlaceType(['groups' => ['visibility'], 'type' => PlaceTypeEnum::ROOM]));
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $patrolship */
        $patrolship = $this->parameter;

        $patrolshipRoom = $this->placeService->findByNameAndDaedalus($patrolship->getName(), $this->player->getDaedalus());
        if ($patrolshipRoom === null) {
            throw new \RuntimeException('Patrolship room not found');
        }
        $this->player->changePlace($patrolshipRoom);

        $this->playerService->persist($this->player);
    }
}
