<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;

class Form extends Controller
{
	/**
	 * Renders a form where a user can report a broken link to the website
	 * owner.
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function brokenLinkForm()
	{
		return $this->render('Message:Mothership:CMS::modules/broken_link', array(
			'form' => $this->_getBrokenLinkForm(),
		));
	}

	public function brokenLinkAction()
	{
		$form = $this->_getBrokenLinkForm();

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$host    = $this->get('request')->getHost();
			$message = $this->get('mail.message');

			$message->setSubject('Broken link on ' . $host);
			$message->setTo($this->get('cfg')->app->defaultContactEmail, $this->get('cfg')->app->name);
			$message->setView('::mail/broken-link', array(
				'message' => $data['message'],
				'uri'     => $data['broken_uri'],
				'host'    => $host,
			));

			$this->get('mail.dispatcher')->send($message);
			$this->addFlash('success', 'Thank you for reporting this broken link.');
		}

		return $this->redirectToReferer();
	}

	public function _getBrokenLinkForm()
	{
		$form = $this->get('form')
			->setName('broken_link')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cms.broken_link.action'));

		$form->add('message', 'textarea', $this->trans('ms.cms.broken_link.message.label'))
			->val()->optional();

		$form->add('broken_uri', 'hidden', NULL , array(
			'data' => $this->get('http.request.master')->getUri(),
		));

		return $form;
	}
}
