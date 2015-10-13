<?php
// src/AppBundle/Entity/Product.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="song")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SongRepository")
 */
Class Song implements \jsonSerializable
{
	 /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


     /**
     * @ORM\Column(type="boolean")
     */
    private $played;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $skipped;

	 /**
     * @ORM\Column(type="string", length=140)
     * @Assert\NotBlank()
     */
	private $keyword;
	
	/**
	  * @ORM\Column(type="boolean")
	 */
	private $isSearchable;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $sender;

	/**
    * @ORM\ManyToOne(targetEntity="Event", inversedBy="songs")
    */

	private $event;
	
	/**
	 * @ORM\ManyToOne(targetEntity="EventUser", inversedBy="songs")
	 */
	
	private $eventUser;

     /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_at")
     */
    private $createdAt;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="updated_at")
     */
    private $updatedAt;

    public function __construct(){
        $this->played = false;
    }

    public function jsonSerialize() {
        $temp = 1000 * $this->getCreatedAt()->format('U');
        return ['at' => $temp, 
		'sender' => $this->getEventUser()->getPhoneNumber(), 
		'keyword' => $this->getKeyword(),
		'played' => $this->getPlayed(),
       	'skipped' => $this->getSkipped(),
        'displayName' => $this->getEventUser()->getNickName(),
        'phoneNumber' => $this->getSender(true),
        'isSearchable' => $this->getIsSearchable(),
		'id' => $this->getId()];
    }

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
     * Set keyword
     *
     * @param string $keyword
     * @return Song
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword
     *
     * @return string 
     */
    public function getKeyword()
    {
        return $this->keyword;
    }


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Song
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
       return $this->createdAt;
        
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Song
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     * @return Song
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
     * Set sender
     *
     * @param string $sender
     * @return Song
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return string 
     */
    public function getSender($isFormatted = false)
    {
        $number = $this->sender; //Run a check for string length
        if(strlen($number) == 11){
            if($isFormatted){
                $insertion = "-";
                $index1 = 1;
                $index2 = 5;
                $index3 = 9;
                $result = substr_replace($number, $insertion, $index1, 0);
                $result = substr_replace($result, $insertion, $index2, 0);
                $result = substr_replace($result, $insertion, $index3, 0);
                return $result;
            }
        }
        else if(strlen($number) == 10){
            if($isFormatted){
                $insertion = "-";
                $index1 = 3;
                $index2 = 7;
                $result = substr_replace($number, $insertion, $index1, 0);
                $result = substr_replace($result, $insertion, $index2, 0);
                return $result;
            }
        }
        return $number;
    }

    /**
     * Set played
     *
     * @param boolean $played
     * @return Song
     */
    public function setPlayed($played)
    {
        $this->played = $played;

        return $this;
    }

    /**
     * Get played
     *
     * @return boolean 
     */
    public function getPlayed()
    {
        return $this->played;
    }

    /**
     * Set isSearchable
     *
     * @param boolean $isSearchable
     * @return Song
     */
    public function setIsSearchable($isSearchable)
    {
        $this->isSearchable = $isSearchable;

        return $this;
    }

    /**
     * Get isSearchable
     *
     * @return boolean 
     */
    public function getIsSearchable()
    {
        return $this->isSearchable;
    }

    /**
     * Set skipped
     *
     * @param boolean $skipped
     * @return Song
     */
    public function setSkipped($skipped)
    {
        $this->skipped = $skipped;

        return $this;
    }

    /**
     * Get skipped
     *
     * @return boolean 
     */
    public function getSkipped()
    {
        return $this->skipped;
    }

    /**
     * Set eventUser
     *
     * @param \AppBundle\Entity\EventUser $eventUser
     * @return Song
     */
    public function setEventUser(\AppBundle\Entity\EventUser $eventUser = null)
    {
        $this->eventUser = $eventUser;

        return $this;
    }

    /**
     * Get eventUser
     *
     * @return \AppBundle\Entity\EventUser 
     */
    public function getEventUser()
    {
        return $this->eventUser;
    }
}
