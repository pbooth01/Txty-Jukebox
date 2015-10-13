<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email')
            ->add('plainPassword', 'password', array('label'=>'Password'))
            ->add('save', 'submit')
        ;
    }

    public function getName()
    {
        return 'new_user_form';
    }
}