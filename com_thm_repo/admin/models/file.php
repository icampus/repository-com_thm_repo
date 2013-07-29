<?php
/**
 * @category    Joomla component
 * @package	    THM_Repo
 * @subpackage  com_thm_repo.admin
 * @author      Stefan Schneider, <stefan.schneider@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * THM_RepoModelFile class for component com_thm_repo
 *
 * @category  Joomla.Component.Admin
 * @package   com_thm_repo.admin
 * @link      www.mni.thm.de
 * @since     Class available since Release 2.0
 */
class THM_RepoModelFile extends JModelAdmin
{
	/**
	 * @var array messages
	 */
	protected $messages;
	
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 * 
	 * @return  JTable  A database object
	 * 
	 * @since   2.5
	 */
	public function getTable($type = 'File', $prefix = 'THM_RepoTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 * 
	 * @return  mixed    A JForm object on success, false on failure
	 * 
	 * @since   2.5
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_thm_repo.file', 'file', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return      mixed   The data for the form.
	 * 
	 * @since       2.5
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_thm_repo.edit.file.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
	
  

  	/**
  	 * Method to get a single record.
  	 * 
  	 * @param   integer  $pk  The id of the primary key.
  	 * 
  	 * @return  mixed    Object on success, false on failure.
  	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);
	
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		if ($pk > 0)
		{
				
			// Get Data from #__thm_repo_version table and assign it to $item
			$data = $this->getData($item->id);
			$item->name = $data->name;
			$item->description = $data->description;
			$item->modified = $data->modified;
			$item->modified_by = $data->modified_by;
			$item->path = $data->path;
			$item->file_id = $data->id;
			$item->size = $data->size;
			$item->mimetype = $data->mimetype;
			$item->current_version = $data->current_version;
		}
		else
		{
			// Set Data NULL for creating new file
			$item->path = null;
			$item->file_id = null;
			$item->size = null;
			$item->mimetype = null;
			$item->current_version = null;
		}
		
		return $item;
	}
	
	/**
	 * Method to get the needed data from entity table
	 *
	 * @param   unknown  $id  ID from creating/editing entry
	 *
	 * @return mixed   The data from #__thm_repo_entity table.
	 */
	public function getData($id)
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__thm_repo_version AS v');
		$query->join('INNER', '#__thm_repo_file AS f ON v.id = f.id AND v.version = f.current_version');
		$query->where('v.id = ' . $id);
		$db->setQuery($query);
		$result = $db->loadObject();
	
		return $result;
	
	}
	
	/**
	 * Function to save an entry and create a version 
	 * 
	 * @param   unknown  $data  Data from the deleted file
	 *
	 * @return boolean
	 */
	public function save($data)
	{
		// Retrieve file details from uploaded file, sent from adminForm form
		$file = JRequest::getVar('file', null, 'files', 'array');
		
		// Clean up filename to get rid of strange characters like spaces etc
		$filename = JFile::makeSafe($file['name']);
		

		// Assign filedata
		$filedata->id = $data['id'];
		$filedata->current_version = $data['current_version'] + 1;
			
		// Assign entity data
		$entitydata->id = $data['id'];
		$entitydata->parent_id = $data['parent_id'];
		$entitydata->viewlevel = $data['viewlevel'];
		$entitydata->created = $data['created'];
		$entitydata->created_by = $data['created_by'];
		
		// Assign version data
		$versiondata->id = $data['id'];
		$versiondata->version = $data['current_version'] + 1;
		$versiondata->name = $data['name'];
		$versiondata->description = $data['description'];
		$versiondata->modified = $data['modified'];
		$versiondata->modified_by = $data['modified_by'];
		$versiondata->path = $data['path'];
		$versiondata->size = $file['size'];
		$versiondata->mimetype = $file['type'];
	
		// GetDBO
		$db = JFactory::getDBO();
	
		// New File is uploaded
		if ($entitydata->id == 0)
		{
				
			if (!($db->insertObject('#__thm_repo_entity', $entitydata, 'id')))
			{
				return false;
			}
				
			// Insert created entity id to version dataid and filedata id
			$versiondata->id = $db->insertID();
			$filedata->id = $db->insertID();
			
			if (!($db->insertObject('#__thm_repo_file', $filedata, 'id')))
			{
				return false;
			}
						
			// Add Path to Versiondata
			$versiondata->path = JPATH_ROOT . DS . "media" . DS . "com_thm_repo" . DS . $versiondata->id . "_" . $filename;
			if (!($db->insertObject('#__thm_repo_version', $versiondata, 'id')))
			{
				return false;
			}
		}
		// Old File is updated
		else
		{		
			if (!($db->updateObject('#__thm_repo_file', $filedata, 'id')))
			{
				return false;
			}
			// A New File is uploaded
			if ($filename)
			{
				// Get needed Data for Version Update
				$db = $this->getDbo();
				$query = $db->getQuery(true);
				$query->select('v.name, v.description, v.modified, v.modified_by, v.path, v.size, v.mimetype, v.version');
				$query->from('#__thm_repo_file AS f');
				$query->where('f.id = ' . $data['id']);
				$query->join('INNER', '#__thm_repo_version AS v ON f.id = v.id AND v.version = ' . $data['current_version']);
				$db->setQuery($query);
				$oldversiondata = $db->loadObject();
				
				// Create a Version of File
				$versionsrc = $oldversiondata->path;
				$versiondest = $oldversiondata->path . "_" . $oldversiondata->version;
			
				if (!JFile::move($versionsrc, $versiondest))
				{
					return false;
				}
					
				// Add Versionnumber to Path
// 				$oldversiondata->path = $versiondest;
					
				// Update Path on Version with same File
				$query = $db->getQuery(true);
				$query->update($db->quoteName('#__thm_repo_version'));
				$query->set('path = ' . $db->quote($versiondest));
				$query->where('path = ' . $db->quote($versionsrc));
				$db->setQuery($query);
				$db->query();
				
				// Add Path to Versiondata
				$versiondata->path = JPATH_ROOT . DS . "media" . DS . "com_thm_repo" . DS . $versiondata->id . "_" . $filename;
				if (!($db->insertObject('#__thm_repo_version', $versiondata, 'id')))
				{
					return false;
				}		
			}
			// No New File is uploaded
			else 
			{
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->select('path, size, mimetype');
				$query->from('#__thm_repo_version');
				$query->where('id = ' . (int) $data['id'] . ' AND version=' . (int) $data['current_version']);
				$db->setQuery($query);
				$result = $db->loadObject();

				$versiondata->path = $result->path;
				$versiondata->size = $result->size;
				$versiondata->mimetype = $result->mimetype;

				// Add Version without new file
				if (!($db->insertObject('#__thm_repo_version', $versiondata, 'id')))
				{
					return false;
				}
			}
		}						
		// Set up the source and destination of the file
		if ($filename)
		{
			$src = $file['tmp_name'];
			$dest = JPATH_ROOT . DS . "media" . DS . "com_thm_repo" . DS . $versiondata->id . "_" . $filename;
			
			if (!JFile::upload($src, $dest))
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Function to delete file including all versions
	 * 
	 * @param   unknown  $data  Data from the deleted file
	 *
	 * @return boolean
	 */
	public function delete($data)
	{
		$id = $data[0];
	
		// GetDBO
		$db = JFactory::getDBO();
		
		// Delete Version files
		$query = $db->getQuery(true);
		$query->select('path');
		$query->from('#__thm_repo_version');
		$query->where('id = ' . $id);
		$db->setQuery((string) $query);
		$versions = $db->loadObjectList();
	
		if ($versions)
		{
			foreach ($versions as $version)
			{	
				// Delete every Version File from deleted File
				JFile::delete($version->path);
			}
		}
		
		// Delete Version record
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__thm_repo_version'));
		$query->where('id = ' . $id);
		$db->setQuery($query);
		if (!($db->query()))
		{
			return false;
		}
	
		// Delete File record
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__thm_repo_file'));
		$query->where('id = ' . $id);
		$db->setQuery($query);
		if (!($db->query()))
		{
			return false;
		}
	
		// Delete Entity record
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__thm_repo_entity'));
		$query->where('id = ' . $id);
		$db->setQuery($query);
		if (!($db->query()))
		{
			return false;
		}
		return true;
	}
}