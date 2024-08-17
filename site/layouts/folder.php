<?php
/**
* @package		com_usernotes
* @copyright	Copyright (C) 2015-2024 RJCreations. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
* @since		1.5.0
*/
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use RJCreations\Component\Usernotes\Site\Helper\M34C;

extract($displayData);	//view,vitm

if ((int)JVERSION<4) {
	HTMLHelper::_('behavior.formvalidator');
} else {
	$wa = $view->document->getWebAssetManager();
	$wa->useScript('keepalive')->useScript('form.validate');
}

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
//		$m->checkOut($vitm->itemID);
		if ($vitm->secured) $vitm->title = base64_decode($vitm->title);
	}		//echo'<xmp>';var_dump($vitm);echo'</xmp>';
	$form = $m->getForm($vitm, $ed);
	$frhtm = HTMLHelper::_('form.token');
	foreach ($form->getFieldset() as $field) {
		// dont show make_secure when is edit or new folder in a secure folder
		if ($field->fieldname == 'maksec' && ($ed || $vitm->secured)) continue;
		// only use this field if the parent folder is secured
		if (!$vitm->secured && $field->fieldname == 'pissec') continue;
		$frhtm .= $form->renderField($field->fieldname);
	}
	return $frhtm;
}

$instlink = Route::_('index.php?option=com_usernotes&Itemid='.$view->menuid, false);

if (!empty($vitm->itemID)) echo HTMLHelper::_(
	'bootstrap.renderModal',
	'foldered-modal', // selector
	array( // options
		'title'  => Text::_('COM_USERNOTES_EDIT_FORM_EDIT_F'),
		'footer' => '<button type="button" class="btn btn-secondary" '.M34C::bs('dismiss').'="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="UNote.saveFolder(this)">Save Folder</button>',
	//	'modalWidth' => 20
	),
	'<form id="un_edtfold" method="POST" class="form-validate" onsubmit="return false">' . un_formGet($view, $vitm, true) . '</form>'
);
echo HTMLHelper::_(
	'bootstrap.renderModal',
	'foldercr-modal', // selector
	array( // options
		'title'  => Text::_('COM_USERNOTES_EDIT_FORM_CREATE_F'),
		'footer' => '<button type="button" class="btn btn-secondary" '.M34C::bs('dismiss').'="modal">Close</button>
					<button type="button" class="btn btn-primary" onclick="UNote.newFolder(event, this)">Create Folder</button>',
	//	'modalWidth' => 20
	),
	'<form id="un_newfold" method="POST" class="form-validate" onsubmit="return false">' . un_formGet($view, $vitm) . '</form>'
);
