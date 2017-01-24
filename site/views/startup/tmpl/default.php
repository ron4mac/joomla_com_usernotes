<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
?>
<h1>User Notes Startup Screen</h1>
<div>
	<p><?=JText::_('Great!!! So you want to try this out, do you...');?></p>
	<p><?=JText::_('Please be patient and give me a few weeks to figure out what to do here.');?></p>
</div> 
<div>
	<form action="<?php echo JRoute::_('index.php?option=com_usernotes')?>" method="POST">
		<button type="submit"><?=JText::_('Start My Notes Collection');?></button>
		<input type="hidden" name="task" value="begin" />
	</form>
</div>