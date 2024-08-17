<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
namespace RJCreations\Component\Usernotes\Site\View\Atvue;

defined('_JEXEC') or die('Restricted access');
 
use Joomla\CMS\Factory;
use RJCreations\Library\RJUserCom;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

//\JLoader::register('UserNotesFileEncrypt', JPATH_COMPONENT.'/classes/file_encrypt.php');

class HtmlView extends \Joomla\CMS\MVC\View\HtmlView
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
		$cat = explode('|',$app->input->getString('cat'),3);
		$this->fnam = $cat[2];

		$m = $this->getModel();
		$this->isecure = $m->itemIsSecure($cat[0]);
		if ($this->isecure) {
			$cookn = UsernotesHelper::hashCookieName(RJUserCom::getInstObject(), $cat[0], $cat[1]);
			$cookv = $app->input->cookie->getBase64($cookn);
			$this->key = UsernotesHelper::doCrypt($cat[0].'-@:'.$cat[1], $cookv, true);
		}

		// Get path to file
		$udp = RJUserCom::getStoragePath();
		$this->fpath = JPATH_BASE.'/'.$udp.'/attach/'.$cat[1].'/'.$cat[2];
		$this->fsize = filesize($this->fpath);
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$this->mtype = finfo_file($finfo, $this->fpath);

		$this->attProps = $m->atFileProps($cat[1],$cat[2]);

		return parent::display($tpl);
	}


}
