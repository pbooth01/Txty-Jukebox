<?php
// src/AppBundle/Entity/Product.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="eventuser")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\EventUserRepository")
 */
Class EventUser
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

    /**
     * @ORM\Column(type="integer")
     **/
    private $eventid;
	
	/**
	 * @ORM\OneToMany(targetEntity="Song", mappedBy="eventUser")
	 **/
	private $songs;
	
	public function __construct() {
		$this->songs = new ArrayCollection();
	}
	
	/**
	 * @ORM\Column(type="string", length=30)
	 * @Assert\NotBlank()
	 * @Assert\NotNull()
	 */
	private $PhoneNumber;
	
	/**
	 * @ORM\Column(type="string", length=30)
	 * @Assert\NotBlank()
	 */
	private $NickName;


    /**
      * @ORM\Column(type="boolean")
     */
    private $isAdmin = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isBlocked = false;

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
     * Set PhoneNumber
     *
     * @param string $phoneNumber
     * @return EventUser
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->PhoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get PhoneNumber
     *
     * @return string 
     */
    public function getPhoneNumber()
    {
        return $this->PhoneNumber;
    }

    /**
     * Set NickName
     *
     * @param string $nickName
     * @return EventUser
     */
    public function setNickName($nickName)
    {
        $this->NickName = $nickName;

        return $this;
    }

    /**
     * Get NickName
     *
     * @return string 
     */
    public function getNickName()
    {
        return $this->NickName;
    }

    /**
     * Add songs
     *
     * @param \AppBundle\Entity\Song $songs
     * @return EventUser
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
     * Set isAdmin
     *
     * @param boolean $isAdmin
     * @return EventUser
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * Get isAdmin
     *
     * @return boolean 
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * Set eventid
     *
     * @param integer $eventid
     * @return EventUser
     */
    public function setEventid($eventid)
    {
        $this->eventid = $eventid;

        return $this;
    }

    /**
     * Get eventid
     *
     * @return integer 
     */
    public function getEventid()
    {
        return $this->eventid;
    }

    /**
     * Set isBlocked
     *
     * @param boolean $isBlocked
     * @return EventUser
     */
    public function setIsBlocked($isBlocked)
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }

    /**
     * Get isBlocked
     *
     * @return boolean 
     */
    public function getIsBlocked()
    {
        return $this->isBlocked;
    }
}
