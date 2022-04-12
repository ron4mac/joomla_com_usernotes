<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2022 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;

function un_formGet ($view, $vitm, $ed=false)
{
		$app = Factory::getApplication();
		$input = $app->input;
		$input->set('type','f');
//		echo'<xmp>';var_dump($vitm);echo'</xmp>';
	$m = $view->getModel('edit');
	if (!$ed) {
		$pid = empty($vitm->itemID) ? 0 : $vitm->itemID;
		$vitm = (object) ['itemID'=>0,'parentID'=>$pid,'contentID'=>null,'checked_out'=>null,'secured'=>($vitm->secured?'1':null)];
	} else {
		$m->checkOut($vitm->itemID);
		if ($vitm->secured) $vitm->title = base64_decode($vitm->title);
	}		//echo'<xmp>';var_dump($vitm);echo'</xmp>';
	$form = $m->getForm($vitm, $ed);
	$frhtm = HTMLHelper::_('form.token');
	foreach ($form->getFieldset() as $field) {
		if ($ed && $field->fieldname == 'maksec') continue;
		if (!$vitm->secured && $field->fieldname == 'pissec') continue;
		$frhtm .= $form->renderField($field->fieldname);
	}
	return $frhtm;
}

if (!empty($displayData['vitm']->itemID)) echo HTMLHelper::_(
	'bootstrap.renderModal',
	'foldered-modal', // selector
	array( // options
		'title'  => Text::_('COM_USERNOTES_EDIT_FORM_EDIT_F'),
		'footer' => '<button type="button" class="btn btn-secondary" '.M34C::bs('dismiss').'="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="UNote.saveFolder(this)">Save Folder</button>',
		'modalWidth' => 30
	),
	'<form id="un_edtfold">' . un_formGet($displayData['view'], $displayData['vitm'], true) . '</form>'
);
echo HTMLHelper::_(
	'bootstrap.renderModal',
	'foldercr-modal', // selector
	array( // options
		'title'  => Text::_('COM_USERNOTES_EDIT_FORM_CREATE_F'),
		'footer' => '<button type="button" class="btn btn-secondary" '.M34C::bs('dismiss').'="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="UNote.newFolder(event, this)">Create Folder</button>',
		'modalWidth' => 30
	),
	'<form id="un_newfold">' . un_formGet($displayData['view'], $displayData['vitm']) . '</form>'
);
