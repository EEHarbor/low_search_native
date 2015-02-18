<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Filter by native parameters
 *
 * @package        low_search
 * @author         Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-search
 * @copyright      Copyright (c) 2014, Low
 */
class Low_search_filter_native extends Low_search_filter {

	// protected $priority = 5;

	/**
	 * Replaces native filtering and adds some of its own
	 *
	 * @access     public
	 * @return     void
	 */
	public function filter($entry_ids)
	{
		// --------------------------------------
		// Log it
		// --------------------------------------

		$this->_log('Applying '.__CLASS__);

		// --------------------------------------
		// Keep track which params to unset
		// --------------------------------------

		$forget = array();

		// --------------------------------------
		// Need this for later
		// --------------------------------------

		$now = ee()->localize->now;

		// --------------------------------------
		// Start the query
		// --------------------------------------

		ee()->db->select('t.entry_id')->distinct()
		        ->from('channel_titles t')
		        ->where_in('t.site_id', $this->params->site_ids());

		// --------------------------------------
		// Are we joining the channels table?
		// --------------------------------------

		if ($val = $this->params->get('channel'))
		{
			// Can be an inner join
			ee()->db->join('channels c', 't.channel_id = c.channel_id');

			$this->_where('c.channel_name', $val);

			// Forget it
			$forget[] = 'channel';
		}

		// --------------------------------------
		// Are we joining the members table?
		// --------------------------------------

		if ($this->params->get('group_id') OR $this->params->get('username'))
		{
			// Should be a left join in case of 'not'
			ee()->db->join('members m', 't.author_id = m.member_id', 'left');

			// Simple query on group ID
			if ($group_id = $this->params->get('group_id'))
			{
				$this->_where('m.group_id', $group_id);

				$forget[] = 'group_id';
			}

			// And target the username
			if ($username = $this->params->get('username'))
			{
				// Allow for [NOT_]CURRENT_USER
				$username = str_replace('NOT_', 'not ', $username);
				$username = str_replace('CURRENT_USER', ee()->session->userdata('username'), $username);

				$this->_where('m.username', $username);

				$forget[] = 'username';
			}
		}

		// --------------------------------------
		// Simple parameters for the channel_titles table
		// --------------------------------------

		// First of all, the given entry IDs
		if ($entry_ids)
		{
			ee()->db->where_in('t.entry_id', $entry_ids);
		}

		// Filter by author_id (optional)
		if ($val = $this->params->get('author_id'))
		{
			// Allow for [NOT_]CURRENT_USER
			$val = str_replace('NOT_', 'not ', $val);
			$val = str_replace('CURRENT_USER', ee()->session->userdata('member_id'), $val);

			$this->_where('t.author_id', $val);

			$forget[] = 'author_id';
		}

		// Filter by channel_id (optional)
		if ($val = $this->params->get('channel_id'))
		{
			$this->_where('t.channel_id', $val);
		}

		// Filter by status, default to 'open'
		if ($val = $this->params->get('status', 'open'))
		{
			$this->_where('t.status', $val);
		}

		// Exclude expired entries
		if ($this->params->get('show_expired') != 'yes')
		{
			ee()->db->where("(t.expiration_date = 0 OR t.expiration_date >= {$now})");
		}

		// Exclude future entries
		if ($this->params->get('show_future_entries') != 'yes')
		{
			ee()->db->where('t.entry_date <=', $now);
		}

		// Filter by url_title (optional)
		if ($val = $this->params->get('url_title'))
		{
			$this->_where('t.url_title', $val);

			$forget[] = 'url_title';
		}

		// Filter by year
		if ($val = $this->params->get('year'))
		{
			ee()->db->where('t.year', $val);

			$forget[] = 'year';
		}

		// Filter by month
		if ($val = $this->params->get('month'))
		{
			if (strlen($val) == 1) $val = '0'.$val;
			ee()->db->where('t.month', $val);

			$forget[] = 'month';
		}

		// Filter by day
		if ($val = $this->params->get('day'))
		{
			if (strlen($val) == 1) $val = '0'.$val;
			ee()->db->where('t.day', $val);

			$forget[] = 'day';
		}

		// EXTRA: sticky="only" or sticky="exclude"
		if ($val = $this->params->get('sticky') && in_array($val, array('only', 'exclude')))
		{
			$val = ($val == 'only') ? 'y' : 'n';
			ee()->db->where('t.sticky', $val);
		}

		// --------------------------------------
		// All done with where clauses - get the results
		// --------------------------------------

		$query = ee()->db->get();

		// --------------------------------------
		// Forget the params
		// --------------------------------------

		$this->params->forget = array_merge($this->params->forget, $forget);

		// --------------------------------------
		// Flatten the results into an array
		// --------------------------------------

		$ids = low_flatten_results($query->result_array(), 'entry_id');

		// Return the ids
		return $ids;
	}

	/**
	 * Simple where clause based on parameter key and value
	 */
	public function _where($key, $val)
	{
		// Get parameter value
		list($val, $in) = low_explode_param($val);

		// Add to query
		ee()->db->{$in ? 'where_in' : 'where_not_in'}($key, $val);
	}
}
// End of file lsf.native.php
