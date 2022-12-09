<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

JLoader::register('JHtmlUsernotes', JPATH_COMPONENT . '/helpers/html/usernotes.php');

class UserNotesController extends BaseController
{
	protected $default_view = 'usernotes';
	protected $instanceObj;

	public function __construct ($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
	{	//file_put_contents('REQUEST.txt',print_r($input,true),FILE_APPEND);
		parent::__construct($config, $factory, $app, $input);
		if (JDEBUG) { JLog::addLogger(['text_file'=>'com_usernotes.log.php'], JLog::ALL, ['com_usernotes']); }
		$this->instanceObj = UserNotesHelper::getInstanceObject();

		// fail if public access attempt to a 'user' instance
		if ($this->instanceObj->type == 0 && !$this->instanceObj->uid) throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);

		JHtmlUsernotes::setInstance($this->instanceObj);
	}


	public function display ($cachable = false, $urlparams = false)
	{
		if (!file_exists(UserNotesHelper::userDataPath())) {
			//set to a view that has no model
			$this->input->set('view', 'startup');
			$view = $this->getView('startup','html');
		} else {
			if ($iv = $this->input->get('view', null)) {
				$iview = $this->getView($iv,'html');
				$iview->instObj = $this->instanceObj;
//				if ($iv == 'atvue') $iview->setModel($this->getModel('usernote'), true);
			}
			$view = $this->getView('usernotes','html');
			// provide the edit model for use, as well
			$view->setModel($this->getModel('edit'));
		}
		$view->menuid = $this->instanceObj->menuid;

		return parent::display($cachable, $urlparams);
	}


	public function begin ()
	{
		if (!$this->instanceObj->uid) return;
		$htm = '<!DOCTYPE html><title></title>';
		$udp = UserNotesHelper::userDataPath();
		mkdir($udp.'/attach', 0777, true);
		file_put_contents($udp.'/index.html', $htm);
		file_put_contents($udp.'/attach/index.html', $htm);
		$this->setRedirect(Route::_('index.php?option=com_usernotes&Itemid='.$this->instanceObj->menuid, false));
	}


	public function search ()
	{
		$view = $this->getView('search', 'html');
		$view->setModel($this->getModel('usernotes'), true);
		$view->display();
	}


	public function printNote ()
	{
		$this->input->set('tmpl','component');
		$view = $this->getView('usernote', 'html');
		$view->setModel($this->getModel('usernote'), true);
		$view->display();
	}


/** ajax calls **/

	public function addRating ()
	{
		$rate = $this->input->post->getInt('rate', 0);
		// don't let unauthorized users cause a ratings reset
		if ($rate == 0 && UserNotesHelper::userAuth() < 2) die(json_encode(['err'=>'NOT AUTHORIZED']));
		// add the rating to the item
		$iid = $this->input->post->getInt('iID', 0);
		$m = $this->getModel('usernote');
		echo json_encode($m->addRating($iid, $rate));
	}

/** end ajax calls **/

}
