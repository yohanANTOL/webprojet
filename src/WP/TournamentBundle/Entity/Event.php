<?php

namespace WP\TournamentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="WP\TournamentBundle\Repository\EventRepository")
 */
class Event
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="lieu", type="string", length=255)
     */
    private $lieu;

    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var bool
     * 
     * @ORM\Column(name="ispro", type="boolean")
     */
    private $ispro;

    /**
     * @ORM\OneToOne(targetEntity="WP\TournamentBundle\Entity\Image", cascade={"persist"})
     */
    private $cover;


    /**
     * @ORM\OneToOne(targetEntity="WP\TournamentBundle\Entity\Image", cascade={"persist"})
     */
    private $planning;


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Event
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set lieu
     *
     * @param string $lieu
     *
     * @return Event
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * Get lieu
     *
     * @return string
     */
    public function getLieu()
    {
        return $this->lieu;
    }



    /**
     * @return text
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get cover
     *
     * @return Image
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set cover
     *
     * @param Image $cover
     *
     * @return Event
     */
    public function setCover(Image $cover)
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * Get planning
     *
     * @return Image
     */
    public function getPlanning()
    {
        return $this->planning;
    }

    /**
     * Set planning
     *
     * @param Image $planning
     *
     * @return Event
     */
    public function setPlanning(Image $planning)
    {
        $this->planning = $planning;

        return $this;
    }

    /**
     * Set ispro
     *
     * @param boolean $ispro
     *
     * @return Event
     */
    public function setIspro($ispro)
    {
        $this->ispro = $ispro;

        return $this;
    }

    /**
     * Get ispro
     *
     * @return boolean
     */
    public function getIspro()
    {
        return $this->ispro;
    }
}
