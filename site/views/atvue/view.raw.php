<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
 
use Joomla\CMS\Factory;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UsernotesViewAtvue extends JViewLegacy
{
	protected $fnam;
	protected $fpath;
	protected $mime;
	protected $down = false;


	public function display ($tpl = null)
	{
		$app = Factory::getApplication();
		$this->jDoc = Factory::getDocument();

		// Get view related request variables.
		$this->down = $app->input->get('down',0,'int');
		$cat = explode('|',$app->input->getString('cat'),2);
		$this->fnam = $cat[1];

		// Get path to file
		$udp = UserNotesHelper::userDataPath();
		$this->fpath = JPATH_BASE.'/'.$udp.'/attach/'.$cat[0].'/'.$cat[1];

		// Get file mime type
		if (file_exists($this->fpath)) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$this->mime = finfo_file($finfo, $this->fpath);
		}

		return parent::display($tpl);
	}


}
