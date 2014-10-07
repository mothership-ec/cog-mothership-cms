<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;

class Contact extends Controller
{
	const SESSION_NAME  = 'ms.cms.contact_form_data';
	const CAPTCHA_FIELD = 'captcha';

	public function contact()
	{
		$form = $this->createForm(
			$this->get('form.contact'),
			$this->_getDataFromSession(),
			['action' => $this->generateUrl('ms.cms.contact.action')]
		);

		return $this->render('Message:Mothership:CMS::modules:contact', [
			'form' => $form,
		]);
	}

	public function contactAction()
	{
		$form = $this->createForm($this->get('form.contact'));

		$form->handleRequest();
		$data = $form->getData();

		if ($form->isValid()) {
			$this->_sendEmail($data);
			$this->addFlash('success', $this->trans('ms.cms.contact.success'));
			$this->get('http.session')->remove(self::SESSION_NAME);
		}
		else {
			$this->get('http.session')->set(self::SESSION_NAME, $data);
		}

		return $this->redirectToReferer();
	}

	private function _getDataFromSession()
	{
		$data = $this->get('http.session')->get(self::SESSION_NAME);

		if (!$data) {
			return null;
		}

		if (array_key_exists(self::CAPTCHA_FIELD, $data)) {
			unset($data[self::CAPTCHA_FIELD]);
		}

		return $data;
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