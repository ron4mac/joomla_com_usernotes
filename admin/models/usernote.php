<?php
/**
 * @package    com_usernotes
 *
 * @copyright  Copyright (C) 2016 RJCreations - All rights reserved.
 * @license    GNU General Public License version 3 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('UserNotesHelper', JPATH_ADMINISTRATOR . '/components/com_usernotes/helpers/usernotes.php');

/**
 * UserNote model.
 *
 * @since  1.6
 */
class UserNotesModelUserNote extends JModelAdmin
{

	public $typeAlias = 'com_usernotes.usernote';

	protected $text_prefix = 'COM_USERNOTES';

	protected function batchCopy ($value, $pks, $contexts)
	{
		$categoryId = (int) $value;

		$newIds = array();

		if (!parent::checkCategoryId($categoryId)) {
			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($pks)) {
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// Check that the row actually exists
			if (!$this->table->load($pk)) {
				if ($error = $this->table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				} else {
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			$data = $this->generateNewTitle($categoryId, $this->table->alias, $this->table->name);
			$this->table->name = $data['0'];
			$this->table->alias = $data['1'];

			// Reset the ID because we are making a copy
			$this->table->id = 0;

			// Unpublish because we are making a copy
			$this->table->published = 0;

			// New category ID
			$this->table->catid = $categoryId;

			// TODO: Deal with ordering?
			// $this->table->ordering	= 1;

			// Check the row.
			if (!$this->table->check()) {
				$this->setError($this->table->getError());
				return false;
			}

			parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

			// Store the row.
			if (!$this->table->store()) {
				$this->setError($this->table->getError());
				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			// Add the new ID to the array
			$newIds[$pk] = $newId;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}


	protected function canDelete ($record)
	{
		if (!empty($record->id)) {
			if ($record->published != -2) {
				return false;
			}

			$user = JFactory::getUser();

			if (!empty($record->catid)) {
				return $user->authorise('core.delete', 'com_usernote.category.' . (int) $record->catid);
			} else {
				return parent::canDelete($record);
			}
		}
		return false;
	}


	protected function canEditState ($record)
	{
		$user = JFactory::getUser();

		if (!empty($record->catid)) {
			return $user->authorise('core.edit.state', 'com_usernotes.category.' . (int) $record->catid);
		} else {
			return parent::canEditState($record);
		}
	}


	public function getTable ($type = 'UserNote', $prefix = 'UserNotesTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}


	public function getForm ($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_usernotes.usernote', 'usernote', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		// Determine correct permissions to check.
		if ($this->getState('usernote.id')) {
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
		} else {
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data)) {
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
		}

		return $form;
	}


	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_usernotes.edit.usernote.data', array());

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('usernote.id') == 0) {
				$app = JFactory::getApplication();
				$data->set('catid', $app->input->get('catid', $app->getUserState('com_usernotes.usernotes.filter.category_id'), 'int'));
			}
		}

		$this->preprocessData('com_usernotes.usernote', $data);

		return $data;
	}


	public function save ($data)
	{
		$input = JFactory::getApplication()->input;

		// Alter the name for save as copy
		if ($input->get('task') == 'save2copy') {
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['name'] == $origTable->name) {
				list($name, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['name']);
				$data['name'] = $name;
				$data['alias'] = $alias;
			} else {
				if ($data['alias'] == $origTable->alias) {
					$data['alias'] = '';
				}
			}
			$data['published'] = 0;
		}

		if (parent::save($data)) {

			$assoc = JLanguageAssociations::isEnabled();
			if ($assoc) {
				$id = (int) $this->getState($this->getName() . '.id');
				$item = $this->getItem($id);

				// Adding self to the association
				$associations = $data['associations'];

				foreach ($associations as $tag => $id) {
					if (empty($id)) {
						unset($associations[$tag]);
					}
				}

				// Detecting all item menus
				$all_language = $item->language == '*';

				if ($all_language && !empty($associations)) {
					JError::raiseNotice(403, JText::_('COM_USERNOTES_ERROR_ALL_LANGUAGE_ASSOCIATED'));
				}

				$associations[$item->language] = $item->id;

				// Deleting old association for these items
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete('#__associations')
					->where($db->quoteName('context') . ' = ' . $db->quote('com_usernotes.item'))
					->where($db->quoteName('id') . ' IN (' . implode(',', $associations) . ')');
				$db->setQuery($query);
				$db->execute();

				if ($error = $db->getErrorMsg()) {
					$this->setError($error);
					return false;
				}

				if (!$all_language && count($associations)) {
					// Adding new association for these items
					$key = md5(json_encode($associations));
					$query->clear()->insert('#__associations');

					foreach ($associations as $id) {
						$query->values($id . ',' . $db->quote('com_usernotes.item') . ',' . $db->quote($key));
					}

					$db->setQuery($query);
					$db->execute();

					if ($error = $db->getErrorMsg()) {
						$this->setError($error);
						return false;
					}
				}
			}

			return true;
		}

		return false;
	}


	public function getItem ($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			$registry = new Registry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();

			// Convert the images field to an array.
			$registry = new Registry;
			$registry->loadString($item->images);
			$item->images = $registry->toArray();
		}

		// Load associated usernotes items
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc) {
			$item->associations = array();
			if ($item->id != null) {
				$associations = JLanguageAssociations::getAssociations('com_usernotes', '#__usernotes', 'com_usernotes.item', $item->id);
				foreach ($associations as $tag => $association) {
					$item->associations[$tag] = $association->id;
				}
			}
		}

		if (!empty($item->id)) {
			$item->tags = new JHelperTags;
			$item->tags->getTagIds($item->id, 'com_usernotes.usernote');
			$item->metadata['tags'] = $item->tags;
		}

		return $item;
	}


	protected function prepareTable ($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
		$table->alias = JApplication::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->name);
		}

		if (empty($table->id)) {
			// Set the values
			$table->created = $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('MAX(ordering)')
					->from($db->quoteName('#__usernotes'));
				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		} else {
			// Set the values
			$table->modified = $date->toSql();
			$table->modified_by = $user->get('id');
		}

		// Increment the content version number.
		$table->version++;
	}


	public function publish (&$pks, $value = 1)
	{
		$result = parent::publish($pks, $value);

		// Clean extra cache for usernotes
		$this->cleanCache('feed_parser');

		return $result;
	}


	protected function getReorderConditions ($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;
		return $condition;
	}


	protected function preprocessForm (JForm $form, $data, $group = 'content')
	{
		// Association usernotes items
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();
		if ($assoc) {
			$languages = JLanguageHelper::getLanguages('lang_code');
			$addform = new SimpleXMLElement('<form />');
			$fields = $addform->addChild('fields');
			$fields->addAttribute('name', 'associations');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'item_associations');
			$fieldset->addAttribute('description', 'COM_USERNOTES_ITEM_ASSOCIATIONS_FIELDSET_DESC');
			$add = false;

			foreach ($languages as $tag => $language) {
				if (empty($data->language) || $tag != $data->language) {
					$add = true;
					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $tag);
					$field->addAttribute('type', 'modal_usernote');
					$field->addAttribute('language', $tag);
					$field->addAttribute('label', $language->title);
					$field->addAttribute('translate_label', 'false');
					$field->addAttribute('edit', 'true');
					$field->addAttribute('clear', 'true');
				}
			}

			if ($add) {
				$form->load($addform, false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}


	protected function generateNewTitle($category_id, $alias, $name)
	{
		// Alter the title & alias
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias, 'catid' => $category_id))) {
			if ($name == $table->name) {
				$name = JString::increment($name);
			}
			$alias = JString::increment($alias, 'dash');
		}
		return array($name, $alias);
	}

}
