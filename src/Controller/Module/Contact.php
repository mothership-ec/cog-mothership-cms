<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;

class Contact extends Controller
{
	public function contact()
	{
		$form = $this->get('form.contact');
		$form->handleRequest();

		if ($form->isValid()) {
			$data = $form->getData();
			$this->_sendEmail($data);
		}
	}

	protected function _sendEmail(array $data)
	{
		$factory = $this->get('mail.factory.contact')
			->set('toEmail', $this->get('cfg')->contact->contactEmail)
			->set('fromEmail', $data['email'])
			->set('name', $data['name'])
			->set('message', $data['message'])
		;

		$this->get('mail.dispatcher')->send($factory->getMessage());
	}
}