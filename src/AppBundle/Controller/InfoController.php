<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use AppBundle\Form\Type\UserType;
use AppBundle\Form\Type\EventType;
use AppBundle\Entity\Event;
use AppBundle\Entity\Song;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class InfoController extends Controller
//ASK Do I need to check to make sure the {id} exists before I execute any code
{
    /**
     * @Route("/event/info", name="event_info")
     */
    public function eventinfoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('AppBundle:Event')->find($request->get('id'));

        $data = [];
        $data['name'] = $event->getEventName();
        $data['quote'] = $event->getQuote();
        $data['isDemo'] = $event->getIsDemo() ? 1 : 0;
        
        $data['number'] = $event->getTwilioNumber(true);
        
        $data['keywords'] = [];
        foreach ($event->getSongs() as $value) {
            $data["keywords"][] = $value;
        }
        return new JsonResponse($data);

    }
    

    /**
     * @Route("/event/list", name="event_song_list")
     */
    public function songlistAction(Request $request)
    {
       $time = $request->get('time') / 1000;
       $pingedat = new \DateTime();
       $pingedat->setTimestamp($time);

       $em = $this->getDoctrine()->getManager();
       $event = $em->getRepository('AppBundle:Event')->find($request->get('id'));

        $includeAll = $request->get('includeAll');
        
        $songinlist = [];
        $songRepository = $em->getRepository('AppBundle:Song');
        $songinlist = $songRepository->getNewlyAddedSongs($pingedat, $event, $includeAll);

        $data = [];

        foreach ($songinlist as $value) {
            $data[] = $value;
        }

        $result = ["at" => time() * 1000, "list" => $data];
        return new JsonResponse($result);
    }

    /**
     * @Route("/song/update", name="update_song")
     * 
     */
    public function songupdateAction(Request $request)
    {
        $temp_song = $this->getDoctrine()->getManager()->getRepository('AppBundle:Song')->find($request->get('id'));
        $temp_song->setPlayed(true);
        $this->getDoctrine()->getManager()->flush($temp_song);

        return new JsonResponse('Success');
    }
    
    
     /**
     * @Route("/event/{id}/skip")
     */
    public function skipSongAction()
    {
    
	$skip = @file_get_contents('/mnt/dev/skip');
	file_put_contents('/mnt/dev/skip',0);
	return new Response($skip==1 ? 1 : 0);
	
    }
}
