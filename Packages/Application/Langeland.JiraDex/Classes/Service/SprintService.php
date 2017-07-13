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
     * @var JiraService
     * @Flow\Inject
     */
    protected $jiraService;

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
     * @return Sprint
     */
    public function create(Team $team, $jiraSprintId)
    {
        $newSprint = new Sprint();
        $newSprint->setTeam($team);
        $newSprint->setJiraSprintId($jiraSprintId);

        $jiraSprint = $this->jiraService->getSprint($jiraSprintId);

        $newSprint->setName($jiraSprint['name']);
        $newSprint->setState($jiraSprint['state']);

        if(array_key_exists('startDate', $jiraSprint)){
            $newSprint->setStartDate(new \DateTime($jiraSprint['startDate']));
        }

        if(array_key_exists('endDate', $jiraSprint)){
            $newSprint->setEndDate(new \DateTime($jiraSprint['endDate']));
        }

        $this->sprintRepository->add($newSprint);

        /** @var TeamMember $teamMember */
        foreach ($team->getTeamMembers() as $teamMember) {
            $this->addAllocation($newSprint, $teamMember);
        }

        return $newSprint;
    }

    /**
     * @param Sprint $sprint
     * @return mixed
     */
    public function update(Sprint $sprint)
    {

        $jiraSprint = $this->jiraService->getSprint($sprint->getJiraSprintId());

        $sprint->setName($jiraSprint['name']);
        $sprint->setState($jiraSprint['state']);

        if(array_key_exists('startDate', $jiraSprint)){
            $sprint->setStartDate(new \DateTime($jiraSprint['startDate']));
        }

        if(array_key_exists('endDate', $jiraSprint)){
            $sprint->setEndDate(new \DateTime($jiraSprint['endDate']));
        }

        return $sprint;
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

    /**
     * @param Sprint $sprint
     */
    public function delete(Sprint $sprint)
    {
        foreach ($sprint->getAllocations() as $allocation) {
            $this->allocationRepository->remove($allocation);
        }

        $this->sprintRepository->remove($sprint);
    }


}