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
use Langeland\JiraDex\Domain\Repository\TeamRepository;
use Neos\Flow\Annotations as Flow;

/**
 * Class TeamService
 * @package Langeland\JiraDex\Service
 *
 * @Flow\Scope("singleton")
 */
class TeamService
{


    /**
     * @var TeamRepository
     * @Flow\Inject
     */
    protected $teamRepository;


    /**
     * @param null $name
     * @param $jiraBoardId
     * @return Team
     */
    public function create($name, $jiraBoardId)
    {
        $newTeam = new Team();
        $this->teamRepository->add($newTeam);

        return $newTeam;
    }

    /**
     * @param Team $team
     * @param TeamMember $teamMember
     * @internal param Sprint $sprint
     */
    public function addTeamMember(Team $team, TeamMember $teamMember)
    {


    }


}