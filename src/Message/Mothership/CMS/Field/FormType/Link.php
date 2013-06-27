<?php

namespace Message\Mothership\CMS\Field\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class Link extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('scope', 'hidden');
        $builder->add('target');
    }

    public function getName()
    {
        return 'link';
    }
}