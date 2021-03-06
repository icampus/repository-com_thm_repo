<?php
/**
 * @category    Joomla component
 * @package     THM_Repo
 * @subpackage  com_thm_repo.admin
 * @author      Stefan Schneider, <stefan.schneider@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
// No direct access
defined('_JEXEC') or die;

// Import Joomla table library
jimport('joomla.database.table');

/**
 * Entity class for component com_thm_repo
 *
 * @category  Joomla.Component.Admin
 * @package   com_thm_repo.admin
 * @link      www.mni.thm.de
 */
class THM_RepoTableEntity extends JTable
{
	public $asset_id;

	/**
	 * Constructor
	 *
	 * @param   object &$db Database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__thm_repo_entity', 'id', $db);
	}

	/**
	 * Overridden bind function
	 *
	 * @param   array $array  named array
	 * @param   mixed $ignore An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error string
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return      string
	 *
	 * @since       2.5
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_thm_repo.entity.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return      string
	 *
	 * @since       2.5
	 */
	protected function _getAssetTitle()
	{
		// TODO: check: is necessary to return a asset title for this entity.
		return 'entity';
	}

	/**
	 * Method to get the asset-parent-id of the item
	 *
	 * @param   JTable  $table A JTable object for the asset parent.
	 * @param   integer $id    Id to look up
	 *
	 * @return      int
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$asset = JTable::getInstance('asset');
		$asset->loadByName('com_thm_repo');

		return $asset->id;
	}
}
