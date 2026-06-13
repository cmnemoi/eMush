<?php

declare(strict_types=1);

namespace Mush\Chat\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Chat\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'communication_channel')]
#[ApiResource(
    normalizationContext: ['groups' => ['channel_read', 'moderation_read']],
    denormalizationContext: ['groups' => ['channel_write']],
    paginationEnabled: false,
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_MODERATOR")',
            filters: ['channel.search_filter', 'date.order_filter'],
        ),
        new Get(
            security: 'is_granted("ROLE_MODERATOR")',
        ),
    ],
)]
class Channel
{
    use TimestampableEntity;

    #[ORM\Version]
    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    private int $version = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['channel_read', 'moderation_read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['channel_read', 'moderation_read'])]
    private string $scope = ChannelScopeEnum::PUBLIC;

    #[ORM\ManyToOne(targetEntity: DaedalusInfo::class)]
    #[Groups(['channel_read', 'moderation_read'])]
    private DaedalusInfo $daedalusInfo;

    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: ChannelPlayer::class)]
    #[Groups(['channel_read', 'moderation_read'])]
    private Collection $participants;

    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: ChannelPlayer::class)]
    #[Groups(['channel_read', 'moderation_read'])]
    private Collection $allTimeParticipants;

    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: Message::class, cascade: ['remove'])]
    #[Groups(['channel_read', 'moderation_read'])]
    private Collection $messages;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->allTimeParticipants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public static function createPublicChannel(): self
    {
        return (new self())->setScope(ChannelScopeEnum::PUBLIC);
    }

    public static function createTipsChannel(): self
    {
        return (new self())->setScope(ChannelScopeEnum::TIPS);
    }

    public static function createTipsChannelForPlayer(Player $player): self
    {
        $channel = self::createTipsChannel()->setDaedalus($player->getDaedalusInfo());
        // We need an id to normalize the channel so we make a fake one here. It won't be used for other purposes
        self::setupFakeIdForChannel($channel);

        return $channel;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdOrThrow(): int
    {
        return $this->id ?? throw new \RuntimeException('Channel id not found');
    }

    public function getDaedalusInfo(): DaedalusInfo
    {
        return $this->daedalusInfo;
    }

    public function setDaedalus(DaedalusInfo $daedalusInfo): static
    {
        $this->daedalusInfo = $daedalusInfo;

        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setScope(string $scope): static
    {
        $this->scope = $scope;

        return $this;
    }

    public function isPublic(): bool
    {
        return ChannelScopeEnum::PUBLIC === $this->getScope();
    }

    public function isScope(string $scope): bool
    {
        return $scope === $this->getScope();
    }

    public function isMushChannel(): bool
    {
        return ChannelScopeEnum::MUSH === $this->getScope();
    }

    public function isPrivate(): bool
    {
        return ChannelScopeEnum::PRIVATE === $this->getScope();
    }

    public function isPrivateOrMush(): bool
    {
        return $this->isPrivate() || $this->isMushChannel();
    }

    public function isPublicOrMush(): bool
    {
        return $this->isPublic() || $this->isMushChannel();
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function getAllTimeParticipants(): Collection
    {
        return $this->allTimeParticipants;
    }

    public function addParticipant(ChannelPlayer $channelPlayer): self
    {
        $this->participants->add($channelPlayer);
        $this->addAllTimeParticipant($channelPlayer);

        return $this;
    }

    public function isPlayerParticipant(PlayerInfo $playerInfo): bool
    {
        return !$this->getParticipants()->filter(static fn (ChannelPlayer $channelPlayer) => ($channelPlayer->getParticipant() === $playerInfo))->isEmpty();
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function setMessages(Collection $messages): self
    {
        $this->messages = $messages;

        return $this;
    }

    public function addMessage(Message $message): static
    {
        $this->messages->add($message);

        return $this;
    }

    /** @return Collection<array-key, Message> */
    public function getPlayerUnreadMessages(Player $player): Collection
    {
        return $this->getMessages()->filter(static fn (Message $message) => $message->isUnreadBy($player));
    }

    /** @return Collection<array-key, Message> */
    public function getMessagesWithChildren(): Collection
    {
        return $this->getMessages()->filter(static fn (Message $message) => $message->getChild()->count() > 0);
    }

    public function isTipsChannel(): bool
    {
        return ChannelScopeEnum::TIPS === $this->scope;
    }

    public function isNotTipsChannel(): bool
    {
        return ChannelScopeEnum::TIPS !== $this->scope;
    }

    public function shouldFlashForPlayer(Player $player): bool
    {
        if ($this->isNotTipsChannel()) {
            return false;
        }

        $shouldFlashForBeginner = $player->hasStatus(PlayerStatusEnum::BEGINNER) && !$player->hasReadTips();
        $shouldFlashForUncompletedMissions = $player->hasUnreadMissions();

        return $shouldFlashForBeginner || $shouldFlashForUncompletedMissions;
    }

    public function getNumberOfTipsMessagesForPlayer(Player $player): int
    {
        if ($this->isNotTipsChannel()) {
            return 0;
        }

        $count = $player->getReceivedMissions()->filter(static fn (CommanderMission $mission) => $mission->isUnread())->count();
        if ($player->hasStatus(PlayerStatusEnum::BEGINNER) && !$player->hasReadTips()) {
            ++$count;
        }

        return $count;
    }

    #[Groups(['channel_read', 'moderation_read'])]
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    private function addAllTimeParticipant(ChannelPlayer $channelPlayer): self
    {
        /** @var ChannelPlayer $participant */
        foreach ($this->allTimeParticipants as $participant) {
            if ($channelPlayer->getParticipant()->getId() === $participant->getParticipant()->getId()) {
                return $this;
            }
        }
        $this->allTimeParticipants->add($channelPlayer);

        return $this;
    }

    private static function setupFakeIdForChannel(self $channel): void
    {
        (new \ReflectionClass($channel))->getProperty('id')->setValue($channel, 0);
    }
}
