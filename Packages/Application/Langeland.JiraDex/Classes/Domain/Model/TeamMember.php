<?php

namespace Langeland\JiraDex\Domain\Model;

/*
 * This file is part of the Langeland.JiraDex package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class TeamMember
{

    /**
     * @var string
     */
    protected $initials;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $defaultWorkHours = array(6.25, 6.25, 6.25, 6.25, 5, 0, 0);

    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getInitials()
    {
        return $this->initials;
    }

    /**
     * @param string $initials
     * @return TeamMember
     */
    public function setInitials($initials)
    {
        $this->initials = $initials;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultWorkHours()
    {
        return $this->defaultWorkHours;
    }

    /**
     * @param array $defaultWorkHours
     * @return TeamMember
     */
    public function setDefaultWorkHours($defaultWorkHours)
    {
        $this->defaultWorkHours = $defaultWorkHours;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return TeamMember
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


}
