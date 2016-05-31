<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/views/view.php';

class UserNotesViewEdit extends UserNotesViewBase
{
	protected $type;
	protected $pid;
	protected $state;
	protected $params;
	protected $isecure;
	protected $ephrase;

	protected $smallDevice = false;

	public function display ($tpl = null)
	{
		$app  = JFactory::getApplication();

		// Get view related request variables.
		$this->type = $app->input->get('type','','cmd');
		$this->pid = $app->input->get('pid',0,'int');

		// Get model data.
		$m = $this->getModel();
		$this->state = $this->get('State');
		$this->isecure = $m->itemIsSecure($this->pid);
		$item = $m->getItem($app->input->get('nid',0,'int'));	//echo'<xmp>';var_dump($item);echo'</xmp>';jexit();

		// Construct the breadcrumb
		$this->buildPathway($item ? $item->itemID : $this->pid);

		if ($item && (int)$item->secured) {
			$item->title = base64_decode($item->title);
			if ($item->contentID) {
				$cookn = UserNotesHelper::hashCookieName($item->itemID, $item->contentID);
				$cookv = $app->input->cookie->getBase64($cookn);
				if ($cookv) {
					setcookie($cookn, '', time() - 3600);
					$item->ephrase = UserNotesHelper::doCrypt($item->itemID.'-@:'.$item->contentID, base64_decode($cookv), true);
				} elseif ($ephrase = $app->input->post->get('ephrase','','string')) {
					$item->ephrase = $ephrase;
				} else {
					$this->item = $item;
					return parent::display('ephrase');
				}
				$item->serial_content = UserNotesHelper::doCrypt($item->ephrase, base64_decode($item->serial_content), true);
			}
		}

		if (!$item) {
			$item = (object) array('itemID'=>0,'parentID'=>$this->pid,'contentID'=>null,'checked_out'=>null,'secured'=>($this->isecure?'1':null));
		} else {
			$m->checkOut($item->itemID);
		}
		$this->item = $item;
		$this->form = $m->getForm($item);

		if ($this->type == 'f') {
			if ($this->isecure) {
				$this->form->removeField('maksec');
			} else {
				$this->form->removeField('pissec');
			}
		}	//echo'<xmp>';var_dump($this->form);jexit();
		$this->form->setFieldAttribute('ephrase', 'type', 'password');

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		// Get the current menu item
		$this->params = $app->getParams();

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();

		return parent::display($tpl);
	}
}
