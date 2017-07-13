<?php

namespace Langeland\JiraDex\Controller;

/*
 * This file is part of the Langeland.JiraDex package.
 */

use Langeland\JiraDex\Domain\Repository\TeamRepository;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

class StandardController extends AbstractActionController
{

    /**
     * @var TeamRepository
     * @Flow\Inject
     */
    protected $teamRepository;

    /**
     * @return void
     */
    public function indexAction()
    {

        $this->view->assignMultiple(array(
            'teams' => $this->teamRepository->findAll()
        ));

    }

}
