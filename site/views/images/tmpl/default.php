<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

echo'<xmp>';var_dump($this->params,$this->state,$this->user);echo'</xmp>';
?>
<h1>User Notes Startup Screen</h1>
<div>
	<p>Great!!! So you want to try this out, do you...</p>
	<p>Please be patient and give me a few weeks to figure out what to do here.</p>
</div> 
<div>
	<form>
		<button type="submit">Start My Gallery</button>
		<input type="hidden" name="task" value="begin" />
	</form>
</div>