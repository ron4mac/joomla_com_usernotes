<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

?>

<?php if ($this->auth > 1): ?>
<h1>User Notes Startup Screen</h1>
<div>
	<p><?=Text::_('COM_USERNOTES_START1');?></p>
	<p><?=sprintf(Text::_('COM_USERNOTES_START2'), $this->quota, $this->maxfs);?></p>
</div> 
<div>
	<form action="<?=Route::_('index.php?option=com_usernotes&Itemid='.$this->itemId, false)?>" method="POST">
		<button type="submit"><?=Text::_('Start My Notes Collection');?></button>
		<input type="hidden" name="task" value="begin" />
	</form>
</div>
<?php else: ?>
<h3><?=Text::_('This notes collection has not yet been initiated');?></h3>
<?php endif; ?>
