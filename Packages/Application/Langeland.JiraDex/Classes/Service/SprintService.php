<?php

namespace Langeland\JiraDex\Service;

use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\Basic;
use chobie\Jira\Issues\Walker;
use Langeland\JiraDex\Domain\Model\Allocation;
use Langeland\JiraDex\Domain\Model\Sprint;
use Langeland\JiraDex\Domain\Model\Team;
use Langeland\JiraDex\Domain\Model\TeamMember;
use Langeland\JiraDex\Domain\Repository\AllocationRepository;
use Langeland\JiraDex\Domain\Repository\SprintRepository;
use Neos\Flow\Annotations as Flow;

/**
 * Class SprintService
 * @package Langeland\JiraDex\Service
 *
 * @Flow\Scope("singleton")
 */
class SprintService
{

    /**
     * @var Api
     * @Flow\Inject
     */
    protected $jira;

    /**
     * @var SprintRepository
     * @Flow\Inject
     */
    protected $sprintRepository;

    /**
     * @var AllocationRepository
     * @Flow\Inject
     */
    protected $allocationRepository;

    /**
     * @var array
     * @Flow\InjectConfiguration()
     */
    protected $settigs = array();

    /**
     * @param Team $team
     * @param integer $jiraSprintId
     * @param null $name
     * @return Sprint
     */
    public function create(Team $team, $jiraSprintId, $name = null)
    {
        $newSprint = new Sprint();
        $newSprint->setTeam($team);
        $newSprint->setJiraSprintId($jiraSprintId);

        if (is_null($name)) {
            $newSprint->setName('New sprint@' . time());
        } else {
            $newSprint->setName($name);
        }

        $this->sprintRepository->add($newSprint);

        return $newSprint;
    }

    /**
     * @param Sprint $sprint
     * @param TeamMember $teamMember
     */
    public function addAllocation(Sprint $sprint, TeamMember $teamMember)
    {
        $newAllocation = new Allocation();
        $newAllocation->setSprint($sprint);
        $newAllocation->setTeamMember($teamMember);
        $newAllocation->setWorkHours($teamMember->getDefaultWorkHours());

        $this->allocationRepository->add($newAllocation);
    }


}