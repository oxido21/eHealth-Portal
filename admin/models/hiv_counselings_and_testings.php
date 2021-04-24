<?php
/*--------------------------------------------------------------------------------------------------------|  www.vdm.io  |------/
    __      __       _     _____                 _                                  _     __  __      _   _               _
    \ \    / /      | |   |  __ \               | |                                | |   |  \/  |    | | | |             | |
     \ \  / /_ _ ___| |_  | |  | | _____   _____| | ___  _ __  _ __ ___   ___ _ __ | |_  | \  / | ___| |_| |__   ___   __| |
      \ \/ / _` / __| __| | |  | |/ _ \ \ / / _ \ |/ _ \| '_ \| '_ ` _ \ / _ \ '_ \| __| | |\/| |/ _ \ __| '_ \ / _ \ / _` |
       \  / (_| \__ \ |_  | |__| |  __/\ V /  __/ | (_) | |_) | | | | | |  __/ | | | |_  | |  | |  __/ |_| | | | (_) | (_| |
        \/ \__,_|___/\__| |_____/ \___| \_/ \___|_|\___/| .__/|_| |_| |_|\___|_| |_|\__| |_|  |_|\___|\__|_| |_|\___/ \__,_|
                                                        | |
                                                        |_|
/-------------------------------------------------------------------------------------------------------------------------------/

	@version		1.0.5
	@build			24th April, 2021
	@created		13th August, 2020
	@package		eHealth Portal
	@subpackage		hiv_counselings_and_testings.php
	@author			Oh Martin <https://github.com/namibia/eHealth-Portal>
	@copyright		Copyright (C) 2020 Vast Development Method. All rights reserved.
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Portal for mobile health clinics

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Hiv_counselings_and_testings Model
 */
class Ehealth_portalModelHiv_counselings_and_testings extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
        {
			$config['filter_fields'] = array(
				'a.id','id',
				'a.published','published',
				'a.access','access',
				'a.ordering','ordering',
				'a.created_by','created_by',
				'a.modified_by','modified_by',
				'a.patient','patient'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// Check if the form was submitted
		$formSubmited = $app->input->post->get('form_submited');

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);
		}

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$created_by = $this->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '');
		$this->setState('filter.created_by', $created_by);

		$created = $this->getUserStateFromRequest($this->context . '.filter.created', 'filter_created');
		$this->setState('filter.created', $created);

		$sorting = $this->getUserStateFromRequest($this->context . '.filter.sorting', 'filter_sorting', 0, 'int');
		$this->setState('filter.sorting', $sorting);

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$patient = $this->getUserStateFromRequest($this->context . '.filter.patient', 'filter_patient');
		if ($formSubmited)
		{
			$patient = $app->input->post->get('patient');
			$this->setState('filter.patient', $patient);
		}

		// List state information.
		parent::populateState($ordering, $direction);
	}
	
	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		// check in items
		$this->checkInNow();

		// load parent items
		$items = parent::getItems();

		// set selection value to a translatable value
		if (Ehealth_portalHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// convert counseling_type
				$item->counseling_type = $this->selectionTranslation($item->counseling_type, 'counseling_type');
				// convert last_test_date
				$item->last_test_date = $this->selectionTranslation($item->last_test_date, 'last_test_date');
				// convert prev_test_result
				$item->prev_test_result = $this->selectionTranslation($item->prev_test_result, 'prev_test_result');
				// convert test_result_one
				$item->test_result_one = $this->selectionTranslation($item->test_result_one, 'test_result_one');
				// convert test_result_two
				$item->test_result_two = $this->selectionTranslation($item->test_result_two, 'test_result_two');
				// convert final_test_result
				$item->final_test_result = $this->selectionTranslation($item->final_test_result, 'final_test_result');
				// convert eqa
				$item->eqa = $this->selectionTranslation($item->eqa, 'eqa');
			}
		}

        
		// return items
		return $items;
	}

	/**
	 * Method to convert selection values to translatable string.
	 *
	 * @return translatable string
	 */
	public function selectionTranslation($value,$name)
	{
		// Array of counseling_type language strings
		if ($name === 'counseling_type')
		{
			$counseling_typeArray = array(
				0 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_INDIVIDUAL',
				1 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_COUPLE',
				2 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_MINOR'
			);
			// Now check if value is found in this array
			if (isset($counseling_typeArray[$value]) && Ehealth_portalHelper::checkString($counseling_typeArray[$value]))
			{
				return $counseling_typeArray[$value];
			}
		}
		// Array of last_test_date language strings
		if ($name === 'last_test_date')
		{
			$last_test_dateArray = array(
				0 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_ONESIX_MONTHS',
				1 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_SEVENTWELVE_MONTHS',
				2 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING__YEAR',
				3 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_NEVER'
			);
			// Now check if value is found in this array
			if (isset($last_test_dateArray[$value]) && Ehealth_portalHelper::checkString($last_test_dateArray[$value]))
			{
				return $last_test_dateArray[$value];
			}
		}
		// Array of prev_test_result language strings
		if ($name === 'prev_test_result')
		{
			$prev_test_resultArray = array(
				0 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_NEGATIVE',
				1 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_POSITIVE',
				2 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_UNKNOWN',
				3 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_NA'
			);
			// Now check if value is found in this array
			if (isset($prev_test_resultArray[$value]) && Ehealth_portalHelper::checkString($prev_test_resultArray[$value]))
			{
				return $prev_test_resultArray[$value];
			}
		}
		// Array of test_result_one language strings
		if ($name === 'test_result_one')
		{
			$test_result_oneArray = array(
				0 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_POSITIVE',
				1 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_NEGATIVE',
				2 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_INCONCLUSIVE'
			);
			// Now check if value is found in this array
			if (isset($test_result_oneArray[$value]) && Ehealth_portalHelper::checkString($test_result_oneArray[$value]))
			{
				return $test_result_oneArray[$value];
			}
		}
		// Array of test_result_two language strings
		if ($name === 'test_result_two')
		{
			$test_result_twoArray = array(
				0 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_POSITIVE',
				1 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_NEGATIVE',
				2 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_INCONCLUSIVE'
			);
			// Now check if value is found in this array
			if (isset($test_result_twoArray[$value]) && Ehealth_portalHelper::checkString($test_result_twoArray[$value]))
			{
				return $test_result_twoArray[$value];
			}
		}
		// Array of final_test_result language strings
		if ($name === 'final_test_result')
		{
			$final_test_resultArray = array(
				0 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_NEGATIVE',
				1 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_POSITIVE',
				2 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_UNKNOWN',
				3 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_NA'
			);
			// Now check if value is found in this array
			if (isset($final_test_resultArray[$value]) && Ehealth_portalHelper::checkString($final_test_resultArray[$value]))
			{
				return $final_test_resultArray[$value];
			}
		}
		// Array of eqa language strings
		if ($name === 'eqa')
		{
			$eqaArray = array(
				0 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_YES',
				1 => 'COM_EHEALTH_PORTAL_HIV_COUNSELING_AND_TESTING_NO'
			);
			// Now check if value is found in this array
			if (isset($eqaArray[$value]) && Ehealth_portalHelper::checkString($eqaArray[$value]))
			{
				return $eqaArray[$value];
			}
		}
		return $value;
	}
	
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery()
	{
		// Get the user object.
		$user = JFactory::getUser();
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('a.*');

		// From the ehealth_portal_item table
		$query->from($db->quoteName('#__ehealth_portal_hiv_counseling_and_testing', 'a'));

		// From the ehealth_portal_testing_reason table.
		$query->select($db->quoteName('g.name','testing_reason_name'));
		$query->join('LEFT', $db->quoteName('#__ehealth_portal_testing_reason', 'g') . ' ON (' . $db->quoteName('a.testing_reason') . ' = ' . $db->quoteName('g.id') . ')');

		// From the ehealth_portal_referral table.
		$query->select($db->quoteName('h.name','referral_name'));
		$query->join('LEFT', $db->quoteName('#__ehealth_portal_referral', 'h') . ' ON (' . $db->quoteName('a.referral') . ' = ' . $db->quoteName('h.id') . ')');

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		// Filter by access level.
		$_access = $this->getState('filter.access');
		if ($_access && is_numeric($_access))
		{
			$query->where('a.access = ' . (int) $_access);
		}
		elseif (Ehealth_portalHelper::checkArray($_access))
		{
			// Secure the array for the query
			$_access = ArrayHelper::toInteger($_access);
			// Filter by the Access Array.
			$query->where('a.access IN (' . implode(',', $_access) . ')');
		}
		// Implement View Level Access
		if (!$user->authorise('core.options', 'com_ehealth_portal'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
		// Filter by search.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search) . '%');
				$query->where('(a.patient LIKE '.$search.')');
			}
		}

		// Filter by Patient.
		$_patient = $this->getState('filter.patient');
		if (is_numeric($_patient))
		{
			if (is_float($_patient))
			{
				$query->where('a.patient = ' . (float) $_patient);
			}
			else
			{
				$query->where('a.patient = ' . (int) $_patient);
			}
		}
		elseif (Ehealth_portalHelper::checkString($_patient))
		{
			$query->where('a.patient = ' . $db->quote($db->escape($_patient)));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');
		if ($orderCol != '')
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get list export data.
	 *
	 * @param   array  $pks  The ids of the items to get
	 * @param   JUser  $user  The user making the request
	 *
	 * @return mixed  An array of data items on success, false on failure.
	 */
	public function getExportData($pks, $user = null)
	{
		// setup the query
		if (($pks_size = Ehealth_portalHelper::checkArray($pks)) !== false || 'bulk' === $pks)
		{
			// Set a value to know this is export method. (USE IN CUSTOM CODE TO ALTER OUTCOME)
			$_export = true;
			// Get the user object if not set.
			if (!isset($user) || !Ehealth_portalHelper::checkObject($user))
			{
				$user = JFactory::getUser();
			}
			// Create a new query object.
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);

			// Select some fields
			$query->select('a.*');

			// From the ehealth_portal_hiv_counseling_and_testing table
			$query->from($db->quoteName('#__ehealth_portal_hiv_counseling_and_testing', 'a'));
			// The bulk export path
			if ('bulk' === $pks)
			{
				$query->where('a.id > 0');
			}
			// A large array of ID's will not work out well
			elseif ($pks_size > 500)
			{
				// Use lowest ID
				$query->where('a.id >= ' . (int) min($pks));
				// Use highest ID
				$query->where('a.id <= ' . (int) max($pks));
			}
			// The normal default path
			else
			{
				$query->where('a.id IN (' . implode(',',$pks) . ')');
			}
			// Implement View Level Access
			if (!$user->authorise('core.options', 'com_ehealth_portal'))
			{
				$groups = implode(',', $user->getAuthorisedViewLevels());
				$query->where('a.access IN (' . $groups . ')');
			}

			// Order the results by ordering
			$query->order('a.ordering  ASC');

			// Load the items
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				$items = $db->loadObjectList();

				// Set values to display correctly.
				if (Ehealth_portalHelper::checkArray($items))
				{
					foreach ($items as $nr => &$item)
					{
						// unset the values we don't want exported.
						unset($item->asset_id);
						unset($item->checked_out);
						unset($item->checked_out_time);
					}
				}
				// Add headers to items array.
				$headers = $this->getExImPortHeaders();
				if (Ehealth_portalHelper::checkObject($headers))
				{
					array_unshift($items,$headers);
				}
				return $items;
			}
		}
		return false;
	}

	/**
	* Method to get header.
	*
	* @return mixed  An array of data items on success, false on failure.
	*/
	public function getExImPortHeaders()
	{
		// Get a db connection.
		$db = JFactory::getDbo();
		// get the columns
		$columns = $db->getTableColumns("#__ehealth_portal_hiv_counseling_and_testing");
		if (Ehealth_portalHelper::checkArray($columns))
		{
			// remove the headers you don't import/export.
			unset($columns['asset_id']);
			unset($columns['checked_out']);
			unset($columns['checked_out_time']);
			$headers = new stdClass();
			foreach ($columns as $column => $type)
			{
				$headers->{$column} = $column;
			}
			return $headers;
		}
		return false;
	}
	
	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @return  string  A store id.
	 *
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		// Check if the value is an array
		$_access = $this->getState('filter.access');
		if (Ehealth_portalHelper::checkArray($_access))
		{
			$id .= ':' . implode(':', $_access);
		}
		// Check if this is only an number or string
		elseif (is_numeric($_access)
		 || Ehealth_portalHelper::checkString($_access))
		{
			$id .= ':' . $_access;
		}
		$id .= ':' . $this->getState('filter.ordering');
		$id .= ':' . $this->getState('filter.created_by');
		$id .= ':' . $this->getState('filter.modified_by');
		$id .= ':' . $this->getState('filter.patient');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to checkin all items left checked out longer then a set time.
	 *
	 * @return  a bool
	 *
	 */
	protected function checkInNow()
	{
		// Get set check in time
		$time = JComponentHelper::getParams('com_ehealth_portal')->get('check_in');

		if ($time)
		{

			// Get a db connection.
			$db = JFactory::getDbo();
			// reset query
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__ehealth_portal_hiv_counseling_and_testing'));
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows())
			{
				// Get Yesterdays date
				$date = JFactory::getDate()->modify($time)->toSql();
				// reset query
				$query = $db->getQuery(true);

				// Fields to update.
				$fields = array(
					$db->quoteName('checked_out_time') . '=\'0000-00-00 00:00:00\'',
					$db->quoteName('checked_out') . '=0'
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('checked_out') . '!=0', 
					$db->quoteName('checked_out_time') . '<\''.$date.'\''
				);

				// Check table
				$query->update($db->quoteName('#__ehealth_portal_hiv_counseling_and_testing'))->set($fields)->where($conditions); 

				$db->setQuery($query);

				$db->execute();
			}
		}

		return false;
	}
}
