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
//        \Neos\Flow\var_dump($sprints->toArray());

        $sprintList = array();
        /** @var Sprint $sprint */
        foreach ($sprints as $sprint) {
            $sprintList[] = array(
                $sprint->getName(),
                $sprint->getTeam()->getName(),
                count($sprint->getAllocations()),
                $sprint->getJiraSprintId(),
                $sprint->getIdentifier()
            );
        }
        $this->outputLine('');
        $this->outputLine('Sprints');
        $this->output->outputTable($sprintList, array('Sprint name', 'Team name', '# Allocations', 'Jira sprint Id', 'Id'));

    }

    /**
     * Show a single sprint
     *
     * @param Sprint|null $sprint
     */
    public function showCommand(Sprint $sprint = null)
    {
        if (is_null($sprint)) {
            $sprints = $this->sprintRepository->findAll();
            $choices = array();
            /** @var Sprint $team */
            foreach ($sprints as $singeSprint) {
                $choices[] = $singeSprint->getName();
            }
            $answer = $this->output->select('Select sprint: ', $choices);
            /** @var Sprint $selectedTeam */
            $sprint = $this->sprintRepository->findOneByName($choices[$answer]);
        }

        //\Neos\Flow\var_dump($sprint->getRemainingWork());
        //$this->sendAndExit(1);


        $this->outputLine('Sprint overview');
        $this->output->outputTable(array(
            array('Time available', TimeUtility::format($sprint->getAvailableTime())),
            array('Time remaining', TimeUtility::format($sprint->getRemainingTime())),
            array('Work remaining', TimeUtility::format($sprint->getRemainingWork())),
            array('Work load', $sprint->getWorkLoad() . '%')

        ), array('Sprint: ' . $sprint->getName(), null));

        $members = array();
        /** @var Allocation $allocation */
        foreach ($sprint->getAllocations() as $allocation) {
            $cels = array();
            $cels[] = $allocation->getTeamMember()->getInitials();
            foreach ($allocation->getWorkHours() as $day) {
                $cels[] = $day;
            }
            $cels[] = TimeUtility::format($allocation->getAllocatedTime());
            $cels[] = TimeUtility::format($allocation->getAllocatedTimeRemaining());
            $cels[] = TimeUtility::format($allocation->getRemainingWork());
            $cels[] = round($allocation->getRemainingWork()/$allocation->getAllocatedTimeRemaining()*100, 2) . '%';

            $members[] = $cels;
        }
        $this->outputLine('');
        $this->outputLine('Member overview');
        $this->output->outputTable($members, array('User', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Time available', 'Time remaining', 'Work remaining', 'Work load'));



        $this->outputLine('');
        $this->outputLine('Issues overview');



        \Neos\Flow\var_dump(array_keys($sprint->getIssuesGroupedByStatus()));


        $issues = $sprint->getIssues();
        $this->outputLine('');
        $this->outputLine('Issues overview');

        $rows = array();
        /** @var \chobie\Jira\Issue $issue */
        foreach ($issues as $issue) {
            $rows[] = array(
                $issue->getKey(),
                $issue->getSummary(),
                $this->formatRemainingEstimate($issue),
                $issue->getStatus()['name'],
                $issue->getProject()['name'] . ' [' . $issue->get('epic')['name'] . ']',
                $issue->getAssignee()['name']
            );
        }
        $this->output->outputTable($rows, array());
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

    protected function formatRemainingEstimate($issue)
    {
        if ($issue->getStatus()['name'] != 'Done' && $issue->get('Remaining Estimate') == '') {
            return '<comment>Est.</comment>';
        } elseif ($issue->getStatus()['name'] != 'Done' && $issue->get('Remaining Estimate') == 0) {
            return '<error>Re est.</error>';
        } elseif ($issue->getStatus()['name'] != 'Done' && $issue->get('Remaining Estimate') < 1800) {
            return '<comment>' . TimeUtility::format($issue->get('Remaining Estimate')) . '</comment>';
        } else {
            return TimeUtility::format($issue->get('Remaining Estimate'));
        }
    }

}
