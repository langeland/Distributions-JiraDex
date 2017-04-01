<?php

namespace Langeland\JiraDex\Domain\Model;

/*
 * This file is part of the Langeland.JiraDex package.
 */

use Langeland\JiraDex\Service\JiraService;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Sprint
{


    /**
     * @var string
     */
    protected $name;

    /**
     * @var Team
     * @ORM\ManyToOne(inversedBy="sprints")
     */
    protected $team;

    /**
     * @var \Doctrine\Common\Collections\Collection<\Langeland\JiraDex\Domain\Model\Allocation>
     * @ORM\OneToMany(mappedBy="sprint")
     */
    protected $allocations;

    /**
     * @var integer
     */
    protected $jiraSprintId;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Sprint
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param Team $team
     * @return Sprint
     */
    public function setTeam($team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAllocations()
    {
        return $this->allocations;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $allocations
     * @return Sprint
     */
    public function setAllocations($allocations)
    {
        $this->allocations = $allocations;

        return $this;
    }

    /**
     * @return int
     */
    public function getJiraSprintId()
    {
        return $this->jiraSprintId;
    }

    /**
     * @param int $jiraSprintId
     */
    public function setJiraSprintId($jiraSprintId)
    {
        $this->jiraSprintId = $jiraSprintId;
    }


}
