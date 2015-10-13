<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventUserRepository extends EntityRepository
{
	public function getNameFromNumber($event, $number)
	{
		$em = $this->getEntityManager();
		$eventuser = $this->findOneBy(array('PhoneNumber' => $number, 'eventid' => $event->getId()));
		
		if(!$eventuser){
			$eventuser = $this->createNewEventUser($event, $number);
			return $eventuser;
			
		}else{
			return $eventuser;
		}
	}

	public function isBlockedCheck($event, $number){

		if( $event->getHostNumber() == $number ){
			return false;
		}

		$eventuser = $this->getEntityManager()
						  ->getRepository('AppBundle:EventUser')
						  ->findOneBy(array('PhoneNumber' => $number, 'eventid' => $event->getId()));

		if($eventuser && $eventuser->getIsBlocked()){
			return true;
		}

		return false;
	}


	public function adminCheck($event, $number){

		if( $event->getAllAdmin() || $event->getHostNumber() == $number ){
			return true;
		}

		$eventuser = $this->getEntityManager()
						  ->getRepository('AppBundle:EventUser')
						  ->findOneBy(array('PhoneNumber' => $number, 'eventid' => $event->getId()));

		if($eventuser && $eventuser->getIsAdmin()){
			return true;
		}

		return false;
	}

	public function updateNumberPrivledge($twilio, $event, $keyword, $sender){ //Marked for # change

		$keywordCommand = explode(" ", $keyword);

		$number = trim(str_replace("#admin", "", $keyword));
		$number = trim(str_replace("#block", "", $number));

		$number = preg_replace('/\D+/', '', $number);

    	$em = $this->getEntityManager();
    	
    	if( strpos($number, '1') !== 0 ){
    		$number = "1". $number;
    	}	

		$eventuser = $em->getRepository('AppBundle:EventUser')
		->findOneBy(array('PhoneNumber' => $number, 'eventid' => $event->getId()))
		;

		if($eventuser){
			if($keywordCommand[0] == "#admin"){
				$eventuser->setIsAdmin(true);
				try{
    				$message = $twilio->account->messages->sendMessage($event->getTwilioNumber(), $number,"You have been added as a jukebox admin for the event " . $event->getEventName());
    			}catch(\Exception $e){
                	return new Response("FAILURE: " . $e->getMessage());
        		}

        		try{
    				 $twilio->account->messages->sendMessage($event->getTwilioNumber(), $sender, "Admin status upgraded baby!");
    			}catch(\Exception $e){
                	return new Response("FAILURE: " . $e->getMessage());
        		}
			}
			//If the first part of the keyword does not equal admin then it must be a block
			else{
				if($eventuser->getIsBlocked()){
					$eventuser->setIsBlocked(false);
				}else{
					$eventuser->setIsBlocked(true);
				}
				try{
    				$message = $twilio->account->messages->sendMessage($event->getTwilioNumber(), $sender,"The blocked status for this number has been changed for the event " . $event->getEventName());
    			}catch(\Exception $e){
                	return new Response("FAILURE: " . $e->getMessage());
        		}
			}

		}
		else{
			$eventuser = $this->createNewEventUser($event, $number);
			$this->updateNumberPrivledge($twilio, $event, $keyword);
		}
    	$em->flush($eventuser);
	}

	public function makeHostAdmin($event, $number){
		$temp = new EventUser();
		$em = $this->getEntityManager();
		
		$temp->setPhoneNumber($number);
		$temp->setNickName($number);
		$temp->setIsAdmin(true);
		$temp->setEventid($event->getId());
		
		$em->persist($temp);
		$em->flush();
	}
	
	public function createNewEventUser($event, $number){
		$temp = new EventUser();
		$em = $this->getEntityManager();
		
		$temp->setPhoneNumber($number);
		$temp->setNickName($number);
		$temp->setEventid($event->getId());
		
		$em->persist($temp);
		$em->flush();
		
		return $temp;
	}
	
	public function updateNickName ($number,$name){
		$em = $this->getEntityManager();
		$eventuser = $em->getRepository('AppBundle:EventUser')
		->findOneBy(array('PhoneNumber' => $number))
		;
		if(!$eventuser){
			$temp = $this->createNewEventUser($number);
			$this->updateNickName($number,$name);
		}
		else{
			$eventuser->setNickName($name);
			$em->flush();
		}	
	}

}