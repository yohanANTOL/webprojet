<?php

namespace WP\TournamentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inscription
 *
 * @ORM\Table(name="inscription")
 * @ORM\Entity(repositoryClass="WP\TournamentBundle\Repository\InscriptionRepository")
 */
class Inscription
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="inscrit", type="boolean")
     */
    private $inscrit;

    /**
     * @ORM\ManyToOne(targetEntity="WP\TournamentBundle\Entity\Event",cascade={"persist"})
     */
    private $event;
    
    /**
     * @ORM\ManyToOne(targetEntity="WP\UserBundle\Entity\User",cascade={"persist"})
     */
    private $user;
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set inscrit
     *
     * @param boolean $inscrit
     *
     * @return Inscription
     */
    public function setInscrit($inscrit)
    {
        $this->inscrit = $inscrit;

        return $this;
    }

    /**
     * Get inscrit
     *
     * @return bool
     */
    public function getInscrit()
    {
        return $this->inscrit;
    }

    /**
     * Set event
     *
     * @param \WP\TournamentBundle\Entity\Event $event
     *
     * @return Inscription
     */
    public function setEvent(\WP\TournamentBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \WP\TournamentBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set user
     *
     * @param \WP\UserBundle\Entity\User $user
     *
     * @return Inscription
     */
    public function setUser(\WP\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \WP\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
