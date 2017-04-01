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
class Allocation
{

    /**
     * @var TeamMember
     *
     * @ORM\OneToOne()
     */
    protected $teamMember;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $workHours = array();

    /**
     * @var Sprint
     * @ORM\ManyToOne(inversedBy="allocations")
     */
    protected $sprint;

    /**
     * @return TeamMember
     */
    public function getTeamMember()
    {
        return $this->teamMember;
    }

    /**
     * @param TeamMember $teamMember
     * @return Allocation
     */
    public function setTeamMember($teamMember)
    {
        $this->teamMember = $teamMember;

        return $this;
    }

    /**
     * @return array
     */
    public function getWorkHours()
    {
        return $this->workHours;
    }

    /**
     * @param array $workHours
     * @return Allocation
     */
    public function setWorkHours($workHours)
    {
        $this->workHours = $workHours;

        return $this;
    }

    /**
     * @return Sprint
     */
    public function getSprint()
    {
        return $this->sprint;
    }

    /**
     * @param Sprint $sprint
     * @return Allocation
     */
    public function setSprint($sprint)
    {
        $this->sprint = $sprint;

        return $this;
    }


}
