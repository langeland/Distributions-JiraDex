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
class SprintReport
{

    /**
     * @var Sprint
     * @ORM\OneToOne()
     */
    protected $sprint;

    /**
     * @var \DateTime
     */
    protected $reportTime;


}
