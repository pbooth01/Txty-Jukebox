<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DatePickerType extends AbstractType
{
    /*public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'widget' => 'single_text'
        ));
    }
    */

    public function getDefaultOptions(array $options)
    {
        return array(
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm',
            'attr' => array(
                'autocomplete' => 'off',
                'class' => 'mybundle_datetime_picker',
            ),
        );
    }

    public function getParent()
    {
        return 'date';
    }

    public function getName()
    {
        return 'datePicker';
    }
}