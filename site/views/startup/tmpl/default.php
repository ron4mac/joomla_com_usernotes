<?php
/**
 * @package    com_usernotes
 * @copyright  Copyright (C) 2016-2021 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

//if (RJC_DBUG) echo '<div>'.$this->instance.'</div>';
?>

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
