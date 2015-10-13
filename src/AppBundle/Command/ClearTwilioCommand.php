<?php
// src/AppBundle/Command/GreetCommand.php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearTwilioCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('demo:clearNumbers')
            ->setDescription('Clear Twilio Numbers From DB')
            ->addArgument(null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $twilio = $this->getContainer()->get('twilio.api');

        //Remove the link between events and numbers if events can no longer become active 
        $events = $this->getContainer()->get('doctrine')->getManager()
                            ->getRepository('AppBundle:Event')
                            ->getEventsForNumberRemoval();

        foreach ($events as $event) {
            if($event->getTwilioNumber() != $this->getContainer()->getParameter('twilio_demo_number')){
                $event->setTwilioNumber(null);
                $event->setSid(null);
                $event->getNumberObject()->setEvent(NULL);
                $event->setNumberObject(null);
                $this->getContainer()->get('doctrine')->getManager()->flush($event);
            }
        }
        //Remove any numbers that are expired and not linked to an event (All expired numbers wont be linked to any event)
        $numbers = $this->getContainer()->get('doctrine')->getManager()
                            ->getRepository('AppBundle:Number')
                            ->getNumbersForRemoval();

        foreach ($numbers as $number) {
            $twilio->account->incoming_phone_numbers->delete($number->getSid()); //This is needed
            $this->getContainer()->get('doctrine')->getManager()->remove($number);
            $this->getContainer()->get('doctrine')->getManager()->flush($song);
        }

        //Remove all songs in the Db that are assciated with events that cant be active
        $songs = $this->getContainer()->get('doctrine')->getManager()
                            ->getRepository('AppBundle:Event')
                            ->getSongsFromExpiredEvents();

        foreach ($songs as $song) {
           $this->getContainer()->get('doctrine')->getManager()->remove($song); 
           $this->getContainer()->get('doctrine')->getManager()->flush($song);
        }

        $output->writeln("MAde it through the Command");
    }
}