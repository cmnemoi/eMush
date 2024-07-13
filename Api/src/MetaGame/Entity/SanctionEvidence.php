<?php

namespace Mush\MetaGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Communication\Entity\Message;
use Mush\Player\Entity\ClosedPlayer;
use Mush\RoomLog\Entity\RoomLog;

#[ORM\Entity]
#[ORM\Table(name: 'sanction_evidence')]
class SanctionEvidence
{
    use TimestampableEntity;

    private const array ENTITY_NAME_MAP = [
        Message::class => 'message',
        ClosedPlayer::class => 'closedPlayer',
        RoomLog::class => 'roomLog',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Message::class)]
    private ?Message $message;

    #[ORM\ManyToOne(targetEntity: ClosedPlayer::class)]
    private ?ClosedPlayer $closedPlayer;

    #[ORM\ManyToOne(targetEntity: RoomLog::class)]
    private ?RoomLog $roomLog;

    #[ORM\OneToOne(inversedBy: 'sanctionEvidence', targetEntity: ModerationSanction::class)]
    private ModerationSanction $moderationSanction;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSanctionEvidence(): SanctionEvidenceInterface
    {
        $sanctionEvidence = $this->message ?: $this->roomLog ?: $this->closedPlayer;
        if ($sanctionEvidence === null) {
            throw new \Exception('One sanction evidence should be set');
        }

        return $sanctionEvidence;
    }

    public function getEvidenceAsArray(): array
    {
        $sanctionEvidence = $this->getSanctionEvidence();

        return [
            'className' => self::ENTITY_NAME_MAP[$sanctionEvidence->getClassName()],
            'id' => $sanctionEvidence->getId(),
            'message' => $sanctionEvidence->getMessage(),
            'day' => $sanctionEvidence->getDay(),
            'cycle' => $sanctionEvidence->getCycle(),
            'date' => $sanctionEvidence->getCreatedAt(),
        ];
    }

    public function setSanctionEvidence(SanctionEvidenceInterface $sanctionEvidence): static
    {
        if ($sanctionEvidence instanceof Message) {
            $this->message = $sanctionEvidence;

            return $this;
        }
        if ($sanctionEvidence instanceof RoomLog) {
            $this->roomLog = $sanctionEvidence;

            return $this;
        }
        if ($sanctionEvidence instanceof ClosedPlayer) {
            $this->closedPlayer = $sanctionEvidence;

            return $this;
        }

        throw new \Exception('this sanction evidence is not a valid SanctionEvidence');
    }

    public function getModerationSanction(): ModerationSanction
    {
        return $this->moderationSanction;
    }

    public function setModerationSanction(ModerationSanction $moderationSanction): static
    {
        $this->moderationSanction = $moderationSanction;

        return $this;
    }
}
