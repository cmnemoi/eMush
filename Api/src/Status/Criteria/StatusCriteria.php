<?php

namespace Mush\Status\Criteria;

use Mush\Daedalus\Entity\Daedalus;

class StatusCriteria
{
    private Daedalus $daedalus;

    private $name;

    public function __construct(Daedalus $daedalus)
    {
        $this->daedalus = $daedalus;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }

    public function setDaedalus(Daedalus $daedalus): StatusCriteria
    {
        $this->daedalus = $daedalus;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): StatusCriteria
    {
        $this->name = $name;

        return $this;
    }
}
