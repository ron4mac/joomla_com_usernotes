<?php
/**
 * @package			com_usernotes
 * @copyright		Copyright (C) 2016 RJCreations - All rights reserved.
 * @license			GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// this class is needed to access content data in legacy "oopim" serialized format
// it would be able to go away when all content is converted
class Note_model
{
	public $rawtxt = '';
	public $format = 0;

	public function __construct ($raw = NULL, $fmt = 0)
	{
		if ($raw)
			$this->rawtxt = $raw;
		if ($fmt)
			$this->format = $fmt;
	}

	public function rendered ()
	{
		switch ($this->format) {
			case 0:	//plain
				return $this->withHtml($this->rawtxt);
				break;
			case 2:	//richtext
				return $this->rawtxt;
				break;
			default:
				return '<pre>'.$this->rawtxt.'</pre>';
		}
	}

	public function rawdata ()
	{
		return $this->rawtxt;
	}

	private function withHtml ($txt)
	{
		$eol = ( strpos($txt,"\r") === FALSE ) ? "\n" : "\r\n";
		$html = '<p>'.str_replace("$eol$eol","</p><p>",$txt).'</p>';
		$html = str_replace("$eol","<br />\n",$html);
		$html = str_replace("</p>","</p>\n\n",$html);
		$html = str_replace("<p></p>","<p>&nbsp;</p>",$html);
		return $html;
	}
}

class Secured_model
{

	public $rawtxt = '';
	public $ephrase = '';

	function __construct ($raw = NULL)
	{
		if ($raw) {
			$ephrase = JFactory::getApplication()->input->post->get('ephrase','','string');
			if (!$ephrase) return;
			$uudat = convert_uuencode($this->doCrypt(false,$ephrase,$raw));
			$this->rawtxt = $uudat;
		}
	}

	function rawdata ()
	{
		return $this->rawtxt;
	}

	function rendered ($addHtml = false)
	{
		$this->ephrase = JFactory::getApplication()->input->post->get('ephrase','','string');	//var_dump($this->ephrase);
		if (!$this->ephrase) return '';
		$odat = $this->doCrypt(true,$this->ephrase,convert_uudecode($this->rawtxt));
		if (!$addHtml) return $odat;
		return $this->withHtml($odat);
		return '<pre>'.$odat.'</pre>';
	}

	private function doCrypt ($de, $pass, $dat)
	{
		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_RANDOM);
		$ks = mcrypt_enc_get_key_size($td);
		$key = substr($pass, 0, $ks);
		mcrypt_generic_init($td, $key, $iv);
		if ($de) { $retdat = trim(mdecrypt_generic($td, $dat)); }
		else { $retdat = mcrypt_generic($td, $dat); }
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $retdat;
	}

}
