<?php
namespace Langeland\JiraDex\Command;

/*
 * This file is part of the Langeland.JiraDex package.
 */

use Langeland\JiraDex\Service\JiraService;
use Langeland\JiraDex\Service\SprintService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 *
 *
 * @Flow\Scope("singleton")
 */
class JiraCommandController extends CommandController
{

    /**
     * @var SprintService
     * @Flow\Inject
     */
    protected $sprintService;

    /**
     * @var JiraService
     * @Flow\Inject
     */
    protected $jiraService;

    /**
     * List all boards in Jira. This only includes boards that the user has permission to view.
     */
    public function boardsCommand()
    {
        $boards = $this->jiraService->getBoards();

        $rows = array();
        foreach ($boards as $board) {
            $rows[] = array($board['id'], $board['name'], $board['type']);
        }

        $this->outputLine('Board overview');
        $this->output->outputTable($rows, array('ID', 'Name', 'Type'));
    }


    /**
     * List all sprints from a board, for a given board Id. This only includes sprints that the user has permission to view.
     *
     * @param integer $boardId
     */
    public function sprintsCommand($boardId)
    {
        $sprints = $this->jiraService->getSprints($boardId);
        $rows = array();
        foreach ($sprints as $sprint) {
            $rows[] = array($sprint['id'], $sprint['name'], $sprint['state'], $sprint['startDate'], $sprint['endDate']);
        }

        $this->outputLine('Sprint overview for board: ' . $boardId);
        $this->output->outputTable($rows, array('ID', 'Name', 'State', 'startDate', 'endDate'));
    }

    /**
     * Shows the sprint for a given sprint Id. The sprint will only be returned if the user can view the board that the sprint was created on, or view at least one of the issues in the sprint.
     *
     * @param integer $sprintId
     */
    public function sprintCommand($sprintId)
    {
        $sprint = $this->jiraService->getSprint($sprintId);
        \Neos\Flow\var_dump($sprint);
    }


}
