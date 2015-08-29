<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<h1>User Notes Startup Screen</h1>
<div>
	<p><?=JText::_('Great!!! So you want to try this out, do you...');?></p>
	<p><?=JText::_('Please be patient and give me a few weeks to figure out what to do here.');?></p>
</div> 
<div>
	<form>
		<button type="submit"><?=JText::_('Start My Notes Collection');?></button>
		<input type="hidden" name="task" value="begin" />
	</form>
</div>