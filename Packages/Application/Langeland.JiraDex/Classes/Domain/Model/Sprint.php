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
     * @var JiraService
     * @Flow\Inject
     */
    protected $jiraService;

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
     * @var string
     */
    protected $state;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $startDate;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $endDate;

    /**
     * @var \chobie\Jira\Issue[]
     * @Flow\Transient
     */
    protected $issues = array();

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return Sprint
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

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }



    /**
     *
     */

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

    public function getWorkLoad()
    {
        if ($this->getRemainingTime() == 0) {
            return 0;
        }

        return round($this->getRemainingWork() / $this->getRemainingTime() * 100, 2);
    }


}
