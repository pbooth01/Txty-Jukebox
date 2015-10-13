<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SongRepository extends EntityRepository
{
    public function getQueueNumberForSong(Song $song)
    {
        $em = $this->getEntityManager();

        $res = $em->getRepository('AppBundle:Song')
            ->createQueryBuilder('s')
            ->where('s.event = :event AND s.played = false AND s.isSearchable = true')
            ->setParameters(['event'=> $song->getEvent()])
            ->getQuery()->getResult();

        return count($res);

    }

    public function addSong($sender, $keyword, Event $event, EventUser $eventuser)
    {
        $temp_song = new Song();
        $namecheck = explode(" ", $keyword);

        $em = $this->getEntityManager();

        $temp_song->setSender($sender);
        $temp_song->setKeyword($keyword);
        $temp_song->setEvent($event);
        $temp_song->setEventUser($eventuser);
        $temp_song->setIsSearchable($this->checkSearchable($keyword));
        $temp_song->setSkipped(false);
        $em->persist($temp_song);
        
        if($namecheck[0] == "#name"){ 
            $name = join(" ", array_slice($namecheck, 1));
        	$eventuser->setNickname($name);
        }
        
        $em->flush();
        
        return $temp_song;
    }
    
    public function checkSearchable($keyword){ 
    	$hashtag = "#";
    	
    	if($keyword[0] == $hashtag){
    		return false;
    		
    	}else return true;	
    }

    public function lowerCaseCommand($keyword){

        $keyword_pieces = explode(" ", $keyword, 2);

        $keyword_pieces[0] = strtolower($keyword_pieces[0]);

        $keyword = implode(" ", $keyword_pieces);

        return $keyword;
    }

    public function getNewlyAddedSongs($pingedat, Event $event)
    { 
        $newsongs = [];

        $queryBuilder = $this->createQueryBuilder('s')
                             ->where('s.event = :event AND s.createdAt >= :time')
                             ->setParameters(['event'=> $event->getId(), 'time' => $pingedat]);
        ;

        $query = $queryBuilder->getQuery();
        $newsongs = $query->getResult();

        return $newsongs;
    }
    public function addAdminToEvent($twilio, $event, $keyword){
    	
    	$number = trim(str_replace("#admin", "", $keyword));
    	$em = $this->getEntityManager();
    	
    	if( strpos($number, '1') !==0 )
    		$number = "1". $number;
    	
    	$event->setAccountManagers($number);
    	
    	try{
    		$message = $twilio->account->messages->sendMessage($event->getTwilioNumber(), $number,"You have been added as a jukebox admin for " . $event->getEventName());
    	}catch(\Exception $e){
                return new Response("FAILURE: " . $e->getMessage());
        }
    	
    	$em->flush($event);
    	
    }
    
    public function getMessageFromKeyword($lowerKeyword, $queueNumber = 0){
        $keywordCommand = explode(" ", $lowerKeyword);
    	
    	$message = "Thanks for your request, your song has been added to the playlist and is number: $queueNumber in the queue.";
    	
    	if( strchr($keywordCommand[0],"#name") !== false )
    		$message ="Thanks for your request, your name has been updated.";
    	
    	if( strchr($keywordCommand[0],"#fullscreen") !== false )
    		$message = "Thanks for your request, fullscreen mode has been activated.";
    		
    	if( strchr($keywordCommand[0],"#admin") !== false )
    		$message = "Thanks for your request, number has been marked as an admin."; 
    	
	if( strchr($keywordCommand[0],"#skip") !== false )
    		$message = "Thanks! The song should be skipped momentarily."; 
    		
    	return $message;
    	
    }

    public function getNumbersFromKeyword($event, $lowerKeyword){

        $keyWordArray = explode(" ", $lowerKeyword, 2);
        $responseArray = [];
        $searchPhrase = $keyWordArray[1];

        $songs = $this->createQueryBuilder('s')
                    ->where('s.event = :event AND s.keyword = :searchPhrase' )
                    ->setParameters(['event'=> $event, 'searchPhrase' => $searchPhrase])
                    ->getQuery()
                    ->getResult(); 

        if($songs){
            foreach ($songs as $value) {
                $response = "Number: " . $value->getSender(true) . "at: " . date('h:i A', strtotime($value->getCreatedAt()->format('Y-m-d H:i:s')));
                array_push($responseArray, $response);
            }
        }else{
            $response = "I'm sorry, there are no songs on the playlist that match your search";
            array_push($responseArray, $response);
        }     

        return implode(", ", $responseArray);
    }

    public function successMessage($twilio, $sender, $message, $number)
    {
          
            try{
                $message = $twilio->account->messages->sendMessage($number, $sender, $message);
            }catch(\Exception $e){
                return new Response("FAILURE: " . $e->getMessage());
            }
    }

      public function failureMessage($twilio, $sender, $number)
    {
          $failMsg = "I'm Sorry, You don't have permission.";

            try{
                $message = $twilio->account->messages->sendMessage($number, $sender, $failMsg);
            }catch(\Exception $e){
                return new Response("FAILURE: " . $e->getMessage());
            }
    }


}