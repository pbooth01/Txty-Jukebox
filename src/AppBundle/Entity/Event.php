<?php
// src/AppBundle/Entity/Product.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity()
 * @ORM\Table(name="event")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\EventRepository")
 */
class Event
{
     /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(
     *      min = 3,
     *      max = 30,
     *      minMessage = "Your Event Name must be at least {{ limit }} characters long",
     *      maxMessage = "Your Event Name cannot be longer than {{ limit }} characters"
     * )
     */
    private $eventName;

      /**
     * @ORM\Column(type="string", length=100)
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 30,
     *      minMessage = "Your quote must be at least {{ limit }} characters long",
     *      maxMessage = "Your quote Name cannot be longer than {{ limit }} characters"
     * )
     */
    private $quote;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="events")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Song", mappedBy="event")
     **/
    private $songs;

    public function __construct() {
        $this->songs = new ArrayCollection();
    }

    /**
     * @ORM\OneToOne(targetEntity="Number", mappedBy="event")
     * @ORM\JoinColumn(name="number_id", referencedColumnName="id", nullable=true)
     */
    private $numberObject;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $twilioNumber;

    /**
     * @ORM\Column(type="string", length=34, nullable=true)
     */
    private $sid;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Type(
     * type ="digit",
     * message="Host Number can only contain digits"
     * )
     * @Assert\Length(
     *      min = 10,
     *      max = 30,
     *      minMessage = "Your quote must be at least {{ limit }} characters long",
     *      maxMessage = "Your quote Name cannot be longer than {{ limit }} characters"
     * )
     */
    private $HostNumber;

    /**
     * @ORM\Column(type="datetime", length=30)
     * @Assert\Date()
     */
    private $startTime;

    /**
     * @ORM\Column(type="datetime", length=30)
     * @Assert\Date()
     */
    private $endTime;

     /**
     * @ORM\Column(type="integer", length=30)
     * @Assert\NotNull()
     * @Assert\Length(
     *      min = 1,
     *      max = 24,
     *      minMessage = "The length of your event must be at least {{ limit }} characters long",
     *      maxMessage = "The length of your event cannot be longer than {{ limit }} characters"
     * )
     */
    private $eventLength;


    /**
     * @var array
     *
     * @ORM\Column(name="account_managers", type="json_array", nullable=true)
     */
    private $accountManagers = array();

     /**
     * @ORM\Column(type="boolean")
     */
    private $allAdmin;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isDemo;
        
    /**
     * @return array
     */
    public function getAccountManagers()
    {
        return $this->accountManagers;

    }

    /**
     * @param array $accountManagers
     */
    public function setAccountManagers($accountManagers)
    {
        $this->accountManagers[] = $accountManagers;
    }


    /**
     * Set twilioNumber
     *
     * @param string $twilioNumber
     * @return Event
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
    public function getTwilioNumber($isFormatted = false)
    {
        $number = $this->twilioNumber; //Run a check for string length
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

        return $number;
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
     * Set eventName
     *
     * @param string $eventName
     * @return Event
     */
    public function setEventName($eventName)
    {
        $this->eventName = $eventName;

        return $this;
    }

    /**
     * Get eventName
     *
     * @return string 
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * Set quote
     *
     * @param string $quote
     * @return Event
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get quote
     *
     * @return string 
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Event
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add songs
     *
     * @param \AppBundle\Entity\Song $songs
     * @return Event
     */
    public function addSong(\AppBundle\Entity\Song $songs)
    {
        $this->songs[] = $songs;

        return $this;
    }

    /**
     * Remove songs
     *
     * @param \AppBundle\Entity\Song $songs
     */
    public function removeSong(\AppBundle\Entity\Song $songs)
    {
        $this->songs->removeElement($songs);
    }

    /**
     * Get songs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSongs()
    {
        return $this->songs;
    }

    /**
     * Set allAdmin
     *
     * @param boolean $allAdmin
     * @return Event
     */
    public function setAllAdmin($allAdmin)
    {
        $this->allAdmin = $allAdmin;

        return $this;
    }

    /**
     * Get allAdmin
     *
     * @return boolean 
     */
    public function getAllAdmin()
    {
        return $this->allAdmin;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime
     * @return Event
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime 
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set eventLength
     *
     * @param integer $eventLength
     * @return Event
     */
    public function setEventLength($eventLength)
    {
        $this->eventLength = $eventLength;

        return $this;
    }

    /**
     * Get eventLength
     *
     * @return integer 
     */
    public function getEventLength()
    {
        return $this->eventLength;
    }

    /**
     * Set HostNumber
     *
     * @param string $hostNumber
     * @return Event
     */
    public function setHostNumber($hostNumber)
    {
    	
    	// making sure that all numbers have 1 as fisrt int
    	if( strpos($hostNumber, '1') !== 0 ) {
    		$hostNumber = "1" . $hostNumber;
    	}
    	    	     	
        $this->HostNumber = $hostNumber;

        return $this;
    }

    /**
     * Get HostNumber
     *
     * @return string 
     */
    public function getHostNumber()
    {
        return $this->HostNumber;
    }
    
    
    /**
     * Set endTime
     *
     * @param \DateTime $endTime
     * @return Event
     * @ORM\PrePersist 
     */
    public function setEndTime(){
    	$end = clone $this->startTime;
    	$endtime = $end->modify("+" . $this->eventLength . " hours");
    	$this->endTime = $endtime;
    }


     /**
     * Get startTime
     *
     * @return \DateTime 
     */
    public function getEndTime(){
        return $this->endTime;
    }
    
    public function getIsActive(){
    	$now = new \DateTime();
    	    	
    	return $now >= $this->startTime && $now <= $this->getEndTime();
    }

    /**
     * Set sid
     *
     * @param string $sid
     * @return Event
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
     * Set numberObject
     *
     * @param \AppBundle\Entity\Number $numberObject
     * @return Event
     */
    public function setNumberObject(\AppBundle\Entity\Number $numberObject = null)
    {
        $this->numberObject = $numberObject;

        return $this;
    }

    /**
     * Get numberObject
     *
     * @return \AppBundle\Entity\Number 
     */
    public function getNumberObject()
    {
        return $this->numberObject;

    }

    public function getIsDemo(){
    	return $this->isDemo;    
    }
    
    public function setIsDemo($v){
    	$this->isDemo = $v;
    	return $this;

    }
}
