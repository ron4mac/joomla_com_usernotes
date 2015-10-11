<?php
defined('_JEXEC') or die;

class UserNotesControllerUserNotes extends JControllerAdmin
{

	public function getModel($name = 'UserNote', $prefix = 'UserNotesModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	protected function postDeleteHook(JModelLegacy $model, $ids = null)
	{
	}

}