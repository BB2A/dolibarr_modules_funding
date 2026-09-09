<?php
/* Copyright (C) 2015		Jean-François Ferry		<jfefe@aternatik.fr>
 * Copyright (C) 2024		Frédéric France			<frederic.france@free.fr>
 * Copyright (C) 2025-2026	Anthony Berton			<anthony.berton@bb2a.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use Luracast\Restler\RestException;

dol_include_once('/funding/class/coefficient.class.php');
dol_include_once('/funding/class/funding.class.php');
dol_include_once('/funding/class/retention.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';



/**
 * \file    htdocs/modulebuilder/template/class/api_funding.class.php
 * \ingroup funding
 * \brief   File for API management of myobject.
 */

/**
 * API class for funding myobject
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class FundingApi extends DolibarrApi
{
	/**
	 * @var MyObject {@type MyObject}
	 */
	/*
	 * @var mixed TODO: set type
	 */
	public $coefficient;
	/*
	 * @var mixed TODO: set type
	 */
	public $funding;
	/*
	 * @var mixed TODO: set type
	 */
	public $retention;

	/**
	 * Constructor
	 *
	 * @url     GET /
	 */
	public function __construct()
	{
		global $db;
		$this->db = $db;
		$this->coefficient = new Coefficient($this->db);
		$this->funding = new Funding($this->db);
		$this->retention = new Retention($this->db);
	}


	/* BEGIN MODULEBUILDER API COEFFICIENT */
	/**
	 * Get properties of a coefficient object
	 *
	 * Return an array with coefficient information
	 *
	 * @param	int		$id				ID of coefficient
	 * @return  Object					Object with cleaned properties
	 * @phan-return	Coefficient			Object with cleaned properties
	 * @phpstan-return	Coefficient			Object with cleaned properties
	 *
	 * @phan-return  Coefficient
	 *
	 * @url	GET coefficients/{id}
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 */
	public function getCoefficient($id)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'coefficient', 'read')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('coefficient', $id, 'funding_coefficient')) {
			throw new RestException(403, 'Access to instance id='.$id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->coefficient->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Coefficient not found');
		}

		return $this->_cleanObjectDatas($this->coefficient);
	}


	/**
	 * List coefficients
	 *
	 * Get a list of coefficients
	 *
	 * @param string		   $sortfield			Sort field
	 * @param string		   $sortorder			Sort order
	 * @param int			   $limit				Limit for list
	 * @param int			   $page				Page number
	 * @param string           $sqlfilters          Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
	 * @param string		   $properties			Restrict the data returned to these properties. Ignored if empty. Comma separated list of properties names
	 * @return  array                               Array of Coefficient objects
	 * @phan-return array<int,Coefficient>
	 * @phpstan-return array<int,Coefficient>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 503 System error
	 *
	 * @url	GET /coefficients/
	 */
	public function indexCoefficient($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '', $properties = '')
	{
		$obj_ret = array();
		$tmpobject = new Coefficient($this->db);

		if (!DolibarrApiAccess::$user->hasRight('funding', 'coefficient', 'read')) {
			throw new RestException(403);
		}

		$socid = DolibarrApiAccess::$user->socid ?: 0;

		$restrictonsocid = 0; // Set to 1 if there is a field socid in table of object

		// If the internal user must only see his customers, force searching by him
		$search_sale = 0;
		if ($restrictonsocid && !DolibarrApiAccess::$user->hasRight('societe', 'client', 'voir') && !$socid) {
			$search_sale = DolibarrApiAccess::$user->id;
		}
		if (!isModEnabled('societe')) {
			$search_sale = 0; // If module thirdparty not enabled, sale representative is something that does not exists
		}

		$sql = "SELECT t.rowid";
		$sql .= " FROM ".$this->db->prefix().$tmpobject->table_element." AS t";
		$sql .= " LEFT JOIN ".$this->db->prefix().$tmpobject->table_element."_extrafields AS ef ON (ef.fk_object = t.rowid)"; // Modification VMR Global Solutions to include extrafields as search parameters in the API GET call, so we will be able to filter on extrafields
		$sql .= " WHERE 1 = 1";
		if ($tmpobject->ismultientitymanaged) {
			$sql .= ' AND t.entity IN ('.getEntity($tmpobject->element).')';
		}
		if ($restrictonsocid && $socid) {
			$sql .= " AND t.fk_soc = ".((int) $socid);
		}
		// Search on sale representative
		if ($search_sale && $search_sale != '-1') {
			if ($search_sale == -2) {
				$sql .= " AND NOT EXISTS (SELECT sc.fk_soc FROM ".$this->db->prefix()."societe_commerciaux as sc WHERE sc.fk_soc = t.fk_soc)";
			} elseif ($search_sale > 0) {
				$sql .= " AND EXISTS (SELECT sc.fk_soc FROM ".$this->db->prefix()."societe_commerciaux as sc WHERE sc.fk_soc = t.fk_soc AND sc.fk_user = ".((int) $search_sale).")";
			}
		}
		if ($sqlfilters) {
			$errormessage = '';
			$sql .= forgeSQLFromUniversalSearchCriteria($sqlfilters, $errormessage);
			if ($errormessage) {
				throw new RestException(400, 'Error when validating parameter sqlfilters -> '.$errormessage);
			}
		}

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;

			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		$result = $this->db->query($sql);
		$i = 0;
		if ($result) {
			$num = $this->db->num_rows($result);
			while ($i < $num) {
				$obj = $this->db->fetch_object($result);
				$tmp_object = new Coefficient($this->db);
				if ($tmp_object->fetch($obj->rowid)) {
					$obj_ret[] = $this->_filterObjectProperties($this->_cleanObjectDatas($tmp_object), $properties);
				}
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieving coefficient list: '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/**
	 * Create coefficient object
	 *
	 * @param array $request_data   Request data
	 * @phan-param ?array<string,mixed> $request_data
	 * @phpstan-param ?array<string,mixed> $request_data
	 * @return int  				ID of coefficient
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 500 System error
	 *
	 * @url	POST coefficients/
	 */
	public function postCoefficient($request_data = null)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'coefficient', 'write')) {
			throw new RestException(403);
		}

		// Check mandatory fields
		$result = $this->_validateCoefficient($request_data);

		foreach ($request_data as $field => $value) {
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again with the caller @phan-suppress-next-line PhanTypeInvalidDimOffset
				$this->coefficient->context['caller'] = sanitizeVal((string) $request_data['caller'], 'aZ09');
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->coefficient->array_options[$index] = $this->_checkValForAPI('extrafields', $val, $this->coefficient);
				}
				continue;
			}

			$this->coefficient->$field = $this->_checkValForAPI((string) $field, $value, $this->coefficient);
		}

		// Clean data
		// $this->coefficient->abc = sanitizeVal($this->coefficient->abc, 'alphanohtml');

		if ($this->coefficient->create(DolibarrApiAccess::$user) < 0) {
			throw new RestException(500, "Error creating Coefficient", array_merge(array($this->coefficient->error), $this->coefficient->errors));
		}
		return $this->coefficient->id;
	}

	/**
	 * Update coefficient
	 *
	 * @param 	int   		$id             Id of coefficient to update
	 * @param 	array 		$request_data   Data
	 * @phan-param ?array<string,mixed>	$request_data
	 * @phpstan-param ?array<string,mixed>	$request_data
	 * @return 	Object						Object after update
	 * @phan-return Coefficient
	 * @phpstan-return Coefficient
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 500 System error
	 *
	 * @url	PUT coefficients/{id}
	 */
	public function putCoefient($id, $request_data = null)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'coefficient', 'write')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('coefficient', $id, 'funding_coefficient')) {
			throw new RestException(403, 'Access to instance id='.$this->coefficient->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->coefficient->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Coefficient not found');
		}

		foreach ($request_data as $field => $value) {
			if ($field == 'id') {
				continue;
			}
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again with the caller
				$this->coefficient->context['caller'] = sanitizeVal($request_data['caller'], 'aZ09');
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->coefficient->array_options[$index] = $this->_checkValForAPI('extrafields', $val, $this->coefficient);
				}
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->coefficient->array_options[$index] = $this->_checkValForAPI($field, $val, $this->coefficient);
				}
				continue;
			}

			$this->coefficient->$field = $this->_checkValForAPI($field, $value, $this->coefficient);
		}

		// Clean data
		// $this->coefficient->abc = sanitizeVal($this->coefficient->abc, 'alphanohtml');

		if ($this->coefficient->update(DolibarrApiAccess::$user, 0) > 0) {
			return $this->getCoeff($id);
		} else {
			throw new RestException(500, $this->coefficient->error);
		}
	}

	/**
	 * Delete coefficient
	 *
	 * @param   int     $id   Coefficient ID
	 * @return  array
	 * @phan-return array<string,array{code:int,message:string}>
	 * @phpstan-return array<string,array{code:int,message:string}>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 409 Nothing to do
	 * @throws RestException 500 System error
	 *
	 * @url	DELETE coefficients/{id}
	 */
	public function deleteCoefficient($id)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'coefficient', 'delete')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('coefficient', $id, 'funding_coefficient')) {
			throw new RestException(403, 'Access to instance id='.$this->coefficient->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->coefficient->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Coefficient not found');
		}

		if ($this->coefficient->delete(DolibarrApiAccess::$user) == 0) {
			throw new RestException(409, 'Error when deleting Coefficient : '.$this->coefficient->error);
		} elseif ($this->coefficient->delete(DolibarrApiAccess::$user) < 0) {
			throw new RestException(500, 'Error when deleting Coefficient : '.$this->coefficient->error);
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'Coefficient deleted'
			)
		);
	}


	/**
	 * Validate fields before creating or updating object
	 *
	 * @param	array		$data   Array of data to validate
	 * @phan-param		?array<string,null|int|float|string> $data
	 * @phpstan-param	?array<string,null|int|float|string> $data
	 * @return	array
	 * @phan-return		array<string,null|int|float|string>|array{}
	 * @phpstan-return	array<string,null|int|float|string>|array{}
	 *
	 * @throws	RestException
	 */
	private function _validateCoefficient($data)
	{
		if (!is_array($data)) {
			$data = array();
		}
		$coefficient = array();
		foreach ($this->coefficient->fields as $field => $propfield) {
			if (in_array($field, array('rowid', 'entity', 'date_creation', 'tms', 'fk_user_creat')) || $propfield['notnull'] != 1) {
				continue; // Not a mandatory field
			}
			if (!isset($data[$field])) {
				throw new RestException(400, "$field field missing");
			}
			$coefficient[$field] = $data[$field];
		}
		return $coefficient;
	}

	/* END MODULEBUILDER API COEFFICIENT */


	/* BEGIN MODULEBUILDER API FUNDING */
	/**
	 * Get properties of a funding object
	 *
	 * Return an array with funding information
	 *
	 * @param	int		$id				ID of funding
	 * @return  Object					Object with cleaned properties
	 * @phan-return	Funding			Object with cleaned properties
	 * @phpstan-return	Funding			Object with cleaned properties
	 *
	 * @phan-return  Funding
	 *
	 * @url	GET fundings/{id}
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 */
	public function getFunding($id)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'funding', 'read')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('funding', $id, 'funding_funding')) {
			throw new RestException(403, 'Access to instance id='.$id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->funding->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Funding not found');
		}

		return $this->_cleanObjectDatas($this->funding);
	}


	/**
	 * List fundings
	 *
	 * Get a list of fundings
	 *
	 * @param string		   $sortfield			Sort field
	 * @param string		   $sortorder			Sort order
	 * @param int			   $limit				Limit for list
	 * @param int			   $page				Page number
	 * @param string           $sqlfilters          Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
	 * @param string		   $properties			Restrict the data returned to these properties. Ignored if empty. Comma separated list of properties names
	 * @return  array                               Array of Funding objects
	 * @phan-return array<int,Funding>
	 * @phpstan-return array<int,Funding>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 503 System error
	 *
	 * @url	GET /fundings/
	 */
	public function indexFunding($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '', $properties = '')
	{
		$obj_ret = array();
		$tmpobject = new Funding($this->db);

		if (!DolibarrApiAccess::$user->hasRight('funding', 'read')) {
			throw new RestException(403);
		}

		$socid = DolibarrApiAccess::$user->socid ?: 0;

		$restrictonsocid = 0; // Set to 1 if there is a field socid in table of object

		// If the internal user must only see his customers, force searching by him
		$search_sale = 0;
		if ($restrictonsocid && !DolibarrApiAccess::$user->hasRight('societe', 'client', 'voir') && !$socid) {
			$search_sale = DolibarrApiAccess::$user->id;
		}
		if (!isModEnabled('societe')) {
			$search_sale = 0; // If module thirdparty not enabled, sale representative is something that does not exists
		}

		$sql = "SELECT t.rowid";
		$sql .= " FROM ".$this->db->prefix().$tmpobject->table_element." AS t";
		$sql .= " LEFT JOIN ".$this->db->prefix().$tmpobject->table_element."_extrafields AS ef ON (ef.fk_object = t.rowid)"; // Modification VMR Global Solutions to include extrafields as search parameters in the API GET call, so we will be able to filter on extrafields
		$sql .= " WHERE 1 = 1";
		if ($tmpobject->ismultientitymanaged) {
			$sql .= ' AND t.entity IN ('.getEntity($tmpobject->element).')';
		}
		if ($restrictonsocid && $socid) {
			$sql .= " AND t.fk_soc = ".((int) $socid);
		}
		// Search on sale representative
		if ($search_sale && $search_sale != '-1') {
			if ($search_sale == -2) {
				$sql .= " AND NOT EXISTS (SELECT sc.fk_soc FROM ".$this->db->prefix()."societe_commerciaux as sc WHERE sc.fk_soc = t.fk_soc)";
			} elseif ($search_sale > 0) {
				$sql .= " AND EXISTS (SELECT sc.fk_soc FROM ".$this->db->prefix()."societe_commerciaux as sc WHERE sc.fk_soc = t.fk_soc AND sc.fk_user = ".((int) $search_sale).")";
			}
		}
		if ($sqlfilters) {
			$errormessage = '';
			$sql .= forgeSQLFromUniversalSearchCriteria($sqlfilters, $errormessage);
			if ($errormessage) {
				throw new RestException(400, 'Error when validating parameter sqlfilters -> '.$errormessage);
			}
		}

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;

			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		$result = $this->db->query($sql);
		$i = 0;
		if ($result) {
			$num = $this->db->num_rows($result);
			while ($i < $num) {
				$obj = $this->db->fetch_object($result);
				$tmp_object = new Funding($this->db);
				if ($tmp_object->fetch($obj->rowid)) {
					$obj_ret[] = $this->_filterObjectProperties($this->_cleanObjectDatas($tmp_object), $properties);
				}
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieving funding list: '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/**
	 * Create funding object
	 *
	 * @param array $request_data   Request data
	 * @phan-param ?array<string,mixed> $request_data
	 * @phpstan-param ?array<string,mixed> $request_data
	 * @return int  				ID of funding
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 500 System error
	 *
	 * @url	POST fundings/
	 */
	public function postFunding($request_data = null)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'write')) {
			throw new RestException(403);
		}

		// Check mandatory fields
		$result = $this->_validateFunding($request_data);

		foreach ($request_data as $field => $value) {
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again with the caller @phan-suppress-next-line PhanTypeInvalidDimOffset
				$this->funding->context['caller'] = sanitizeVal((string) $request_data['caller'], 'aZ09');
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->funding->array_options[$index] = $this->_checkValForAPI('extrafields', $val, $this->funding);
				}
				continue;
			}

			$this->funding->$field = $this->_checkValForAPI((string) $field, $value, $this->funding);
		}

		// Clean data
		// $this->funding->abc = sanitizeVal($this->funding->abc, 'alphanohtml');

		if ($this->funding->create(DolibarrApiAccess::$user) < 0) {
			throw new RestException(500, "Error creating Funding", array_merge(array($this->funding->error), $this->funding->errors));
		}
		return $this->funding->id;
	}

	/**
	 * Update funding
	 *
	 * @param 	int   		$id             Id of funding to update
	 * @param 	array 		$request_data   Data
	 * @phan-param ?array<string,mixed>	$request_data
	 * @phpstan-param ?array<string,mixed>	$request_data
	 * @return 	Object						Object after update
	 * @phan-return Funding
	 * @phpstan-return Funding
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 500 System error
	 *
	 * @url	PUT fundings/{id}
	 */
	public function putFunding($id, $request_data = null)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'write')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('funding', $id, 'funding_funding')) {
			throw new RestException(403, 'Access to instance id='.$this->funding->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->funding->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Funding not found');
		}

		foreach ($request_data as $field => $value) {
			if ($field == 'id') {
				continue;
			}
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again with the caller
				$this->funding->context['caller'] = sanitizeVal($request_data['caller'], 'aZ09');
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->funding->array_options[$index] = $this->_checkValForAPI('extrafields', $val, $this->funding);
				}
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->funding->array_options[$index] = $this->_checkValForAPI($field, $val, $this->funding);
				}
				continue;
			}

			$this->funding->$field = $this->_checkValForAPI($field, $value, $this->funding);
		}

		// Clean data
		// $this->funding->abc = sanitizeVal($this->funding->abc, 'alphanohtml');

		if ($this->funding->update(DolibarrApiAccess::$user, 0) > 0) {
			return $this->getFunding($id);
		} else {
			throw new RestException(500, $this->funding->error);
		}
	}

	/**
	 * Delete funding
	 *
	 * @param   int     $id   Funding ID
	 * @return  array
	 * @phan-return array<string,array{code:int,message:string}>
	 * @phpstan-return array<string,array{code:int,message:string}>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 409 Nothing to do
	 * @throws RestException 500 System error
	 *
	 * @url	DELETE fundings/{id}
	 */
	public function deleteFunding($id)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'delete')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('funding', $id, 'funding_funding')) {
			throw new RestException(403, 'Access to instance id='.$this->funding->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->funding->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Funding not found');
		}

		if ($this->funding->delete(DolibarrApiAccess::$user) == 0) {
			throw new RestException(409, 'Error when deleting Funding : '.$this->funding->error);
		} elseif ($this->funding->delete(DolibarrApiAccess::$user) < 0) {
			throw new RestException(500, 'Error when deleting Funding : '.$this->funding->error);
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'Funding deleted'
			)
		);
	}


	/**
	 * Validate fields before creating or updating object
	 *
	 * @param	array		$data   Array of data to validate
	 * @phan-param		?array<string,null|int|float|string> $data
	 * @phpstan-param	?array<string,null|int|float|string> $data
	 * @return	array
	 * @phan-return		array<string,null|int|float|string>|array{}
	 * @phpstan-return	array<string,null|int|float|string>|array{}
	 *
	 * @throws	RestException
	 */
	private function _validateFunding($data)
	{
		if (!is_array($data)) {
			$data = array();
		}
		$funding = array();
		foreach ($this->funding->fields as $field => $propfield) {
			if (in_array($field, array('rowid', 'entity', 'date_creation', 'tms', 'fk_user_creat')) || $propfield['notnull'] != 1) {
				continue; // Not a mandatory field
			}
			if (!isset($data[$field])) {
				throw new RestException(400, "$field field missing");
			}
			$funding[$field] = $data[$field];
		}
		return $funding;
	}

	/**
	 * Get funding status list
	 *
	 * Return list of possible status for funding
	 *
	 * @return  array                               Array with funding status list
	 * @throws RestException 403 Not allowed
	 *
	 * @url	GET fundings/statuslist
	 */
	public function getFundingStatusList()
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'read')) {
			throw new RestException(403);
		}

		global $langs;
		$langs->load('funding@funding');

		// Status dictionary
		$statusList['status'] = array(
			Funding::STATUS_DRAFT => $langs->trans('FundingStatusDraft'),
			Funding::STATUS_VALIDATED => $langs->trans('FundingStatusValidated'),
			Funding::STATUS_UPDATE => $langs->trans('FundingStatusUpdate'),
			Funding::STATUS_ACCEPT => $langs->trans('FundingStatusAccept'),
			Funding::STATUS_DENIED => $langs->trans('FundingStatusDenied'),
			Funding::STATUS_RUNNING => $langs->trans('FundingStatusRunning'),
			Funding::STATUS_END => $langs->trans('FundingStatusEnd'),
			Funding::STATUS_CANCELED => $langs->trans('FundingStatusDisabled')
		);

		// Status short dictionary
		$statusList['status_short'] = array(
			Funding::STATUS_DRAFT => $langs->trans('FundingStatusDraftShort'),
			Funding::STATUS_VALIDATED => $langs->trans('FundingStatusValidatedShort'),
			Funding::STATUS_UPDATE => $langs->trans('FundingStatusUpdateShort'),
			Funding::STATUS_ACCEPT => $langs->trans('FundingStatusAcceptShort'),
			Funding::STATUS_DENIED => $langs->trans('FundingStatusDeniedShort'),
			Funding::STATUS_RUNNING => $langs->trans('FundingStatusRunningShort'),
			Funding::STATUS_END => $langs->trans('FundingStatusEndShort'),
			Funding::STATUS_CANCELED => $langs->trans('FundingStatusDisabledShort')
		);

		// Status folder dictionary
		$statusList['status_folder'] = array(
			Funding::STATUS_FOLDER_SENDORG => $langs->trans('FundingStatusFolderSendOrg'),
			Funding::STATUS_FOLDER_LACK => $langs->trans('FundingStatusFolderLack'),
			Funding::STATUS_FOLDER_LACKOK => $langs->trans('FundingStatusFolderLackOk'),
			Funding::STATUS_FOLDER_ACCEPT_RETENTION => $langs->trans('FundingStatusFolderAcceptRetention'),
			Funding::STATUS_FOLDER_REDEEMED => $langs->trans('FundingStatusFolderRedeemed'),
			Funding::STATUS_FOLDER_EXTENSION => $langs->trans('FundingStatusFolderExtension'),
			Funding::STATUS_FOLDER_DENOUNCED => $langs->trans('FundingStatusFolderDenounced'),
			Funding::STATUS_FOLDER_CLOSED_TRANSFER => $langs->trans('FundingStatusFolderClosedTransfer'),
			Funding::STATUS_FOLDER_CLOSED_LESSOR => $langs->trans('FundingStatusFolderClosedLessor')
		);

		// Status folder short dictionary
		$statusList['status_folder_short'] = array(
			Funding::STATUS_FOLDER_SENDORG => $langs->trans('FundingStatusFolderSendOrgShort'),
			Funding::STATUS_FOLDER_LACK => $langs->trans('FundingStatusFolderLackShort'),
			Funding::STATUS_FOLDER_LACKOK => $langs->trans('FundingStatusFolderLackOkShort'),
			Funding::STATUS_FOLDER_ACCEPT_RETENTION => $langs->trans('FundingStatusFolderAcceptRetentionShort'),
			Funding::STATUS_FOLDER_REDEEMED => $langs->trans('FundingStatusFolderRedeemedShort'),
			Funding::STATUS_FOLDER_EXTENSION => $langs->trans('FundingStatusFolderExtensionShort'),
			Funding::STATUS_FOLDER_DENOUNCED => $langs->trans('FundingStatusFolderDenouncedShort'),
			Funding::STATUS_FOLDER_CLOSED_TRANSFER => $langs->trans('FundingStatusFolderClosedTransferShort'),
			Funding::STATUS_FOLDER_CLOSED_LESSOR => $langs->trans('FundingStatusFolderClosedLessorShort')
		);

		return $statusList;
	}

	/**
	 * Get funding scales list
	 *
	 * Return list of scales from llx_c_funding_scale table
	 *
	 * @param string $sortfield Sort field
	 * @param string $sortorder Sort order
	 * @param int $limit Limit for list
	 * @param int $page Page number
	 * @return array Array of scale objects
	 * @phan-return array<int,object>
	 * @phpstan-return array<int,object>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 503 System error
	 *
	 * @url GET /dictionary/scales/
	 */
	public function dictionaryScales($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'read')) {
			throw new RestException(403);
		}

		$obj_ret = array();

		$sql = "SELECT t.rowid, t.code, t.label, t.active";
		$sql .= " FROM ".$this->db->prefix()."c_funding_scale as t";
		$sql .= " WHERE t.active = 1";

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;
			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		$result = $this->db->query($sql);
		if ($result) {
			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($result);
				$obj_ret[] = $obj;
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieving scales list: '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/**
	 * Get funding durations list
	 *
	 * Return list of durations from llx_c_funding_duration table
	 *
	 * @param string $sortfield Sort field
	 * @param string $sortorder Sort order
	 * @param int $limit Limit for list
	 * @param int $page Page number
	 * @return array Array of duration objects
	 * @phan-return array<int,object>
	 * @phpstan-return array<int,object>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 503 System error
	 *
	 * @url GET /dictionary/durations/
	 */
	public function dictionaryDurations($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'read')) {
			throw new RestException(403);
		}

		$obj_ret = array();

		$sql = "SELECT t.rowid, t.code, t.label, t.active";
		$sql .= " FROM ".$this->db->prefix()."c_funding_duration as t";
		$sql .= " WHERE t.active = 1";

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;
			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		$result = $this->db->query($sql);
		if ($result) {
			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($result);
				$obj_ret[] = $obj;
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieving durations list: '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/**
	 * Get funding types list
	 *
	 * Return list of types from llx_c_funding_type table
	 *
	 * @param string $sortfield Sort field
	 * @param string $sortorder Sort order
	 * @param int $limit Limit for list
	 * @param int $page Page number
	 * @return array Array of type objects
	 * @phan-return array<int,object>
	 * @phpstan-return array<int,object>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 503 System error
	 *
	 * @url GET /dictionary/types/
	 */
	public function dictionaryTypes($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'read')) {
			throw new RestException(403);
		}

		$obj_ret = array();

		$sql = "SELECT t.rowid, t.code, t.label, t.active";
		$sql .= " FROM ".$this->db->prefix()."c_funding_type as t";
		$sql .= " WHERE t.active = 1";

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;
			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		$result = $this->db->query($sql);
		if ($result) {
			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($result);
				$obj_ret[] = $obj;
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieving types list: '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/**
	 * Get filtered third parties list
	 *
	 * Return list of third parties (societes) filtered by fk_typent = FUNDING_FILTRE_ORGANIZATION
	 *
	 * @param string $sortfield Sort field
	 * @param string $sortorder Sort order
	 * @param int $limit Limit for list
	 * @param int $page Page number
	 * @return array Array of third party objects
	 * @phan-return array<int,object>
	 * @phpstan-return array<int,object>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 503 System error
	 *
	 * @url GET /organizations/
	 */
	public function organizations($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0)
	{
		global $conf;

		if (!DolibarrApiAccess::$user->hasRight('funding', 'read')) {
			throw new RestException(403);
		}

		if (!isModEnabled('societe')) {
			throw new RestException(403, 'Module societe is not enabled');
		}

		$obj_ret = array();

		// Check if FUNDING_FILTRE_ORGANIZATION is configured
		if (empty($conf->global->FUNDING_FILTRE_ORGANIZATION)) {
			throw new RestException(400, 'FUNDING_FILTRE_ORGANIZATION is not configured');
		}

		$sql = "SELECT t.rowid, t.nom as name, t.name_alias, t.code_client, t.code_fournisseur, t.address, t.zip, t.town, t.fk_pays, t.phone, t.email";
		$sql .= " FROM ".$this->db->prefix()."societe as t";
		$sql .= " WHERE t.fk_typent = ".((int) $conf->global->FUNDING_FILTRE_ORGANIZATION);
		$sql .= " AND t.entity IN (".getEntity('societe').")";

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;
			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		$result = $this->db->query($sql);
		if ($result) {
			$num = $this->db->num_rows($result);
			$i = 0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($result);
				$obj_ret[] = $obj;
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieving third parties list: '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/* END MODULEBUILDER API FUNDING */

	/**
	 * Upload a document for a funding
	 *
	 * @param int   $id             ID of funding
	 * @param array $request_data   Data with file content
	 * @phan-param ?array<string,mixed> $request_data
	 * @phpstan-param ?array<string,mixed> $request_data
	 * @return array
	 * @phan-return array<string,mixed>
	 * @phpstan-return array<string,mixed>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 500 System error
	 *
	 * @url POST fundings/{id}/documents
	 */
	public function postFundingDocument($id, $request_data = null)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'write')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('funding', $id, 'funding_funding')) {
			throw new RestException(403, 'Access to instance id='.$id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->funding->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Funding not found');
		}

		// Check if file data is provided
		if (empty($request_data['file']) || empty($request_data['filename'])) {
			throw new RestException(400, 'File data and filename are required');
		}

		global $conf;
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		// Define upload directory
		$module = 'funding';
		$class = 'funding';
		$upload_dir = $conf->$module->multidir_output[$conf->entity].'/'.$class.'/'.dol_sanitizeFileName($this->funding->ref);

		// Create directory if it doesn't exist
		if (!file_exists($upload_dir)) {
			if (dol_mkdir($upload_dir) < 0) {
				throw new RestException(500, 'Failed to create upload directory: '.$upload_dir);
			}
		}

		// Get file data and filename
		$filedata = base64_decode($request_data['file']);
		$filename = dol_sanitizeFileName($request_data['filename']);
		$filepath = $upload_dir.'/'.$filename;

		// Save the file
		$byteswritten = file_put_contents($filepath, $filedata);
		if ($byteswritten === false) {
			throw new RestException(500, 'Failed to write file to server');
		}

		// Verify file was written correctly
		if (!file_exists($filepath)) {
			throw new RestException(500, 'File was not created on server');
		}

		// Add file to database if there's a specific field for it
		// For now, we just return success with file information
		$fileinfo = array(
			'name' => $filename,
			'path' => $filepath,
			'size' => filesize($filepath),
			'mime_type' => mime_content_type($filepath)
		);

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'File uploaded successfully',
				'file' => $fileinfo
			)
		);
	}

	/**
	 * List documents for a funding
	 *
	 * @param int $id ID of funding
	 * @return array Array of document information
	 * @phan-return array<int,array<string,mixed>>
	 * @phpstan-return array<int,array<string,mixed>>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 503 System error
	 *
	 * @url GET fundings/{id}/documents
	 */
	public function getFundingDocuments($id)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'read')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('funding', $id, 'funding_funding')) {
			throw new RestException(403, 'Access to instance id='.$id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->funding->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Funding not found');
		}

		global $conf;
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$module = 'funding';
		$class = 'funding';
		$upload_dir = $conf->$module->multidir_output[$conf->entity].'/'.$class.'/'.dol_sanitizeFileName($this->funding->ref);

		$files = array();
		if (file_exists($upload_dir)) {
			$filearray = dol_dir_list($upload_dir, 'files');
			foreach ($filearray as $file) {
				$files[] = array(
					'name' => $file['name'],
					'size' => $file['size'],
					'date' => $file['date'],
					'fullname' => $file['fullname']
				);
			}
		}

		return $files;
	}

	/**
	 * Delete a document for a funding
	 *
	 * @param int    $id          ID of funding
	 * @param string $filename    Name of file to delete
	 * @return array
	 * @phan-return array<string,array{code:int,message:string}>
	 * @phpstan-return array<string,array{code:int,message:string}>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 500 System error
	 *
	 * @url DELETE fundings/{id}/documents/{filename}
	 */
	public function deleteFundingDocument($id, $filename)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'delete')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('funding', $id, 'funding_funding')) {
			throw new RestException(403, 'Access to instance id='.$id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->funding->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Funding not found');
		}

		global $conf;
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$module = 'funding';
		$class = 'funding';
		$upload_dir = $conf->$module->multidir_output[$conf->entity].'/'.$class.'/'.dol_sanitizeFileName($this->funding->ref);
		$filepath = $upload_dir.'/'.$filename;

		if (!file_exists($filepath)) {
			throw new RestException(404, 'File not found');
		}

		if (dol_delete_file($filepath) < 0) {
			throw new RestException(500, 'Failed to delete file');
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'File deleted successfully'
			)
		);
	}


	/* BEGIN MODULEBUILDER API RETENTION */
	/**
	 * Get properties of a retention object
	 *
	 * Return an array with retention information
	 *
	 * @param	int		$id				ID of retention
	 * @return  Object					Object with cleaned properties
	 * @phan-return	Retention			Object with cleaned properties
	 * @phpstan-return	Retention			Object with cleaned properties
	 *
	 * @phan-return  Retention
	 *
	 * @url	GET retentions/{id}
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 */
	public function getRetention($id)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'retention', 'read')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('retention', $id, 'funding_retention')) {
			throw new RestException(403, 'Access to instance id='.$id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->retention->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Retention not found');
		}

		return $this->_cleanObjectDatas($this->retention);
	}


	/**
	 * List retentions
	 *
	 * Get a list of retentions
	 *
	 * @param string		   $sortfield			Sort field
	 * @param string		   $sortorder			Sort order
	 * @param int			   $limit				Limit for list
	 * @param int			   $page				Page number
	 * @param string           $sqlfilters          Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
	 * @param string		   $properties			Restrict the data returned to these properties. Ignored if empty. Comma separated list of properties names
	 * @return  array                               Array of Retention objects
	 * @phan-return array<int,Retention>
	 * @phpstan-return array<int,Retention>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 503 System error
	 *
	 * @url	GET /retentions/
	 */
	public function indexRetention($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $sqlfilters = '', $properties = '')
	{
		$obj_ret = array();
		$tmpobject = new Retention($this->db);

		if (!DolibarrApiAccess::$user->hasRight('funding', 'retention', 'read')) {
			throw new RestException(403);
		}

		$socid = DolibarrApiAccess::$user->socid ?: 0;

		$restrictonsocid = 0; // Set to 1 if there is a field socid in table of object

		// If the internal user must only see his customers, force searching by him
		$search_sale = 0;
		if ($restrictonsocid && !DolibarrApiAccess::$user->hasRight('societe', 'client', 'voir') && !$socid) {
			$search_sale = DolibarrApiAccess::$user->id;
		}
		if (!isModEnabled('societe')) {
			$search_sale = 0; // If module thirdparty not enabled, sale representative is something that does not exists
		}

		$sql = "SELECT t.rowid";
		$sql .= " FROM ".$this->db->prefix().$tmpobject->table_element." AS t";
		$sql .= " LEFT JOIN ".$this->db->prefix().$tmpobject->table_element."_extrafields AS ef ON (ef.fk_object = t.rowid)"; // Modification VMR Global Solutions to include extrafields as search parameters in the API GET call, so we will be able to filter on extrafields
		$sql .= " WHERE 1 = 1";
		if ($tmpobject->ismultientitymanaged) {
			$sql .= ' AND t.entity IN ('.getEntity($tmpobject->element).')';
		}
		if ($restrictonsocid && $socid) {
			$sql .= " AND t.fk_soc = ".((int) $socid);
		}
		// Search on sale representative
		if ($search_sale && $search_sale != '-1') {
			if ($search_sale == -2) {
				$sql .= " AND NOT EXISTS (SELECT sc.fk_soc FROM ".$this->db->prefix()."societe_commerciaux as sc WHERE sc.fk_soc = t.fk_soc)";
			} elseif ($search_sale > 0) {
				$sql .= " AND EXISTS (SELECT sc.fk_soc FROM ".$this->db->prefix()."societe_commerciaux as sc WHERE sc.fk_soc = t.fk_soc AND sc.fk_user = ".((int) $search_sale).")";
			}
		}
		if ($sqlfilters) {
			$errormessage = '';
			$sql .= forgeSQLFromUniversalSearchCriteria($sqlfilters, $errormessage);
			if ($errormessage) {
				throw new RestException(400, 'Error when validating parameter sqlfilters -> '.$errormessage);
			}
		}

		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;

			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		$result = $this->db->query($sql);
		$i = 0;
		if ($result) {
			$num = $this->db->num_rows($result);
			while ($i < $num) {
				$obj = $this->db->fetch_object($result);
				$tmp_object = new Retention($this->db);
				if ($tmp_object->fetch($obj->rowid)) {
					$obj_ret[] = $this->_filterObjectProperties($this->_cleanObjectDatas($tmp_object), $properties);
				}
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieving retention list: '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/**
	 * Create retention object
	 *
	 * @param array $request_data   Request data
	 * @phan-param ?array<string,mixed> $request_data
	 * @phpstan-param ?array<string,mixed> $request_data
	 * @return int  				ID of retention
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 500 System error
	 *
	 * @url	POST retentions/
	 */
	public function postRetention($request_data = null)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'retention', 'write')) {
			throw new RestException(403);
		}

		// Check mandatory fields
		$result = $this->_validateRetention($request_data);

		foreach ($request_data as $field => $value) {
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again with the caller @phan-suppress-next-line PhanTypeInvalidDimOffset
				$this->retention->context['caller'] = sanitizeVal((string) $request_data['caller'], 'aZ09');
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->retention->array_options[$index] = $this->_checkValForAPI('extrafields', $val, $this->retention);
				}
				continue;
			}

			$this->retention->$field = $this->_checkValForAPI((string) $field, $value, $this->retention);
		}

		// Clean data
		// $this->retention->abc = sanitizeVal($this->retention->abc, 'alphanohtml');

		if ($this->retention->create(DolibarrApiAccess::$user) < 0) {
			throw new RestException(500, "Error creating Retention", array_merge(array($this->retention->error), $this->retention->errors));
		}
		return $this->retention->id;
	}

	/**
	 * Update retention
	 *
	 * @param 	int   		$id             Id of retention to update
	 * @param 	array 		$request_data   Data
	 * @phan-param ?array<string,mixed>	$request_data
	 * @phpstan-param ?array<string,mixed>	$request_data
	 * @return 	Object						Object after update
	 * @phan-return Retention
	 * @phpstan-return Retention
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 500 System error
	 *
	 * @url	PUT retentions/{id}
	 */
	public function putRetention($id, $request_data = null)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'retention', 'write')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('retention', $id, 'funding_retention')) {
			throw new RestException(403, 'Access to instance id='.$this->retention->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->retention->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Retention not found');
		}

		foreach ($request_data as $field => $value) {
			if ($field == 'id') {
				continue;
			}
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again with the caller
				$this->retention->context['caller'] = sanitizeVal($request_data['caller'], 'aZ09');
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->retention->array_options[$index] = $this->_checkValForAPI('extrafields', $val, $this->retention);
				}
				continue;
			}

			if ($field == 'array_options' && is_array($value)) {
				foreach ($value as $index => $val) {
					$this->retention->array_options[$index] = $this->_checkValForAPI($field, $val, $this->retention);
				}
				continue;
			}

			$this->retention->$field = $this->_checkValForAPI($field, $value, $this->retention);
		}

		// Clean data
		// $this->retention->abc = sanitizeVal($this->retention->abc, 'alphanohtml');

		if ($this->retention->update(DolibarrApiAccess::$user, 0) > 0) {
			return $this->getRetention($id);
		} else {
			throw new RestException(500, $this->retention->error);
		}
	}

	/**
	 * Delete retention
	 *
	 * @param   int     $id   Retention ID
	 * @return  array
	 * @phan-return array<string,array{code:int,message:string}>
	 * @phpstan-return array<string,array{code:int,message:string}>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 409 Nothing to do
	 * @throws RestException 500 System error
	 *
	 * @url	DELETE retentions/{id}
	 */
	public function deleteRetention($id)
	{
		if (!DolibarrApiAccess::$user->hasRight('funding', 'retention', 'delete')) {
			throw new RestException(403);
		}
		if (!DolibarrApi::_checkAccessToResource('retention', $id, 'funding_retention')) {
			throw new RestException(403, 'Access to instance id='.$this->retention->id.' of object not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->retention->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Retention not found');
		}

		if ($this->retention->delete(DolibarrApiAccess::$user) == 0) {
			throw new RestException(409, 'Error when deleting Retention : '.$this->retention->error);
		} elseif ($this->retention->delete(DolibarrApiAccess::$user) < 0) {
			throw new RestException(500, 'Error when deleting Retention : '.$this->retention->error);
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'Retention deleted'
			)
		);
	}


	/**
	 * Validate fields before creating or updating object
	 *
	 * @param	array		$data   Array of data to validate
	 * @phan-param		?array<string,null|int|float|string> $data
	 * @phpstan-param	?array<string,null|int|float|string> $data
	 * @return	array
	 * @phan-return		array<string,null|int|float|string>|array{}
	 * @phpstan-return	array<string,null|int|float|string>|array{}
	 *
	 * @throws	RestException
	 */
	private function _validateRetention($data)
	{
		if (!is_array($data)) {
			$data = array();
		}
		$retention = array();
		foreach ($this->retention->fields as $field => $propfield) {
			if (in_array($field, array('rowid', 'entity', 'date_creation', 'tms', 'fk_user_creat')) || $propfield['notnull'] != 1) {
				continue; // Not a mandatory field
			}
			if (!isset($data[$field])) {
				throw new RestException(400, "$field field missing");
			}
			$retention[$field] = $data[$field];
		}
		return $retention;
	}

	/* END MODULEBUILDER API RETENTION */


	/* BEGIN MODULEBUILDER API MYOBJECT */
	/* END MODULEBUILDER API MYOBJECT */



	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 * Clean sensitive object data fields
	 * @phpstan-template T of Object
	 *
	 * @param   Object  $object     Object to clean
	 * @return  Object              Object with cleaned properties
	 *
	 * @phpstan-param T $object
	 * @phpstan-return T
	 */
	protected function _cleanObjectDatas($object)
	{
		// phpcs:enable
		$object = parent::_cleanObjectDatas($object);

		unset($object->rowid);
		unset($object->canvas);

		return $object;
	}
}
