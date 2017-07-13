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
     *
     * @Flow\Identity
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $identifier;

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
     * @var \Doctrine\Common\Collections\Collection<\Langeland\JiraDex\Domain\Model\TeamMember>
     * @ORM\OneToMany(mappedBy="team")
     */
    protected $teamMembers;

    /**
     * @var integer
     */
    protected $jiraBoardId;

    /**
     * Team constructor.
     */
    public function __construct()
    {
        $this->teamMembers = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return Team
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeamMembers()
    {
        return $this->teamMembers;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $teamMembers
     * @return Team
     */
    public function setTeamMembers($teamMembers)
    {
        $this->teamMembers = $teamMembers;
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


    public function getAvailableTime()
    {
        $availableTime = 0;
        /** @var Allocation $allocation */
        foreach ($this->getAllocations() as $allocation) {
            $availableTime += $allocation->getAllocatedTime();
        }

        return $availableTime;
    }

    /**
     * @return int
     */
    public function getRemainingTime()
    {
        $availableTimeRemaining = 0;
        /** @var Allocation $allocation */
        foreach ($this->getAllocations() as $allocation) {
            $availableTimeRemaining += $allocation->getAllocatedTimeRemaining();
        }

        return $availableTimeRemaining;
    }

    /**
     * @return \chobie\Jira\Issue[]
     */
    public function getIssues()
    {
        if ($this->issues == array()) {
            /** @var Allocation $allocation */
            foreach ($this->getAllocations() as $allocation) {
                $this->issues = array_merge($this->issues, $allocation->getIssues());
            }

            $this->issues = array_merge($this->issues, $this->jiraService->getIssuesForSprint($this->getJiraSprintId(), 'assignee is EMPTY ORDER BY status'));
        }

        return $this->issues;
    }

    /**
     * @return \chobie\Jira\Issue[]
     */
    public function getIssuesGroupedByStatus()
    {
        if ($this->issues == array()) {
            $this->getIssues();
        }

        $issuesGrouped = array();
        foreach ($this->issues as $issue) {
            $issuesGrouped[$issue->getStatus()['name']] = $issue;
        }

        return $issuesGrouped;
    }

    /**
     * @return int
     */
    public function getRemainingWork()
    {
        $workRemaining = 0;

        foreach ($this->getIssues() as $issue) {
            if ($issue->getStatus()['name'] != 'Done') {
                $workRemaining += $issue->get('Remaining Estimate');
            }
        }

        return $workRemaining;
    }


}
