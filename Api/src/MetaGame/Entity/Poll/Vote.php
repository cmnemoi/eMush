<?php

namespace Mush\MetaGame\Entity\Poll;

use Doctrine\ORM\Mapping as ORM;
use Mush\User\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: 'poll_vote')]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: PollOption::class)]
    private PollOption $option;

    public function __construct(PollOption $pollOption, User $user)
    {
        $this->user = $user;
        $this->option = $pollOption;
        $this->option->addVote($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOption(): PollOption
    {
        return $this->option;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
