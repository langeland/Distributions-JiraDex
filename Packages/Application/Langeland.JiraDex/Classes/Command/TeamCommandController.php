<?php
namespace Langeland\JiraDex\Command;

/*
 * This file is part of the Langeland.JiraDex package.
 */

use Langeland\JiraDex\Domain\Model\Team;
use Langeland\JiraDex\Domain\Model\TeamMember;
use Langeland\JiraDex\Domain\Repository\TeamRepository;
use Langeland\JiraDex\Service\JiraService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class TeamCommandController extends CommandController
{

    /**
     * @var TeamRepository
     * @Flow\Inject
     */
    protected $teamRepository;

    /**
     * @var JiraService
     * @Flow\Inject
     */
    protected $jiraService;

    public function listCommand()
    {
        $teamMembers = $this->teamRepository->findAll();
        \Neos\Flow\var_dump($teamMembers->toArray());
    }

    /**
     *
     */
    public function createCommand()
    {
        $newTeam = new Team();

        $newTeam->setName($this->output->ask('Team name: '));

        $boards = $this->jiraService->getBoards();

        $rows = array();
        foreach ($boards as $board) {
            $rows[] = array($board['id'], $board['name'], $board['type']);
        }

        $this->outputLine('Jira board');
        $this->output->outputTable($rows, array('ID', 'Name', 'Type'));
        $newTeam->setJiraBoardId($this->output->ask('Jira board ID: '));

        $this->teamRepository->add($newTeam);
        \Neos\Flow\var_dump($newTeam);
    }

    /**
     * Add a member to a team. (Not implemented)
     *
     * @param Team $team
     * @param TeamMember $teamMember
     */
    public function addTeamMemberCommand(Team $team, TeamMember $teamMember)
    {

    }

}
