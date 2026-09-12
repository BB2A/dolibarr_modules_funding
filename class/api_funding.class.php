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
	public $funding_id_reglement;

	/**
	 * Constructor
	 *
	 * @url     GET /
	 */
	public function __construct()
	{
		global $db, $conf;
		$this->db = $db;
		$this->coefficient = new Coefficient($this->db);
		$this->funding = new Funding($this->db);
		$this->retention = new Retention($this->db);

		$this->funding_id_reglement = !empty($conf->global->FUNDING_ID_REGLEMENT) ? $conf->global->FUNDING_ID_REGLEMENT : null;
		$this->funding_validity_month = !empty($conf->global->FUNDING_VALIDITY_MONTH) ? $conf->global->FUNDING_VALIDITY_MONTH : null;
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
	 * Upload a document for a funding - based on sendDocumentFunding method
	 *
	 * @param int   $id             ID of funding
	 * @param string $docfield      Document field name (fundoc1-6 or funfoldoc1-6)
	 * @param array $request_data   Data with file content
	 * @phan-param ?array<string,mixed> $request_data
	 * @phpstan-param ?array<string,mixed> $request_data
	 * @return array
	 * @phan-return array<string,mixed>
	 * @phpstan-return array<string,mixed>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 400 Bad request
	 * @throws RestException 500 System error
	 *
	 * @url POST fundings/{id}/documents/{docfield}
	 */
	public function postFundingDocument($id, $docfield, $request_data = null)
	{
		global $conf, $langs, $user, $db;

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

		// Validate docfield parameter - only allow fundoc1-6 and funfoldoc1-6
		$allowed_docfields = array('fundoc1', 'fundoc2', 'fundoc3', 'fundoc4', 'fundoc5', 'fundoc6', 'funfoldoc1', 'funfoldoc2', 'funfoldoc3', 'funfoldoc4', 'funfoldoc5', 'funfoldoc6');
		if (!in_array($docfield, $allowed_docfields)) {
			throw new RestException(400, 'Invalid document field. Allowed fields are: '.implode(', ', $allowed_docfields));
		}

		// Check if file data is provided
		if (empty($request_data['file']) || empty($request_data['filename'])) {
			throw new RestException(400, 'File data and filename are required');
		}

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		// Define upload directory
		$module = 'funding';
		$class = 'funding';
		$upload_dir = $conf->$module->multidir_output[$conf->entity].'/'.$class.'/'.dol_sanitizeFileName($this->funding->ref);

		// Create directory if it doesn't exist
		if (!file_exists($upload_dir)) {
			if (dol_mkdir($upload_dir) < 0) {
				throw new RestException(500, 'Failed to create upload directory');
			}
		}

		// Get file data and filename from request
		$filedata = base64_decode($request_data['file']);
		$uploaded_filename = dol_sanitizeFileName($request_data['filename']);

		// Save the uploaded file temporarily
		$temp_filepath = $upload_dir.'/'.$uploaded_filename;
		$byteswritten = file_put_contents($temp_filepath, $filedata);
		if ($byteswritten === false) {
			throw new RestException(500, 'Failed to write file to server');
		}

		// Determine the output filename based on the docfield (similar to sendDocumentFunding logic)
		$fileoutputname = dol_string_nospecial(dol_sanitizeFileName(dol_string_nohtmltag($this->funding->ref.'_'.$langs->trans($docfield))));
		$fileoutputname = str_replace(array('\'' , '&nbsp;', ' '), '_', $fileoutputname.'.pdf');
		$final_filepath = $upload_dir.'/'.$fileoutputname;

		// Check file type and convert to PDF if needed (similar to sendDocumentFunding)
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mtype = finfo_file($finfo, $temp_filepath);
		finfo_close($finfo);

		// If it's a PDF, just rename it
		if (strpos($mtype, '/pdf') !== false) {
			$result = dol_move($temp_filepath, $final_filepath);
			if ($result == false) {
				dol_delete_file($temp_filepath);
				throw new RestException(500, 'Failed to rename PDF file');
			}
		} elseif (strpos($mtype, 'image/') === 0) { // If it's an image, convert to PDF
			require_once DOL_DOCUMENT_ROOT . '/core/lib/pdf.lib.php';

			$formatarray = pdf_getFormat();
			$page_largeur = $formatarray['width'];
			$page_hauteur = $formatarray['height'];
			$format = array($page_largeur, $page_hauteur);
			$marge_gauche = isset($conf->global->MAIN_PDF_MARGIN_LEFT) ? $conf->global->MAIN_PDF_MARGIN_LEFT : 10;
			$marge_droite = isset($conf->global->MAIN_PDF_MARGIN_RIGHT) ? $conf->global->MAIN_PDF_MARGIN_RIGHT : 10;
			$marge_haute = isset($conf->global->MAIN_PDF_MARGIN_TOP) ? $conf->global->MAIN_PDF_MARGIN_TOP : 10;
			$marge_basse = isset($conf->global->MAIN_PDF_MARGIN_BOTTOM) ? $conf->global->MAIN_PDF_MARGIN_BOTTOM : 10;

			$pdf = pdf_getInstance($format);
			$pdf->SetMargins($marge_gauche, $marge_haute, $marge_droite);
			$pdf->SetTitle($fileoutputname);
			$pdf->SetAuthor(!empty($conf->global->MAIN_INFO_SOCIETE_NOM)?$conf->global->MAIN_INFO_SOCIETE_NOM:'');
			$pdf->SetCreator($user->getfullname($langs));
			if (class_exists('TCPDF')) {
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
			}

			$pdf->AddPage();
			$pdf->Image($temp_filepath, '', '', $page_largeur - $marge_gauche - $marge_droite);

			$pdf->Output($final_filepath, 'F');
			$pdf->Close();

			dol_delete_file($temp_filepath);
		} else {
			$result = dol_move($temp_filepath, $final_filepath);
			if ($result == false) {
				dol_delete_file($temp_filepath);
				throw new RestException(500, 'Failed to process file');
			}
		}

		// Verify file was created
		if (!file_exists($final_filepath)) {
			throw new RestException(500, 'File was not created on server');
		}

		// Update the database to store the filename in the specified document field
		$checkfield = $docfield.'check';

		if (isset($this->funding->$checkfield)) {
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->funding->table_element." SET ".$docfield." = '".addslashes($fileoutputname)."', ".$checkfield." = NULL WHERE rowid = ".((int) $id);
		} else {
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->funding->table_element." SET ".$docfield." = '".addslashes($fileoutputname)."' WHERE rowid = ".((int) $id);
		}

		$resql = $db->query($sql);
		if (!$resql) {
			dol_delete_file($final_filepath);
			throw new RestException(500, 'Failed to update document field in database: '.$db->lasterror());
		}

		// Update status if needed
		$this->funding->fetch($id);
		if (empty($this->funding->fundoc1check) && empty($this->funding->fundoc2check) &&
			empty($this->funding->fundoc3check) && empty($this->funding->fundoc4check) &&
			empty($this->funding->fundoc5check) && $this->funding->status_folder == $this->funding::STATUS_FOLDER_LACK) {
			$this->funding->setStatusFolder($user, $this->funding::STATUS_FOLDER_LACKOK);
		}

		$fileinfo = array(
			'name' => $fileoutputname,
			'path' => $final_filepath,
			'size' => filesize($final_filepath),
			'mime_type' => mime_content_type($final_filepath),
			'field' => $docfield
		);

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'File uploaded successfully to field '.$docfield,
				'file' => $fileinfo
			)
		);
	}

	/**
	 * Get document for a specific funding field
	 *
	 * @param int $id ID of funding
	 * @param string $docfield Document field name (fundoc1-6 or funfoldoc1-6)
	 * @return array Array of document information
	 * @phan-return array<string,mixed>
	 * @phpstan-return array<string,mixed>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 400 Bad request
	 * @throws RestException 503 System error
	 *
	 * @url GET fundings/{id}/documents/{docfield}
	 */
	public function getFundingDocument($id, $docfield = '')
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

		global $conf, $langs;
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$module = 'funding';
		$class = 'funding';
		$upload_dir = $conf->$module->multidir_output[$conf->entity].'/'.$class.'/'.dol_sanitizeFileName($this->funding->ref);

		$allowed_docfields = array('fundoc1', 'fundoc2', 'fundoc3', 'fundoc4', 'fundoc5', 'fundoc6', 'funfoldoc1', 'funfoldoc2', 'funfoldoc3', 'funfoldoc4', 'funfoldoc5', 'funfoldoc6');

		// If docfield is empty, return all documents
		if (empty($docfield)) {
			$langs->load('funding@funding');
			$documents = array();
			foreach ($allowed_docfields as $field) {
				$filename = $this->funding->$field;
				if (!empty($filename)) {
					$filepath = $upload_dir.'/'.$filename;
					$checkfield = $field.'check';
					$exists = file_exists($filepath);
					$documents[$field] = array(
						'field' => $field,
						'name' => $langs->trans($field),
						'filename' => $filename,
						'path' => $filepath,
						'size' => $exists ? filesize($filepath) : 0,
						'mime_type' => $exists ? mime_content_type($filepath) : null,
						'check' => isset($this->funding->$checkfield) ? $this->funding->$checkfield : null,
						'exists' => $exists
					);
				}
			}
			return array(
				'all_documents' => true,
				'documents' => $documents
			);
		}

		// Validate docfield parameter for single document
		if (!in_array($docfield, $allowed_docfields)) {
			throw new RestException(400, 'Invalid document field. Allowed fields are: '.implode(', ', $allowed_docfields));
		}

		// Get the filename from the database field
		$filename = $this->funding->$docfield;

		if (empty($filename)) {
			return array(
				'field' => $docfield,
				'filename' => null,
				'message' => 'No document associated with field '.$docfield
			);
		}

		$filepath = $upload_dir.'/'.$filename;

		if (!file_exists($filepath)) {
			return array(
				'field' => $docfield,
				'filename' => $filename,
				'message' => 'File referenced but not found on filesystem',
				'exists' => false
			);
		}

		$checkfield = $docfield.'check';

		return array(
			'field' => $docfield,
			'filename' => $filename,
			'path' => $filepath,
			'size' => filesize($filepath),
			'mime_type' => mime_content_type($filepath),
			'check' => isset($this->funding->$checkfield) ? $this->funding->$checkfield : null,
			'exists' => true
		);
	}

	/**
	 * Delete a document for a specific funding field
	 *
	 * @param int    $id          ID of funding
	 * @param string $docfield    Document field name (fundoc1-6 or funfoldoc1-6)
	 * @return array
	 * @phan-return array<string,array{code:int,message:string}>
	 * @phpstan-return array<string,array{code:int,message:string}>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 400 Bad request
	 * @throws RestException 500 System error
	 *
	 * @url DELETE fundings/{id}/documents/{docfield}
	 */
	public function deleteFundingDocument($id, $docfield)
	{
		global $conf, $db;

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

		// Validate docfield parameter
		$allowed_docfields = array('fundoc1', 'fundoc2', 'fundoc3', 'fundoc4', 'fundoc5', 'fundoc6', 'funfoldoc1', 'funfoldoc2', 'funfoldoc3', 'funfoldoc4', 'funfoldoc5', 'funfoldoc6');
		if (!in_array($docfield, $allowed_docfields)) {
			throw new RestException(400, 'Invalid document field. Allowed fields are: '.implode(', ', $allowed_docfields));
		}

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$module = 'funding';
		$class = 'funding';
		$upload_dir = $conf->$module->multidir_output[$conf->entity].'/'.$class.'/'.dol_sanitizeFileName($this->funding->ref);

		// Get the filename from the database field
		$filename = $this->funding->$docfield;

		if (empty($filename)) {
			throw new RestException(404, 'No file associated with field '.$docfield);
		}

		$filepath = $upload_dir.'/'.$filename;

		if (!file_exists($filepath)) {
			throw new RestException(404, 'File not found: '.$filename);
		}

		// Delete the file from filesystem
		if (dol_delete_file($filepath) < 0) {
			throw new RestException(500, 'Failed to delete file from filesystem');
		}

		// Clear the document field in database
		$checkfield = $docfield.'check';

		if (isset($this->funding->$checkfield)) {
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->funding->table_element." SET ".$docfield." = '', ".$checkfield." = NULL WHERE rowid = ".((int) $id);
		} else {
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->funding->table_element." SET ".$docfield." = '' WHERE rowid = ".((int) $id);
		}

		$resql = $db->query($sql);
		if (!$resql) {
			throw new RestException(500, 'Failed to clear document field in database: '.$db->lasterror());
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'Document field '.$docfield.' cleared successfully'
			)
		);
	}

	/**
	 * Mark a funding document field as requested (asked) - based on sendDocumentFunding method
	 *
	 * This mirrors the "filecheck" behaviour of funding_card.php: it sets the
	 * corresponding fundoc{N}check flag to 1 and moves the folder status to
	 * STATUS_FOLDER_LACK. As in the card, the request can only be set when the
	 * matching document is not yet uploaded (its filename field is empty).
	 *
	 * @param int    $id          ID of funding
	 * @param string $docfield    Document field name (fundoc1-6 or funfoldoc1-6)
	 * @return array
	 * @phan-return array<string,mixed>
	 * @phpstan-return array<string,mixed>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 400 Bad request
	 * @throws RestException 409 Conflict - document already provided
	 * @throws RestException 500 System error
	 *
	 * @url POST fundings/{id}/documents/{docfield}/request
	 */
	public function postFundingDocumentRequest($id, $docfield)
	{
		global $conf, $langs, $db;

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

		// Validate docfield parameter - only allow fundoc1-6 and funfoldoc1-6
		$allowed_docfields = array('fundoc1', 'fundoc2', 'fundoc3', 'fundoc4', 'fundoc5', 'fundoc6', 'funfoldoc1', 'funfoldoc2', 'funfoldoc3', 'funfoldoc4', 'funfoldoc5', 'funfoldoc6');
		if (!in_array($docfield, $allowed_docfields)) {
			throw new RestException(400, 'Invalid document field. Allowed fields are: '.implode(', ', $allowed_docfields));
		}

		// The request flag only exists for fundoc1-6 (funfoldoc1-6 have no check field)
		$checkfield = $docfield.'check';
		if (!property_exists($this->funding, $checkfield)) {
			throw new RestException(400, 'Document field '.$docfield.' does not support a request flag');
		}

		// As in funding_card.php, the request can only be set when the document is not yet provided
		if (!empty($this->funding->$docfield)) {
			throw new RestException(409, 'Document already provided for field '.$docfield.'; cannot mark it as requested');
		}

		$langs->load('funding@funding');

		// Mark document request as required: SET fundoc{N}check = 1
		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->funding->table_element." SET ".$checkfield." = 1 WHERE rowid = ".((int) $id);
		$resql = $db->query($sql);
		if (!$resql) {
			throw new RestException(500, 'Failed to mark document field as requested: '.$db->lasterror());
		}
		$this->funding->fetch($id);

		// Move the folder status to STATUS_FOLDER_LACK when a document is requested
		$this->funding->setStatusFolder(DolibarrApiAccess::$user, $this->funding::STATUS_FOLDER_LACK);

		return array(
			'success' => array(
				'code' => 200,
				'message' => $langs->trans('FilesChecked'),
				'field' => $docfield,
				'check' => 1,
				'status_folder' => $this->funding->status_folder
			)
		);
	}

	/**
	 * Cancel a funding document field request (no longer requested) - based on sendDocumentFunding method
	 *
	 * This mirrors the uncheck behaviour of funding_card.php: it clears the
	 * corresponding fundoc{N}check flag (sets it to NULL). Only applies when the
	 * matching document is not yet uploaded.
	 *
	 * @param int    $id          ID of funding
	 * @param string $docfield    Document field name (fundoc1-6 or funfoldoc1-6)
	 * @return array
	 * @phan-return array<string,mixed>
	 * @phpstan-return array<string,mixed>
	 *
	 * @throws RestException 403 Not allowed
	 * @throws RestException 404 Not found
	 * @throws RestException 400 Bad request
	 * @throws RestException 409 Conflict - document already provided
	 * @throws RestException 500 System error
	 *
	 * @url DELETE fundings/{id}/documents/{docfield}/request
	 */
	public function deleteFundingDocumentRequest($id, $docfield)
	{
		global $conf, $langs, $db;

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

		// Validate docfield parameter - only allow fundoc1-6 and funfoldoc1-6
		$allowed_docfields = array('fundoc1', 'fundoc2', 'fundoc3', 'fundoc4', 'fundoc5', 'fundoc6', 'funfoldoc1', 'funfoldoc2', 'funfoldoc3', 'funfoldoc4', 'funfoldoc5', 'funfoldoc6');
		if (!in_array($docfield, $allowed_docfields)) {
			throw new RestException(400, 'Invalid document field. Allowed fields are: '.implode(', ', $allowed_docfields));
		}

		// The request flag only exists for fundoc1-6 (funfoldoc1-6 have no check field)
		$checkfield = $docfield.'check';
		if (!property_exists($this->funding, $checkfield)) {
			throw new RestException(400, 'Document field '.$docfield.' does not support a request flag');
		}

		// As in funding_card.php, the uncheck only applies when the document is not yet provided
		if (!empty($this->funding->$docfield)) {
			throw new RestException(409, 'Document already provided for field '.$docfield.'; cannot cancel the request');
		}

		$langs->load('funding@funding');

		// Mark document request as not required: SET fundoc{N}check = NULL
		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->funding->table_element." SET ".$checkfield." = NULL WHERE rowid = ".((int) $id);
		$resql = $db->query($sql);
		if (!$resql) {
			throw new RestException(500, 'Failed to cancel document field request: '.$db->lasterror());
		}
		$this->funding->fetch($id);

		// If no more requested document, restore STATUS_FOLDER_LACKOK when status was LACK
		if (empty($this->funding->fundoc1check) && empty($this->funding->fundoc2check) &&
			empty($this->funding->fundoc3check) && empty($this->funding->fundoc4check) &&
			empty($this->funding->fundoc5check) && $this->funding->status_folder == $this->funding::STATUS_FOLDER_LACK) {
			$this->funding->setStatusFolder(DolibarrApiAccess::$user, $this->funding::STATUS_FOLDER_LACKOK);
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => $langs->trans('FilesUnChecked'),
				'field' => $docfield,
				'check' => null,
				'status_folder' => $this->funding->status_folder
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
