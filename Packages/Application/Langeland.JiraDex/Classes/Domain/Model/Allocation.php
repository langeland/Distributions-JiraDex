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
     * @var TeamMember
     * @ORM\ManyToOne()
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
     * @return Allocation
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

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

    public function getAllocatedTime()
    {
        $allocatedTime = 0;
        foreach ($this->getWorkHours() as $dayAllocation) {
            $allocatedTime += $dayAllocation * 3600;
        }

        return (integer)$allocatedTime;
    }

    /**
     * @return int
     */
    public function getAllocatedTimeRemaining()
    {
        if ($this->getSprint()->getState() == 'future') {
            return $this->getAllocatedTime();
        } elseif ($this->getSprint()->getState() == 'active') {


            $date = new \DateTime();

            $workStart = new \DateTime();
            $workStart->setTime(9, 0, 0);

            $workEnd = new \DateTime();
            $workEnd->setTime(15, 15, 0);

            if ($date < $workStart) {
                $done = 0;
            } else {
                $done = $date->format('U') - $workStart->format('U');
            }

            $dayOffset = $date->format('N') - 1;

            $allocatedTimeRemaining = 0;

            $allocation = $this->getWorkHours();

            for ($i = $dayOffset; $i < 7; $i++) {
                $allocatedTimeRemaining += $allocation[$i] * 3600;
            }
            $allocatedTimeRemaining -= $done;

            return (integer)$allocatedTimeRemaining;
        } elseif ($this->getSprint()->getState() == 'closed') {
            return 0;
        } else {
            throw new \Exception('Unknown sprint state.');
        }
    }

    /**
     * @return \chobie\Jira\Issue[]
     */
    public function getIssues()
    {
        if ($this->issues == array()) {
            $this->issues = $this->jiraService->getIssuesForSprint($this->getSprint()->getJiraSprintId(), 'assignee = ' . $this->getTeamMember()->getInitials() . ' ORDER BY status');
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
        if ($this->getAllocatedTimeRemaining() == 0) {
            return 0;
        }

        return round($this->getRemainingWork() / $this->getAllocatedTimeRemaining() * 100, 2);
    }

}
