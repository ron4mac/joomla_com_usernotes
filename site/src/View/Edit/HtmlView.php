<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.6.0
*/
namespace RJCreations\Component\Usernotes\Site\View\Edit;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use RJCreations\Library\RJUserCom;
use RJCreations\Component\Usernotes\Site\View\ViewBase;
use RJCreations\Component\Usernotes\Site\Model\EditModel;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

class HtmlView extends ViewBase
{
	public $form;
	public $pageclass_sfx;

	protected $type;
	protected $pid;
	protected $state;
	protected $params;
	protected $isecure;
	protected $ephrase;

	protected $smallDevice = false;

	// use alternate css
	protected $usecss = 'unote';
	// use NO js
//	protected $usejs = [];


	public function display ($tpl = null)
	{
		/** @var EditModel $model */
		$m = $this->getModel();

		$app = Factory::getApplication();
		$input = $app->getInput();

		// Get view related request variables.
		$this->type = $input->get('type','','cmd');
		$this->pid = $input->get('pid',0,'int');

		// Get model data.
		$this->state = $m->getState();
		$this->isecure = $m->itemIsSecure($this->pid);
		$item = $m->getItem($input->get('nid',0,'int'));

		// Construct the breadcrumb
		$this->buildPathway($item ? $item->itemID : $this->pid);

		if ($item && (int)$item->secured) {
			$item->title = base64_decode($item->title);
			if ($item->contentID) {
				$cookn = UsernotesHelper::hashCookieName(RJUserCom::getInstObject(), $item->itemID, $item->contentID);
				$cookv = $input->cookie->getBase64($cookn);
				if ($cookv) {
					setcookie($cookn, '', ['expires' => time() - 3600]);
					$item->ephrase = UsernotesHelper::doCrypt($item->itemID.'-@:'.$item->contentID, $cookv, true);
				} elseif ($ephrase = $input->post->get('ephrase','','string')) {
					$item->ephrase = $ephrase;
				} else {
					$this->item = $item;
					return parent::display('nocando');
				}
				$item->serial_content = UsernotesHelper::doCrypt($item->ephrase, $item->serial_content, true, (int)$item->secured);
			}
		}

		if (!$item) {
			$item = (object) ['itemID'=>0,'parentID'=>$this->pid,'contentID'=>null,'checked_out'=>null,'secured'=>($this->isecure?'1':null)];
		} else {
			$m->checkOut($item->itemID);
		}
		$this->item = $item;
		$this->form = $m->getForm($item);

		if ($this->type == 'f') {
			if ($this->isecure) {
				$this->form->removeField('maksec');
			} elseif (!(int)$item->secured) {
				$this->form->removeField('pissec');
			}
		}
		$this->form->setFieldAttribute('ephrase', 'type', 'password');

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $m->getErrors())) {
			throw new Exception(implode("\n", $errors), 500);
		}

		// Get the current menu item
		$this->params = $app->getParams();

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', ''));

		$this->_prepareDocument();

		return parent::display($tpl);
	}


}
