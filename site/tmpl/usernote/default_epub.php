<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2026 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.5
*/
defined('_JEXEC') or die;

function sendParts ($html)
{
	// Suppress potential HTML parsing errors for malformed HTML
	libxml_use_internal_errors(TRUE); 

	$dom = new DOMDocument();
	$dom->loadHTML('<?xml encoding="UTF-8">' . trim($html,'<br>'));

	// Clear errors to avoid memory issues
	libxml_clear_errors(); 

	// get title element
	$title = $dom->getElementsByTagName('h3')[0];
//	echo "<h3>{$title->textContent}</h3>";
	$note = $dom->getElementById('note');
//	echo '<div class="newspaper">'.$dom->saveHTML($note).'</div>';	//$note->textContent;
	$n = $dom->saveHTML($note);	//$note->textContent;
			while (str_ends_with($n,'<br>')) { $n = substr($c, 0, -4); };
			while (str_starts_with($n,'<br>')) { $n = substr($c, 4); };
	file_put_contents('htm.txt', $n);
	if (preg_match_all('|___(.+?)___|', $n, $m)) {
	//	echo'<pre>';var_dump($m);echo'</pre>';
		$n = '<div id="note">';
		for ($i=0; $i<count($m[1]); $i++) {
			$c = $m[1][$i];
		//	while (str_ends_with($c,'<br>')) { $c = substr($c, 0, -4); };
			while (str_starts_with($c,'<br>')) { $c = substr($c, 4); };
			$n .= $c;
		}
		$n .= '</div>';
	} else {$n .= '@@NOPE@@';}
	return /*"<h3>{$title->textContent}</h3>".*/$n;
}

$cntnt = $this->item->serial_content;
$n='';
//file_put_contents('cntnt.txt', $cntnt);
if (preg_match_all('|___(.+?)___|sm', $cntnt, $m)) {	//echo'<xmp>';var_dump($m);echo'</xmp>';
	//$cntnt = '<div>' . implode('<br>',$m[1]) . '</div>';
	foreach ($m[1] as $prt) {
		$n .= preg_replace('|^</p>|','',trim(preg_replace('|^</p>|','',$prt)));
	}
} else {$cntnt = '@@NOPE@@';}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Published Note</title>
		<style>
			body {width:800px;height:480px;margin:8px;}
			p {margin:0;}
			#note {width:100%;height:480px;column-count:2;font-size:16px;font-family:Helvetica,san-serif;}
		</style>
	</head>
	<body>
		<div id="note"><?=$n?></div>
	</body>
</html>
