<?php
// src/AppBundle/Entity/Number.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="number")
 * @ORM\HasLifecycleCallbacks 
 * @ORM\Entity(repositoryClass="AppBundle\Entity\NumberRepository")
 */
class Number
{
	 /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

     /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $twilioNumber;

     /**
     * @ORM\Column(type="string", length=34, nullable=true)
     */
    private $sid;

      /**
     * @ORM\Column(type="datetime", length=30)
     * @Assert\Date()
     */
    private $expiration;


    /**
     * @ORM\OneToOne(targetEntity="Event", mappedBy="numberObject")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     **/
    private $event;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isDemo;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set twilioNumber
     *
     * @param string $twilioNumber
     * @return Number
     */
    public function setTwilioNumber($twilioNumber)
    {
        $this->twilioNumber = $twilioNumber;

        return $this;
    }

    /**
     * Get twilioNumber
     *
     * @return string 
     */
    public function getTwilioNumber()
    {
        return $this->twilioNumber;
    }

    /**
     * Set sid
     *
     * @param string $sid
     * @return Number
     */
    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get sid
     *
     * @return string 
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set expiration
     *
     * @param \DateTime $expiration
     * @return Number
     * @ORM\PrePersist 
     */
    public function setExpiration()
    {
        $createdat = new \DateTime();
        $monthindays = 29;
        $this->expiration = $createdat->modify("+" . $monthindays . " day");
    }

    /**
     * Get expiration
     *
     * @return \DateTime 
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     * @return Number
     */
    public function setEvent(\AppBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \AppBundle\Entity\Event 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set isDemo
     *
     * @param boolean $isDemo
     * @return Number
     */
    public function setIsDemo($isDemo)
    {
        $this->isDemo = $isDemo;

        return $this;
    }

    /**
     * Get isDemo
     *
     * @return boolean 
     */
    public function getIsDemo()
    {
        return $this->isDemo;
    }
}
