<?php

namespace Message\Mothership\CMS\Field\FormType;

use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class MothershipFile extends AbstractType implements ContainerAwareInterface
{
	protected $_container;

	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'choices' => $this->_getFiles(),
		));
	}

	public function getParent()
	{
		return 'choice';
	}

	public function getName()
	{
		return 'ms_file';
	}

	protected function _getFiles()
	{
		if (!$this->_container) {
			throw new \LogicException(
				__CLASS__ . '::$_container not set, use the setContainer() method'
			);
		}
//var_dump($this->_container); die();
		$files = (array) $this->_container['file_manager.file.loader']->getAll();
		$choices = array();

		foreach ($files as $file) {
			$choices[$file->id] = $file->name;
		}

		return $choices;
	}
}