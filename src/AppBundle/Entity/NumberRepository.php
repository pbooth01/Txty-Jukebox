<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class NumberRepository extends EntityRepository
{
    public function searchForFreeNumber(Event $event){
           
        $now = new \DateTime();
        $day = 24;
        $daybefore = $now->modify("+" . $day . " hours");
        $eventEnd = $event->getEndTime();

        //free any numbers associated with events that have ended
        $this->getEntityManager()->getRepository('AppBundle:Event')->freeNumbersFromPastEvents();

        //after removing any keys linked to past events, check to see if any are free.
        $freenumbers = $this->createQueryBuilder('n')
            ->where('n.event IS NULL AND n.isDemo = false AND n.expiration >= :endtime')
            ->setParameters(['endtime'=> $eventEnd])
            ->getQuery()
            ->getResult();  

        //return the first free numberObject    
            if($freenumbers){
                return $freenumbers[0];
            }
            else{
                return Null;
            }
                        
    }

    public function getNumbersForRemoval(){
        $now = $today = Date("Y-m-d H:i:s");

        $numbers = $this->createQueryBuilder('n')
            ->where('n.event IS NULL AND n.isDemo = false AND n.expiration < :today')
            ->setParameters(['today'=> $now])
            ->getQuery()
            ->getResult(); 

        return $numbers;
    }

    public function createNewNumberObject($event, $eventnumber, $sid){
        $temp_number = new Number();
        $temp_number->setTwilioNumber($eventnumber);
        $temp_number->setSid($sid);
        $temp_number->setIsDemo(false);
        $temp_number->setEvent($event);

        $this->getEntityManager()->persist($temp_number);
        $this->getEntityManager()->flush();
    }
}