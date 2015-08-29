<?php
defined('_JEXEC') or die;

include_once JPATH_COMPONENT.'/classes/note_class.php';

use Joomla\Registry\Registry;

JLoader::register('UserNotesHelper', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/usernotes.php');

class UserNotesModelUserNote extends JModelItem
{
	protected $_context = 'com_usernotes.usernote';
	protected $_item = null;	// use for cache

	public function __construct ($config = array())
	{
		$udbPath = UserNotesHelper::userDataPath().'/usernotes.db3';
		$db = JDatabaseDriver::getInstance(array('driver'=>'sqlite', 'database'=>$udbPath));

		$config['dbo'] = $db;
		parent::__construct($config);
	}

	public function buildPathway ($to, $extra=null)
	{
		$pw = JFactory::getApplication()->getPathWay();
	//	$db = parent::getDBO();
		$db = $this->getDbo();
		$crums = array();
		while ($to) {
			$db->setQuery('SELECT title,isParent,parentID FROM notes WHERE itemID='.$to);
			$r = $db->loadAssoc();
			$cpth = $r['isParent'] ? ('index.php?option=com_usernotes&pid='.$to) : '';
			array_unshift($crums, array($r['title'],$cpth));
			$to = $r['parentID'];
		}
		foreach ($crums as $crum) {
			$pw->addItem($crum[0],$crum[1]);
		}
		if ($extra) $pw->addItem($extra[0],$extra[1]);
	}

	public function &getItem ($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('usernote.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('n.*, c.serial_content, a.attached')
					->from('notes AS n')
					->join('LEFT', 'content AS c on c.contentID = n.contentID')
					->join('LEFT', 'attach AS a on a.contentID = n.contentID')
					->where('n.itemID = ' . (int) $pk);

				$db->setQuery($query);		//echo'<pre>';var_dump($db->getQuery());echo'</pre>';

				$data = $db->loadObject();		//echo'<xmp>';var_dump($data);echo'</xmp>';

				if (empty($data)) {
					JError::raiseError(404, JText::_('COM_USERNOTES_ERROR_FEED_NOT_FOUND'));
				} else {
					if ($nm = @unserialize($data->serial_content)) {
						$data->serial_content = $nm->rendered();
					}
				}

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}

	public function storeNote ($note, $user)
	{
		try
		{
			$db = $this->getDbo();
			if ($note['itemID']) {
				$q = $db->getQuery(true);
				$q->update('content')->set('serial_content='.$db->quote($note['serial_content']))->where('contentID='.$note['contentID']);
				$db->setQuery($q);
				$db->execute();
				$q = $db->getQuery(true);
				$q->update('notes')->set('title='.$db->quote($note['title']))->where('itemID='.$note['itemID']);
				$db->setQuery($q);
				$db->execute();
			} else {
				$q = $db->getQuery(true);
				$q->insert('content')->columns('serial_content')->values($db->quote($note['serial_content']));
				$db->setQuery($q);
				$db->execute();
				$cid = $db->insertid();
				$q = $db->getQuery(true);
				$q->insert('notes')->columns('ownerID,shared,isParent,title,contentID,parentID')
									->values(implode(',',array($user,1,0,$db->quote($note['title']),$cid,$note['parentID'])));
				$db->setQuery($q);
				$db->execute();
			}
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
	}

	public function storeFolder ($fold, $user)
	{
		try
		{
			$db = $this->getDbo();
			if ($fold['itemID']) {
				$q = $db->getQuery(true);
				$q->update('notes')->set('title='.$db->quote($fold['title']))->where('itemID='.$fold['itemID']);
				$db->setQuery($q);
				$db->execute();
			} else {
				$q = $db->getQuery(true);
				$q->insert('notes')->columns('ownerID,shared,isParent,title,contentID,parentID')
									->values(implode(',',array($user,1,1,$db->quote($fold['title']),0,$fold['pid'])));
				$db->setQuery($q);
				$db->execute();
			}
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
	}

	public function deleteItem ($iid)
	{
		try
		{
			$db = $this->getDbo();
			$q = $db->getQuery(true);
			$q->select('contentID,isParent,parentID')->from('notes')->where('itemID='.$iid);
			$db->setQuery($q);
			$itm = $db->loadObject();
			if ($itm->isParent) {
				$this->deleteFolder($iid);
			} else {
				$q = $db->getQuery(true);
				$q->delete('content')->where('contentID='.$itm->contentID);
				$db->setQuery($q);
				$db->execute();
			}
			$db->setQuery('DELETE FROM notes WHERE itemID='.$iid);
			$db->execute();
			return $itm->parentID;
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
		return false;
	}

	private function deleteFolder ($iid)
	{
		try
		{
			$db = $this->getDbo();
			$db->setQuery('SELECT itemID FROM notes WHERE parentID='.$iid);
			$itms = $db->loadAssocList();
			foreach ($itms as $itm) {
				$this->deleteItem($itm['itemID']);
			}
		}
		catch (Exception $e)
		{
			$this->setError($e);
		}
	}

	protected function populateState ()
	{
		// Initialize variables
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_usernotes');
		$input = $app->input;

		// album ID
		$nid = $input->get('nid', 0, 'INT');
		$this->state->set('usernote.id', $nid);

		// Load the parameters.
		$this->setState('params', $params);
	}
}
