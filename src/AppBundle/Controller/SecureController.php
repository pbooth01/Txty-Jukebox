<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\Type\UserType;
use AppBundle\Form\Type\EventType;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use AppBundle\Entity\Song;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("has_role('ROLE_USER')")
 */
class SecureController extends Controller
{
		
    /**
     * @Route("/events", name="user_events")
     * @Template
     */
    public function eventsAction(Request $request){

        $today = Date("Y-m-d H:i:s");
        
        $myEvents = $this->getDoctrine()->getManager()
        				 ->getRepository('AppBundle:Event')
        			     ->getEventsForUser($this->getUser()); 
        
        $temp_event = new Event();
        $temp_event->setIsDemo(false);
        $form = $this->createForm(new EventType(), $temp_event);
        
        if( $request->isMethod("POST") ){

        	$wasSuccess = $this->processEventForm($request, $form);

        	if($wasSuccess){
        		$this->addFlash('info', 'Boom! The event was added.');
        		return $this->redirectToRoute('user_events');
        	}        	                 
        }
        
        $flashes = $this->get('session')->getFlashBag()->get('info');
        $flashes = count($flashes) ? join("<br>", $flashes) : null;
        
        return ["today" => $today, "myEvents" => $myEvents, "flashes" => $flashes, 
        		"onHomepage" => false, "form" => $form->createView()];     
    }

    /**
     * @Route("/newevent", name="create_event")
     * @Template
     */
    public function neweventAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $eventuserRepository = $em->getRepository('AppBundle:EventUser');
        $eventRepository = $em->getRepository('AppBundle:Event');

        $temp_event = new Event();
        $temp_event->setUser($this->getUser());
        $temp_event->setIsDemo(false);

        $form = $this->createForm(new EventType(), $temp_event);
        if( $request->isMethod("POST") ){

        $form->handleRequest( $request );

        if( $form->isValid() ){

             $number = $temp_event->getHostNumber();

            if( strpos($number, '1') !==0 ) {//making sure that all numbers have 1 as fisrt int
                $number = "1" . $number;
                $temp_event->setHostNumber( $number );
            }

            $this->getDoctrine()->getManager()->persist($temp_event);
            $this->getDoctrine()->getManager()->flush();
            //$searchNumber = $temp_event->getTwilioNumber();
            //$searchEvent = $eventRepository->getEventFromNumber($searchNumber); 
            $eventuserRepository->makeHostAdmin($temp_event, $temp_event->getHostNumber()); //maybe change to superadmin in the furture
            
            return $this->redirectToRoute('user_events');
            }
        }
        return ["form" => $form->createView(), "onHomepage" => false];
    }
    
    private function processEventForm(Request $request, $form){
    	    	    	 
    	$form->handleRequest( $request );
    	 
    	if( $form->isValid() ){
            $event = $form->getData();
            $number = $event->getHostNumber();

            if( strpos($number, '1') !==0 ) {//making sure that all numbers have 1 as fisrt int
                $number = "1" . $number;
                $event->setHostNumber( $number );
            }

    		$event->setUser( $this->getUser() );
    		$this->getDoctrine()->getManager()->persist($event);
    		$this->getDoctrine()->getManager()->flush();
    	
    		//maybe change to superadmin in the furture
    		$this->getDoctrine()->getManager()
    			 ->getRepository('AppBundle:EventUser')
    			 ->makeHostAdmin($event, $event->getHostNumber()); 
    		 
    		return true;
    	}    	
    	
    	return false;
    }
    
    private function addTwilioNumberToEvent($event){
    	
        if( strlen($event->getTwilioNumber()) ){
    		return $event;
    	}

        //cycle through number table to see if there are any free numbers that havent been used in the last 24 hours
        $twilioNumber = $this->getDoctrine()->getManager()->getRepository('AppBundle:Number')->searchForFreeNumber($event);
        
        //If there is a free number in the number table
        if($twilioNumber != Null){
            $twilioNumber->setEvent($event);
            $this->getDoctrine()->getManager()->flush($twilioNumber);

            $event->setNumberObject($twilioNumber);

            //These two things are being set also so the rest of the program logic doesnt have to change
            $event->setTwilioNumber($twilioNumber->getTwilioNumber());
            $event->setSid($twilioNumber->getSid());
            $this->getDoctrine()->getManager()->flush($event);

            return $event;
        }

        //Using the Twilio API to generate a random phone number if none are available
        $twilio = $this->get('twilio.api');
        

       $numbers = $twilio->account->available_phone_numbers->getList('US', 'Local');
        $firstNumber = $numbers->available_phone_numbers[0]->phone_number;
        $purchasedNumber = $twilio->account->incoming_phone_numbers->create(array('PhoneNumber' => $firstNumber));

        $sid = $purchasedNumber->sid; //This is need so the number can be deleted in the end

        $url = $this->generateUrl('create_song', array(), true);

        
        $purchasedNumber->update(array(
                        "SmsUrl" => $url,
                        "SmsMethod" => "POST"
        ));
        

        $eventnumber = str_replace("+", "",$purchasedNumber->phone_number);

    	
        $numberObject = $this->getDoctrine()->getManager()
                             ->getRepository('AppBundle:Number')->createNewNumberObject($event, $eventnumber, $sid);

        $event->setTwilioNumber($eventnumber);
        $event->setSid($sid);
        $event->setNumberObject($numberObject);
        
        
    	$this->getDoctrine()->getManager()->persist($event);
    	$this->getDoctrine()->getManager()->flush();

    
    	return $event;
    }
        
    /**
     * @Route("/eventinfo/{eventId}", name="eventinfo")
     * @Template
    */
    public function eventinfoAction(Request $request){
    	
        $eventId = $request->get('eventId');        
        $event = $this->getDoctrine()->getManager()->getRepository('AppBundle:Event')->find($eventId);        
        
        if( !$event->getIsActive() ){
        	$this->addFlash('info', 'Sorry! That event has not started.');
        	return $this->redirectToRoute('user_events');        	
        }
        
        $this->addTwilioNumberToEvent($event);        
        $date = $event->getStartTime()->format('Y-m-d H:i:s');

        return['event' => $event, 'loadSender' => true, 'startdate' => $date, 'id' => $eventId];
    }
    
}
