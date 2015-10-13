<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class EventRepository extends EntityRepository
{
    public function getActiveEvents(User $user){
                
        $temp_user = $user->getId();
        $today = Date("Y-m-d H:i:s");
        
        $PDO = $this->getEntityManager()->getConnection()
        			->prepare('SELECT id FROM event WHERE user_id = :user AND startTime <= Now() AND Now() <= startTime + INTERVAL eventLength HOUR');
        $PDO->bindParam(':user', $temp_user);
        $PDO->execute();
        $result = $PDO->fetchAll();

        $activeevents = [];
        foreach ($result as $value) {
            $event = $this->findOneBy(array('id' => $value));
            $activeevents[] = $event;            
        }
        
        return $activeevents;
    }

    public function getSongsFromExpiredEvents(){
        $now = Date("Y-m-d H:i:s");
        $songs = [];
        $events =  $this->createQueryBuilder('e')
                    ->where('e.endTime < :now')
                    ->setParameters(['now'=> $now])
                    ->orderBy("e.startTime", "DESC")
                    ->getQuery()
                    ->getResult();  
        foreach ($events as $event) {
                $songs = array_merge($songs, $event->getSongs()->toArray());
            } 
            
        return $songs;   
    }

    public function getTempEventForNumber($temp_number, $demoNumber){                
        $event = $this->findOneBy(['HostNumber' => $temp_number, "isDemo" => true]);
        
        if($event){
            return $event;
        }
        
        $temp_event = new Event();        
        $temp_event->setEventName('Temporary Texty Event');
        $temp_event->setQuote('Have fun with friends');
        $temp_event->setEventLength(2);
        $temp_event->setStartTime( new \DateTime() );
        $temp_event->setHostNumber($temp_number);
        $temp_event->setAllAdmin(false);
        $temp_event->setIsDemo(true);
        $temp_event->setTwilioNumber($demoNumber);
        
        $this->getEntityManager()->persist($temp_event);
        $this->getEntityManager()->flush();
        
        return $temp_event;
    }

    public function getEventsForUser(User $user) {
    	return $this->createQueryBuilder('u')
			    	->where('u.user = :user')
			    	->setParameters(['user'=> $user])
			    	->orderBy("u.startTime", "DESC")
			    	->getQuery()
			    	->getResult();    	
    }

      public function getEventsForNumberRemoval() {

        $null = 'null';
       $PDO = $this->getEntityManager()->getConnection()
                    ->prepare('SELECT id FROM event WHERE TRIM(twilioNumber) IS NOT NULL AND (startTime > Now() OR Now() > startTime + INTERVAL eventLength HOUR)');
        //$PDO->bindParam(':value', NULL);
        $PDO->execute();
        $result = $PDO->fetchAll();

        $activeevents = [];
        foreach ($result as $value) {
            $event = $this->findOneBy(array('id' => $value));
            $events[] = $event;            
        }
        
        return $events;     
    }

    public function freeNumbersFromPastEvents(){
        $day = 24;
        $now = new \DateTime();
        $daybefore = $now->modify("-" . $day . " hours");

         //get events that expired more than 24hrs ago and have numbers attached
         $expiredEvents = $this->createQueryBuilder('u')
                    ->where('u.endTime <= :daybefore AND u.numberObject != :null')
                    ->setParameters(['null'=> 'NULL', 'daybefore' => $daybefore])
                    ->getQuery()
                    ->getResult();  

        //remove the foreign keys in the numbers table and the events table
        foreach ($expiredEvents as $value) {

            $value->getNumberObject()->setEvent(NULL);
            $this->getEntityManager()->flush($value->getNumberObject());
         
            $value->setNumberObject(null);
            $value->setTwilioNumber(null);
            $value->setSid(null);

            $this->getEntityManager()->flush($value);
        }
    }

    public function getPastEvents(User $user) //Treat as current for right now
    {

		return $this->createQueryBuilder('u')
					->where('u.user = :user AND u.startTime <= :today')
					->setParameters(['user'=> $user, 'today' => new \DateTime()])
					->getQuery()
					->getResult();		
    }

    public function getFutureEvents(User $user)
    {
        return $this->createQueryBuilder('u')
        			->where('u.user = :user AND u.startTime > :today')
            		->setParameters(['user'=> $user, 'today' => new \DateTime()])
					->getQuery()
					->getResult();
    }
    
    public function getEventFromNumber($number, $isDemo = false, $sender) {
        if($isDemo){
            return $this->findOneBy(array('twilioNumber' => $number, "isDemo" => $isDemo, 'HostNumber' => $sender));
        }
        
        return $this->findOneBy(array('twilioNumber' => $number, "isDemo" => $isDemo));
    }

    public function checkForAdmin(Event $event, $number){
        $admins = $event->getAccountManagers();
        foreach ($admins as $value) {           
        	if (strpos($value, $number)!==false){
                return true;
            }
        }
        return false;
    }
    
}