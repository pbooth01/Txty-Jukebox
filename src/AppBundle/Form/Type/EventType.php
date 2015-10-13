<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('eventName')
            ->add('startTime', 'datetime', array(
                'widget' => 'single_text',
                'attr' => array('class' => 'new_event_form_startTime_time')))

                /*'date_widget' => 'single_text',*/
                // this is actually the default format for single_text
                /*'format' => 'yyyy-MM-dd',*/ 
                /*'time_widget' => "single_text")*/
            ->add('eventLength', 'integer', array('label' => 'Event Length in Hours', 
                    'attr' => array('min' => 0, 'max' => 24, 'onkeypress' => "return event.charCode >= 48 && event.charCode <=57" )))
            ->add('quote')
            ->add('HostNumber') 
            ->add('allAdmin', "checkbox", array('label' => 'Enable everyone to edit the playlist?', 'required' => false))
            ->add('schedule event', 'submit')
        ;
    }

    public function getName()
    {
        return 'new_event_form';
    }
}