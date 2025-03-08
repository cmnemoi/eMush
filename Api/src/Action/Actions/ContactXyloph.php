<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\AllXylophDatabasesDecoded;
use Mush\Action\Validator\ClassConstraint;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\LinkWithSolConstraint;
use Mush\Action\Validator\NeedTitle;
use Mush\Communications\Entity\XylophEntry;
use Mush\Communications\Repository\XylophRepositoryInterface;
use Mush\Communications\Service\DecodeXylophDatabaseServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ContactXyloph extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::CONTACT_XYLOPH;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        private RandomServiceInterface $randomService,
        private XylophRepositoryInterface $xylophRepository,
        private DecodeXylophDatabaseServiceInterface $decodeXylophDatabaseService,
    ) {
        parent::__construct($eventService, $actionService, $validator);
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraints([
            new HasStatus([
                'status' => PlayerStatusEnum::FOCUSED,
                'target' => HasStatus::PLAYER,
                'statusTargetName' => EquipmentEnum::COMMUNICATION_CENTER,
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
            new NeedTitle([
                'title' => TitleEnum::COM_MANAGER,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::COMS_NOT_OFFICER,
            ]),
            new LinkWithSolConstraint([
                'shouldBeEstablished' => true,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::LINK_WITH_SOL_NOT_ESTABLISHED,
            ]),
            new HasStatus([
                'status' => PlayerStatusEnum::DIRTY,
                'target' => HasStatus::PLAYER,
                'contain' => false,
                'groups' => [ClassConstraint::EXECUTE],
                'message' => ActionImpossibleCauseEnum::DIRTY_RESTRICTION,
            ]),
            new AllXylophDatabasesDecoded([
                'groups' => [ClassConstraint::VISIBILITY],
            ]),
        ]);
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $xylophEntry = $this->getRandomUndecodedXylophDatabaseEntry();
        $this->decodeXylophDatabase($xylophEntry);
    }

    private function getRandomUndecodedXylophDatabaseEntry(): XylophEntry
    {
        $xylophList = $this->xylophRepository->findAllUndecodedByDaedalusId($this->player->getDaedalus()->getId());

        if (!$xylophList) {
            throw new GameException('You cannot decode XylophEntry!');
        }

        return $this->getRandomEntry($xylophList);
    }

    private function getRandomEntry(array $xylophList): XylophEntry
    {
        $xylophName = (string) $this->randomService->getRandomXylophNameToDecode($xylophList);

        return $this->xylophRepository->findByDaedalusIdAndNameOrThrow($this->player->getDaedalus()->getId(), $xylophName);
    }

    private function decodeXylophDatabase(XylophEntry $xylophEntry): void
    {
        $this->decodeXylophDatabaseService->execute(
            xylophEntry: $xylophEntry,
            player: $this->player,
            tags: $this->getTags(),
        );
    }
}
