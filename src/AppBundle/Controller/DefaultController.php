<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
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

class DefaultController extends Controller
{	
	
    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction(Request $request)
    {
        //Check if user is already logged in and redirect to myevents page if they are
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirecttoRoute('user_events');
        }

        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('AppBundle:User');

        $temp_user = new User();

        $form1 = $this->createForm(new UserType(), $temp_user);

        if( $request->isMethod("POST") ){
            $form1->handleRequest( $request );

            if( $form1->isValid() ){
                $temp_user->setEnabled(true);

                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($temp_user, true);
                $temp_user->setRoles(['ROLE_USER']);
                $this->getDoctrine()->getManager()->persist($temp_user);
                $this->getDoctrine()->getManager()->flush();

                //Loggin in the User after they register
                $token = new UsernamePasswordToken($temp_user, $temp_user->getPassword(), "public", $temp_user->getRoles());
                
                $this->get("security.context")->setToken($token); //this might be deprecated, use security.token_storage instead

                // Fire the login event
                // Logging the user in above the way we do it doesn't do this automatically
                $event = new InteractiveLoginEvent($request, $token);
                $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                return $this->redirectToRoute('create_event');
            }
        }

         return ["form1" => $form1->createView(), "onHomepage" => true];

    }       

    /**
     * @Route("/login", name="login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/newsong", name="create_song")
     * @Template
     */
    public function newSongAction(Request $request){

        file_put_contents('/mnt/dev/jukebox-request.txt',json_encode($_REQUEST));

    	$twilio = $this->get('twilio.api');
    	$keyword = $request->get('Body');
    	$lowerKeyword = strtolower($keyword);
        $keywordCommand = explode(" ", $lowerKeyword);
        

    	
    	$number = str_replace("+", "", $request->get('To'));
    	$sender = str_replace("+", "", $request->get('From'));
        
        //Make sure all numbers have a 1 at the first position
        if( strpos($sender, '1') !==0 ){
                $sender = "1". $sender;
        }

        if( strpos($number, '1') !==0 ){
                $number = "1". $number;
        }

    	$demoNumber = $this->container->getParameter('twilio_demo_number');
    	$isDemoNumber = $number == $demoNumber;


        $event =  $this->getDoctrine()->getManager()
    				   ->getRepository('AppBundle:Event')
    				   ->getEventFromNumber($number, $isDemoNumber, $sender);


        //If an event is returned then the user has the opportunity to add to its playlist
    if($event){  
        $isBlocked =  $this->getDoctrine()->getManager()
                           ->getRepository('AppBundle:EventUser')
                           ->isBlockedCheck($event, $sender);

        if($isBlocked){
            $response = "I'm sorry, you dont have permission to send in requests :("; 
            $twilio->account->messages->sendMessage($event->getTwilioNumber(), $sender, $response);
            return new Response("FAILURE");
        }

        //Right above this add a check to see if eventuser is blocked and if is send message                
        $canAdmin = $this->getDoctrine()->getManager()
                         ->getRepository('AppBundle:EventUser')
                         ->adminCheck($event, $sender);
    	$response = "";

        if( in_array($keywordCommand[0], ["#skip", "#admin", "#block", "#who"]) && !$canAdmin ){
            $twilio->account->messages
                   ->sendMessage($event->getTwilioNumber(), $sender,"You are not an admin :(");
            return new Response("ERROR");
        }
    	

    	if( in_array($keywordCommand[0], ["#admin", "#block", "#who"]) && $canAdmin){
    		if($keywordCommand[0] == "#who"){
                $response = $this->getDoctrine()->getManager()
                 ->getRepository('AppBundle:Song')->getNumbersFromKeyword($event, $keyword);
                  $twilio->account->messages->sendMessage($event->getTwilioNumber(), $sender, $response);
                  return new Response("SUCCESS");
            }else{
                $this->getDoctrine()->getManager()
    			  ->getRepository('AppBundle:EventUser')->updateNumberPrivledge($twilio, $event, $lowerKeyword, $sender);
                 return new Response("SUCCESS");
            }
    	}else{

	    	$eventuser = $this->getDoctrine()->getManager()
	    				 	  ->getRepository('AppBundle:EventUser')
	    					  ->getNameFromNumber($event, $sender);
	    	
	    	$song = $this->getDoctrine()->getManager()
	    				 ->getRepository('AppBundle:Song')
	    				 ->addSong($sender, $keyword, $event, $eventuser);
	    	
	    	$queueNumber = $this->getDoctrine()->getManager()
	    				 		->getRepository('AppBundle:Song')
	    						->getQueueNumberForSong($song);
	    	
	    	$response = $this->getDoctrine()->getManager()
	    				    ->getRepository('AppBundle:Song')
	    					->getMessageFromKeyword($lowerKeyword, $queueNumber);
    	 
    	}
        $twilio->account->messages->sendMessage($event->getTwilioNumber(), $sender, $response);
    }else{
        $response = "I'm Sorry there is no event associated with the number you texted :(";

        /*
        Use the Temporary Twilio number to send responses so that even if an event is not
        found we can still send the response that no event was found.
        */
        $twilio->account->messages->
                        sendMessage($this->container->getParameter('twilio_demo_number'), $sender, $response);
    }	
    	return new Response("SUCCESS");
    }

    /**
     * @Route("/jukebox", name="jukebox")
     * @Route("/jukebox-player/{eventId}", name="jukebox_player")
     * @Template
     */

    public function jukeboxAction(Request $request){
    	
    	if($request->get("_route") == "jukebox_player"){
    		$eventId = $request->get('eventId', null);
    		$istest = "1";
    	}else{
    		$eventId = null;
    		$istest = $request->get('test');
    	}
		
        return ['test' => $istest, "eventId" => $eventId];
    }

     /**
     * @Route("/tempevent", name="tempevent")
     */
     public function tempeventAction(Request $request){
        $twilio = $this->get('twilio.api');
        $temp_number = $request->get('phonenumber');
        $demoNumber = $this->container->getParameter('twilio_demo_number');
        
        $event = $this->getDoctrine()->getManager()
        		      ->getRepository('AppBundle:Event')
        		      ->getTempEventForNumber($temp_number, $demoNumber);
                        
        $url = $this->generateUrl('jukebox_player', ['eventId' => $event->getId()]);
        
        return new Response($url);
    }
}
