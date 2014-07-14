<?php

namespace Message\Mothership\CMS\Form;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;
use Message\Cog\Localisation\Translator;

class PublishSchedule extends Form\AbstractType
{
  /**
   * Builds the form
   *
   */
  public function buildform(Form\FormBuilderInterface $builder, array $options)
  {
      $builder->addEventListener(Form\FormEvents::PRE_SET_DATA, [$this, 'setDateRange']);
  }

  public function setDateRange(Form\FormEvent $event)
  {

    $form = $event->getForm();
    $model = $event->getData();

    $form->add('publish_date','datetime', [
      'label' => 'ms.cms.publish.publish-date.label',
      'attr' => ['data-help-key' => 'ms.cms.publish.publish-date.help'],
      'data' => $model->publishDateRange->getStart(),
      'mapped' => false
      ]);

    $form->add('unpublish_date', 'datetime', [
      'label' => 'ms.cms.publish.unpublish-date.label',
      'attr' => ['data-help-key' => 'ms.cms.publish.unpublish-date.help'],
      'data' => $model->publishDateRange->getEnd(),
      'mapped' => false
      ]);

  }

  public function getName()
  {
    return 'ms_cms_publishschedule';
  }
}
