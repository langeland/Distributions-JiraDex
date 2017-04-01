<?php
namespace Langeland\JiraDex\Command;

/*
 * This file is part of the Langeland.JiraDex package.
 */

use Langeland\JiraDex\Domain\Model\TeamMember;
use Langeland\JiraDex\Domain\Repository\TeamMemberRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class TeamMemberCommandController extends CommandController
{

    /**
     * @var TeamMemberRepository
     * @Flow\Inject
     */
    protected $teamMemberRepository;

    public function listCommand()
    {
        $teamMembers = $this->teamMemberRepository->findAll();
        \Neos\Flow\var_dump($teamMembers->toArray());
    }

    /**
     *
     */
    public function createCommand()
    {
        $newTeamMember = new TeamMember();
        $newTeamMember->setInitials($this->output->ask('Enter user initials: '));
        $newTeamMember->setName($this->output->ask('Full name: ', 'New team member@' . time()));
        $this->teamMemberRepository->add($newTeamMember);
        \Neos\Flow\var_dump($newTeamMember);
    }

}
