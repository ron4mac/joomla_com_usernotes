<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.6.0
*/
namespace RJCreations\Component\Usernotes\Site\View\Atvue;

defined('_JEXEC') or die('Restricted access');
 
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use RJCreations\Library\RJUserCom;
use RJCreations\Component\Usernotes\Site\Helper\FileEncrypt;
use RJCreations\Component\Usernotes\Administrator\Helper\UsernotesHelper;

class HtmlView extends BaseHtmlView
{
	protected $fnam;
	protected $fpath;
	protected $mime;
	protected $down = false;

	public function display ($tpl = null)
	{
		$app = Factory::getApplication();
		$input = $app->getInput();
		$this->jDoc = Factory::getDocument();

		// Get view related request variables.
		$this->down = $input->get('down',0,'int');
		$cat = explode('|',$input->getString('cat'),3);
		$this->fnam = $cat[2];

		$m = $this->getModel();
		$this->isecure = $m->itemIsSecure($cat[0]);
		if ($this->isecure) {
			$cookn = UsernotesHelper::hashCookieName(RJUserCom::getInstObject(), $cat[0], $cat[1]);
			$cookv = $input->cookie->getBase64($cookn);
			$this->key = UsernotesHelper::doCrypt($cat[0].'-@:'.$cat[1], $cookv, true);
		}

		// Get path to file
		$udp = RJUserCom::getStoragePath();
		$this->fpath = JPATH_BASE.'/'.$udp.'/attach/'.$cat[1].'/'.$cat[2];
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$this->mtype = finfo_file($finfo, $this->fpath);

		$this->attProps = $m->atFileProps($cat[1],$cat[2]);
		// resolve actual file size
		$this->fsize = $this->attProps->ucfs ?: $this->attProps->fsize - ($this->isecure ? 	FileEncrypt::fsOverhead() : 0);

		return parent::display($tpl);
	}


}
