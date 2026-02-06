<?php

namespace Mush\MetaGame\Entity\Poll;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Mush\User\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: 'poll_option')]
class PollOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Poll::class)]
    private Poll $poll;

    #[ORM\OneToMany(mappedBy: 'option', targetEntity: Vote::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $votes;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    public function __construct(Poll $poll, string $name)
    {
        $this->poll = $poll;
        $this->name = $name;
        $this->votes = new ArrayCollection();
        $this->poll->addOption($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPoll(): Poll
    {
        return $this->poll;
    }

    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote)
    {
        $this->votes->add($vote);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function userAlreadyVoted(User $user): bool
    {
        $votes = $this->getVotes()->filter(
            static fn (Vote $vote) => $vote->getUser() === $user
        );

        return $votes->count() > 0;
    }

    public function getUserVote(User $user): false|Vote
    {
        $votes = $this->getVotes()->filter(
            static fn (Vote $vote) => $vote->getUser() === $user
        );

        return $votes->first();
    }

    public function removeVote(Vote $vote)
    {
        $this->votes->removeElement($vote);
    }
}
