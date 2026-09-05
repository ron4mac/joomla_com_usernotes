<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.6.0
*/
/** @var \RJCreations\Component\Usernotes\Site\View\Usernote\HtmlView $this */
defined('_JEXEC') or die;

//var_dump($this->item);
$cntnt = $this->item->serial_content;
$n='<h3>'.$this->item->title.'</h3>';
//file_put_contents('cntnt.txt', $cntnt);
if (preg_match_all('|___(.+?)___|sm', $cntnt, $m)) {	//echo'<xmp>';var_dump($m);echo'</xmp>';
	//$cntnt = '<div>' . implode('<br>',$m[1]) . '</div>';
	foreach ($m[1] as $prt) {
		$n .= preg_replace('|^</p>|','',trim(preg_replace('|^</p>|','',$prt)));
	}
} else {$n .= $cntnt;}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Published Note</title>
		<style>
			body {width:1872px;height:1404px;}
			p {margin:0;}
			* {margin:0;}
			ul {list-style:none;padding:0;}
			strong {font-weight:lighter;}
			#note {width:100%;height:100%;padding:1rem;box-sizing:border-box;font-size:32px;font-family:Verdana,san-serif;column-width:908px;column-fill:auto;column-gap:24px;column-rule:2px solid #ccc;}

		</style>
	</head>
	<body>
		<div id="note"><?=$n?></div>
	</body>
</html>
