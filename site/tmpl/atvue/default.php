<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2025 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.1
*/
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

if (file_exists($this->fpath)) {
	if ($this->down) {
		$this->jDoc->setMimeEncoding('application/download');
		header("Pragma: public");
		header("Expires: 0"); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Cache-Control: private",false); // required for certain browsers 
		header('Content-Disposition: attachment; filename="'.$this->fnam.'"');
		header("Content-Transfer-Encoding: binary");
	} else {
		$this->jDoc->setMimeEncoding($this->attProps->mtype ?: $this->mtype);
	}
	header('Content-Length: '.$this->fsize);
	if (JDEBUG) {
		$hdmp = print_r(headers_list(), true);
		JLog::add("download headers: {$hdmp}", JLog::INFO, 'com_usernotes');
	}
	if ($this->isecure) {
		$ucfs = $this->attProps->ucfs;
		\UserNotesFileEncrypt::output($this->key, $this->fpath, !is_null($ucfs));
		flush();
	} else {
		if ($this->attProps->ucfs) {
			readgzfile($this->fpath);
		} else {
			readfile($this->fpath);
		}
	}
} else {
	if ($this->down) {
		echo '<script>alert("'.Text::_('COM_USERNOTES_NO_FILE').'")</script>';
	} else {
		echo '<p style="font-size:1.5em">'.Text::_('COM_USERNOTES_NO_FILE').'</p>';
	}
}
