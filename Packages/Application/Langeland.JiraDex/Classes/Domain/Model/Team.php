<?php

namespace Langeland\JiraDex\Domain\Model;

/*
 * This file is part of the Langeland.JiraDex package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Team
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\Collection<\Langeland\JiraDex\Domain\Model\Sprint>
     * @ORM\OneToMany(mappedBy="team")
     */
    protected $sprints;

    /**
     * @var integer
     */
    protected $jiraBoardId;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Team
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSprints()
    {
        return $this->sprints;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $sprints
     * @return Team
     */
    public function setSprints($sprints)
    {
        $this->sprints = $sprints;

        return $this;
    }

    /**
     * @return int
     */
    public function getJiraBoardId()
    {
        return $this->jiraBoardId;
    }

    /**
     * @param int $jiraBoardId
     * @return Team
     */
    public function setJiraBoardId($jiraBoardId)
    {
        $this->jiraBoardId = $jiraBoardId;

        return $this;
    }


}
