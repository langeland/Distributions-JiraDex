<?php
namespace Langeland\JiraDex\Command;

/*
 * This file is part of the Langeland.JiraDex package.
 */

use Langeland\JiraDex\Domain\Model\Allocation;
use Langeland\JiraDex\Domain\Model\Sprint;
use Langeland\JiraDex\Domain\Model\Team;
use Langeland\JiraDex\Domain\Model\TeamMember;
use Langeland\JiraDex\Domain\Repository\SprintRepository;
use Langeland\JiraDex\Domain\Repository\TeamMemberRepository;
use Langeland\JiraDex\Domain\Repository\TeamRepository;
use Langeland\JiraDex\Service\JiraService;
use Langeland\JiraDex\Service\SprintService;
use Langeland\JiraDex\Utility\TimeUtility;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 *
 *
 * @Flow\Scope("singleton")
 */
class SprintCommandController extends CommandController
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
     * @var SprintRepository
     * @Flow\Inject
     */
    protected $sprintRepository;

    /**
     * @var TeamRepository
     * @Flow\Inject
     */
    protected $teamRepository;

    /**
     * @var TeamMemberRepository
     * @Flow\Inject
     */
    protected $teamMemberRepository;

    /**
     *
     */
    public function listCommand()
    {
        $sprints = $this->sprintRepository->findAll();
        \Neos\Flow\var_dump($sprints->toArray());
    }



    /**
     */
    public function createCommand()
    {

        $teams = $this->teamRepository->findAll();
        $choices = array();
        /** @var Team $team */
        foreach ($teams as $team) {
            $choices[] = $team->getName();
        }

        $answer = $this->output->select('Select team: ', $choices);

        /** @var Team $selectedTeam */
        $selectedTeam = $this->teamRepository->findOneByName($choices[$answer]);


        $sprints = $this->jiraService->getSprints($selectedTeam->getJiraBoardId());

        $rows = array();
        foreach ($sprints as $sprint) {
            $rows[] = array($sprint['id'], $sprint['name'], $sprint['state'], $sprint['startDate'], $sprint['endDate']);
        }

        $this->output->outputTable($rows, array('ID', 'Name', 'State', 'startDate', 'endDate'));

        $jiraSprintId = $this->output->ask('Enter Jira sprint Id: ');

        $newSprint = $this->sprintService->create($selectedTeam, $jiraSprintId);
        \Neos\Flow\var_dump($newSprint);
    }

    /**
     *
     */
    public function addAllocationCommand()
    {
        $teams = $this->teamRepository->findAll();
        $choices = array();
        /** @var Team $team */
        foreach ($teams as $team) {
            $choices[] = $team->getName();
        }

        $answer = $this->output->select('Select team: ', $choices);

        /** @var Team $selectedTeam */
        $selectedTeam = $this->teamRepository->findOneByName($choices[$answer]);



        $sprints = $team->getSprints();
        $choices = array();

        /** @var Team $team */
        foreach ($sprints as $sprint) {
            $choices[] = $sprint->getName();
        }

        $answer = $this->output->select('Select sprint: ', $choices);
        $sprint = $this->sprintRepository->findOneByName($choices[$answer]);



        $userInitials = $this->output->ask('Enter user initials: ');
        $teamMember = $this->teamMemberRepository->findOneByInitials($userInitials);

        $this->sprintService->addAllocation($sprint, $teamMember);
    }


}
