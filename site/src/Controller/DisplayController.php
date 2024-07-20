<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

\JLoader::register('RJUserCom', JPATH_LIBRARIES . '/rjuser/com.php');
\JLoader::register('HtmlUsernotes', JPATH_COMPONENT . '/helpers/html/usernotes.php');
\JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');
\JLoader::register('M34C', JPATH_COMPONENT.'/helpers/m34c.php');
\JLoader::register('UsernotesViewBase', JPATH_COMPONENT.'/src/View/view.php');

//require_once JPATH_BASE . '/components/com_usernotes/src/View/view.php';

define('RJC_DBUG', (true || JDEBUG) && file_exists(JPATH_ROOT.'/rjcdev.php'));

class DisplayController extends BaseController
{
	protected $default_view = 'usernotes';
	protected $instanceObj;

	public function __construct ($config = [], $factory = null, $app = null, $input = null)
	{	//file_put_contents('REQUEST.txt',print_r($input,true),FILE_APPEND);
		parent::__construct($config, $factory, $app, $input);
		if (false && JDEBUG) { JLog::addLogger(['text_file'=>'com_usernotes.log.php'], JLog::ALL, ['com_usernotes']); }
		$this->instanceObj = \RJUserCom::getInstObject();

		// fail if public access attempt to a 'user' instance
		if ($this->instanceObj->type == 0 && !$this->instanceObj->uid) throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);

		\HtmlUsernotes::setInstance($this->instanceObj);
	}


	public function display ($cachable = false, $urlparams = false)
	{
		if (!file_exists(\RJUserCom::getStoragePath())) {
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
		$udp = \RJUserCom::getStoragePath();
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


}
