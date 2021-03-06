<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
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
		$this->jDoc->setMimeEncoding($this->mime);
	}
	header('Content-Length: '.filesize($this->fpath));
	if (JDEBUG) {
		$hdmp = print_r(headers_list(), true);
		JLog::add("download headers: {$hdmp}", JLog::INFO, 'com_usernotes');
	}
	readfile($this->fpath);
} else {
	if ($this->down) {
		echo '<script>alert("'.Text::_('Not able to access this file for download.').'")</script>';
	} else {
		echo '<p style="font-size:1.5em">'.Text::_('Not able to access this file.').'</p>';
	}
}
