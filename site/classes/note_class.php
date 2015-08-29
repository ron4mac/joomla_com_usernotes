<?php
// this class is needed to access content data in legacy "oopim" serialized format
// it would be able to go away when all content is converted
class Note_model
{
	public $rawtxt = '';
	public $format = 0;

    public function __construct ($raw=NULL,$fmt=0)
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
