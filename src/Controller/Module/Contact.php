<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;

class Contact extends Controller
{
	public function contact()
	{
		$form = $this->createForm($this->get('form.contact'));
		$form->handleRequest();

		if ($form->isValid()) {
			$data = $form->getData();
			$this->_sendEmail($data);
			$this->addFlash('success', $this->trans('ms.cms.contact.success'));
		}

		return $this->render('Message:Mothership:CMS::modules:contact', [
			'form' => $form,
		]);
	}

	protected function _sendEmail(array $data)
	{
		$factory = $this->get('mail.factory.contact')
			->set('email', $data['email'])
			->set('name', $data['name'])
			->set('message', $data['message'])
		;

		$this->get('mail.dispatcher')->send($factory->getMessage());
	}
}