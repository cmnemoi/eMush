<?php

namespace Mush\MetaGame\Entity\Poll;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\User\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: 'poll')]
class Poll
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToMany(mappedBy: 'poll', targetEntity: PollOption::class, cascade: ['remove', 'persist'])]
    private Collection $options;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $title;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxVote;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $important;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $closed = false;

    public function __construct(string $title, int $maxVote = 1, bool $isImportant = false)
    {
        $this->setCreatedAt(new \DateTime());
        $this->title = $title;
        $this->maxVote = $maxVote;
        $this->important = $isImportant;
        $this->options = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMaxVote(): int
    {
        return $this->maxVote;
    }

    public function isImportant(): bool
    {
        return $this->important;
    }

    /**
     * @return Collection<int, PollOption>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(PollOption $pollOption): void
    {
        $this->options->add($pollOption);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getVoteCount(): int
    {
        $count = 0;
        foreach ($this->getOptions() as $option) {
            if ($option !== null) {
                $count += $option->getVotes()->count();
            }
        }

        return $count;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getUserVotes(User $user): Collection
    {
        /** @var ArrayCollection<int, Vote> $votes */
        $votes = new ArrayCollection();
        foreach ($this->getOptions() as $option) {
            if ($option !== null) {
                $vote = $option->getUserVote($user);

                if ($vote !== false) {
                    $votes->add($vote);
                }
            }
        }

        return $votes;
    }

    public function getRemainingsVotes(User $user): int
    {
        return $this->maxVote - $this->getUserVotes($user)->count();
    }

    public function canUserVote(User $User): bool
    {
        return $this->getUserVotes($User)->count() < $this->maxVote && $this->closed === false;
    }

    public function removeVotesForUser(User $user): self
    {
        foreach ($this->getOptions() as $option) {
            if ($option !== null) {
                $vote = $option->getUserVote($user);

                if ($vote !== false) {
                    $option->removeVote($vote);
                }
            }
        }

        return $this;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function close(): bool
    {
        return $this->closed = true;
    }
}
