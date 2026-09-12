<?php
/* Copyright (C) 2017 		Laurent Destailleur  	<eldy@users.sourceforge.net>
 * Copyright (C) 2020-2026	Anthony Berton 			<anthony.berton@bb2a.fr>
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

/**
 * \file        class/funding.class.php
 * \ingroup     funding
 * \brief       This file is a CRUD class file for Funding (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for Funding
 */
class Funding extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'funding';

	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'funding';

	/**
	 * @var string		Prefix to check for any trigger code of any business class to prevent bad value for trigger code.
	 * @see CommonTrigger::call_trigger()
	 */
	public $TRIGGER_PREFIX = 'FUNDING_FUNDING';	// Will be used to build trgiger keys 'FUNDING_FUNDING_MODIFY', ...

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'funding_funding';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for funding. Must be the part after the 'object_' into object_funding.png
	 */
	public $picto = 'fa-piggy-bank infobox-action';
	/**
	* @var array<int,string>		Array with labels of status
	*/
	public $labelStatusFolder = array();

	/**
	 * @var array<int,string>	Array with short labels of status
	 */
	public $labelStatusFolderShort = array();

	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_UPDATE = 2;
	const STATUS_ACCEPT = 4;
	const STATUS_DENIED = 5;
	const STATUS_RUNNING = 6;
	const STATUS_END = 7;
	const STATUS_CANCELED = 8;

	const STATUS_FOLDER_SENDORG = 1;
	const STATUS_FOLDER_LACK = 2;
	const STATUS_FOLDER_LACKOK = 3;
	const STATUS_FOLDER_ACCEPT_RETENTION = 5;
	const STATUS_FOLDER_EXTENSION = 9;

	// STATUS_FOLDER_REDEEMED passage 8 en 20
	const STATUS_FOLDER_REDEEMED = 20;
	// STATUS_FOLDER_REDEEMED passage 7 en 21
	const STATUS_FOLDER_DENOUNCED = 21;
	const STATUS_FOLDER_CLOSED_TRANSFER = 22;
	const STATUS_FOLDER_CLOSED_LESSOR = 23;


	/**
	 *  'type' field format:
	 *  	'integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]',
	 *  	'select' (list of values are in 'options'),
	 *  	'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:Sortfield]]]]',
	 *  	'chkbxlst:...',
	 *  	'varchar(x)',
	 *  	'text', 'text:none', 'html',
	 *  	'double(24,8)', 'real', 'price',
	 *  	'date', 'datetime', 'timestamp', 'duration',
	 *  	'boolean', 'checkbox', 'radio', 'array',
	 *  	'mail', 'phone', 'url', 'password', 'ip'
	 *  	Note: Filter must be a Dolibarr Universal Filter syntax string. Example: "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.status:!=:0) or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM' or 'isModEnabled("multicurrency")' ...)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'alwayseditable' says if field can be modified also when status is not draft ('1' or '0')
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' and 'helplist' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if need to validate with $this->validateField()
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'comment'=>"Id"),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref.', 'enabled'=>'1', 'position'=>2, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
		'entity' =>array('type'=>'integer', 'label'=>'Entity', 'default'=>1, 'enabled'=>1, 'visible'=>-2, 'notnull'=>1, 'position'=>20, 'index'=>1),
		'study_number' => array('type'=>'varchar(128)', 'label'=>'StudyNumber', 'enabled'=>'1', 'position'=>3, 'notnull'=>0, 'visible'=>2, 'index'=>1, 'searchall'=>1, 'help'=>"Help_studyNumber", 'showoncombobox'=>'1',),
		'folder_number' => array('type'=>'varchar(128)', 'label'=>'FolderNumber', 'enabled'=>'1', 'position'=>4, 'notnull'=>0, 'visible'=>2, 'index'=>1, 'searchall'=>1, 'help'=>"Help_folderNumber", 'showoncombobox'=>'1',),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php::((status:=:1) AND (entity:IN:__SHARED_ENTITIES__))', 'label'=>'ThirdParty', 'picto'=>'company', 'enabled'=>'isModEnabled("societe")', 'position'=>5, 'notnull'=>1, 'visible'=>-2, 'noteditable'=>'1', 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx', 'csslist'=>'tdoverflowmax150', 'showoncombobox'=>'1', 'help'=>"Help_linkToThirparty",),
		'fk_soc_invoice' => array('type'=>'integer:Societe:societe/class/societe.class.php::((status:=:1) AND (entity:IN:__SHARED_ENTITIES__))', 'label'=>'ThirdPartyInvoice', 'picto'=>'company', 'enabled'=>'isModEnabled("societe")', 'position'=>6, 'notnull'=>0, 'visible'=>-5, 'noteditable'=>'1', 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx', 'csslist'=>'tdoverflowmax150', 'showoncombobox'=>'1', 'help'=>"Help_linkToThirpartyInvoice",),
		'fk_org' => array('type'=>'integer:Societe:societe/class/societe.class.php::((status:=:1) AND (entity:IN:__SHARED_ENTITIES__))', 'label'=>'Organization', 'picto'=>'company', 'enabled'=>'isModEnabled("societe")', 'position'=>7, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx', 'csslist'=>'tdoverflowmax150', 'help'=>"Help_linkToOrganization", 'validate'=>'1',),
		'amount' => array('type'=>'price', 'label'=>'Amount', 'picto'=>'percent', 'enabled'=>'1', 'position'=>8, 'notnull'=>0, 'visible'=>5, 'noteditable'=>'1', 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help_amount", 'copytoclipboard'=>2,),
		'amount_maint' => array('type'=>'price', 'label'=>'AmountMaint', 'enabled'=>'1', 'position'=>9, 'notnull'=>0, 'visible'=>1, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help_amountMaint", 'copytoclipboard'=>2,),
		'amount_total' => array('type'=>'price', 'label'=>'AmountTotal', 'enabled'=>'1', 'position'=>10, 'notnull'=>0, 'visible'=>5, 'noteditable'=>'1', 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help_amountTotal", 'copytoclipboard'=>2,),
		'fk_duration' => array('type'=>'integer', 'label'=>'Duration', 'enabled'=>'1', 'position'=>11, 'notnull'=>1, 'visible'=>-1, 'foreignkey'=>'c_funding_duration.rowid', 'help'=>"Help_duration", 'arrayofkeyval'=>array('1'=>'12 Mois', '2'=>'24 Mois', '3'=>'36 Mois', '4'=>'48 Mois', '5'=>'60 Mois'), 'copytoclipboard'=>2,),
		'coef' => array('type'=>'real', 'label'=>'Coef', 'enabled'=>'1', 'position'=>12, 'notnull'=>0, 'visible'=>-5, 'noteditable'=>'1', 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp', 'help'=>"Help_coef", 'copytoclipboard'=>2,),
		'fk_scale' => array('type'=>'integer', 'label'=>'Scale', 'enabled'=>'1', 'position'=>13, 'notnull'=>1, 'visible'=>-1, 'foreignkey'=>'c_funding_scale.rowid', 'help'=>"Help_scale", 'arrayofkeyval'=>array('1'=>'1 - Standard', '2'=>'2 - Création'),),
		'amount_rent' => array('type'=>'price', 'label'=>'Rent', 'enabled'=>'1', 'position'=>14, 'notnull'=>0, 'visible'=>5, 'copytoclipboard'=>2,),
		'amount_rent_edit' => array('type'=>'price', 'label'=>'RentEdit', 'enabled'=>'0', 'position'=>15, 'notnull'=>0, 'visible'=>5, 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help_amountRentEdit", 'copytoclipboard'=>2,),
		'date_accepted' => array('type'=>'date', 'label'=>'DateAccepted', 'enabled'=>'1', 'position'=>16, 'notnull'=>0, 'visible'=>2, 'noteditable'=>'1', 'searchall'=>1, 'help'=>"Help_dateAccepted",),
		'date_endvalidity' => array('type'=>'date', 'label'=>'DateEndValidity', 'enabled'=>'1', 'position'=>17, 'notnull'=>0, 'visible'=>2, 'noteditable'=>'1', 'searchall'=>1, 'help'=>"Help_dateAcceptedEnd",),
		'date_delivery' => array('type'=>'date', 'label'=>'DateDelivery', 'enabled'=>'1', 'position'=>18, 'notnull'=>0, 'visible'=>5, 'noteditable'=>'1', 'searchall'=>1, 'help'=>"Help_dateDelivery",),
		'date_end_calculated' => array('type'=>'date', 'label'=>'DateEndCalculated', 'enabled'=>'1', 'position'=>20, 'notnull'=>17, 'visible'=>5, 'noteditable'=>'1', 'help'=>"Help_dateEndCalculated",),
		'date_signature' => array('type'=>'date', 'label'=>'DateSignature', 'enabled'=>'1', 'position'=>19, 'notnull'=>0, 'visible'=>-4, 'noteditable'=>'0', 'searchall'=>1, 'help'=>"Help_dateSignature",),
		'date_end' => array('type'=>'date', 'label'=>'DateEnd', 'enabled'=>'1', 'position'=>20, 'notnull'=>17, 'visible'=>5, 'noteditable'=>'1', 'help'=>"Help_dateEnd",),
		'fk_funding_type' => array('type'=>'smallint', 'label'=>'TypeFunding', 'enabled'=>'1', 'position'=>21, 'notnull'=>1, 'visible'=>-1, 'foreignkey'=>'c_funding_type.rowid', 'arrayofkeyval'=>array('2'=>'Crédit bail', '1'=>'Location'),),
		'redemption' => array('type'=>'boolean', 'label'=>'Redemption', 'enabled'=>'1', 'position'=>22, 'notnull'=>0, 'visible'=>-1,),
		'redemption_number' => array('type'=>'varchar(128)', 'label'=>'RedemptionNumber', 'enabled'=>'1', 'position'=>23, 'notnull'=>0, 'visible'=>-1, 'index'=>1, 'searchall'=>1, 'help'=>"Help_redemptionNumber", 'showoncombobox'=>'1',),
		'retention' => array('type'=>'boolean', 'label'=>'RetentionOfGuarantee', 'enabled'=>'1', 'position'=>24, 'notnull'=>0, 'visible'=>-1, 'default'=>0, 'help'=>"Help_retention",),
		'retention_rate' => array('type'=>'real', 'label'=>'RetentionRate', 'enabled'=>'1', 'position'=>25, 'notnull'=>0, 'visible'=>-5, 'noteditable'=>'1', 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp', 'help'=>"Help_retentionRate", 'copytoclipboard'=>2,),
		'retention_mount' => array('type'=>'price', 'label'=>'RetentionMount', 'enabled'=>'1', 'position'=>26, 'notnull'=>0, 'visible'=>5, 'noteditable'=>'1', 'default'=>'null', 'isameasure'=>'1', 'help'=>"Help_retentionMount", 'copytoclipboard'=>2,),
		'fk_user_comm' => array('type'=>'integer:User:user/class/user.class.php::(entity:IN:__SHARED_ENTITIES__)', 'label'=>'SalesRepresentative', 'picto'=>'user', 'enabled'=>'1', 'position'=>27, 'notnull'=>0, 'visible'=>-4, 'foreignkey'=>'user.rowid', 'css'=>'maxwidth250 widthcentpercentminusxx', 'csslist'=>'tdoverflowmax150'),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>100, 'notnull'=>0, 'visible'=>-1, 'noteditable'=>'0', 'alwayseditable'=>'1',),
		'fundoc1' => array('type'=>'varchar(255)', 'label'=>'fundoc1', 'enabled'=>'1', 'position'=>101, 'notnull'=>0, 'visible'=>-2,),
		'fundoc1check' => array('type'=>'smallint', 'label'=>'fundoc1check', 'enabled'=>'1', 'position'=>101, 'notnull'=>0, 'visible'=>0,),
		'fundoc2' => array('type'=>'varchar(255)', 'label'=>'fundoc2', 'enabled'=>'1', 'position'=>102, 'notnull'=>0, 'visible'=>0,),
		'fundoc2check' => array('type'=>'smallint', 'label'=>'fundoc2check', 'enabled'=>'1', 'position'=>102, 'notnull'=>0, 'visible'=>0,),
		'fundoc3' => array('type'=>'varchar(255)', 'label'=>'fundoc3', 'enabled'=>'1', 'position'=>103, 'notnull'=>0, 'visible'=>0,),
		'fundoc3check' => array('type'=>'smallint', 'label'=>'fundoc3check', 'enabled'=>'1', 'position'=>103, 'notnull'=>0, 'visible'=>0,),
		'fundoc4' => array('type'=>'varchar(255)', 'label'=>'fundoc4', 'enabled'=>'1', 'position'=>104, 'notnull'=>0, 'visible'=>0,),
		'fundoc4check' => array('type'=>'smallint', 'label'=>'fundoc4check', 'enabled'=>'1', 'position'=>104, 'notnull'=>0, 'visible'=>0,),
		'fundoc5' => array('type'=>'varchar(255)', 'label'=>'fundoc5', 'enabled'=>'1', 'position'=>105, 'notnull'=>0, 'visible'=>0,),
		'fundoc5check' => array('type'=>'smallint', 'label'=>'fundoc5check', 'enabled'=>'1', 'position'=>105, 'notnull'=>0, 'visible'=>0,),
		'fundoc6' => array('type'=>'varchar(255)', 'label'=>'fundoc6', 'enabled'=>'1', 'position'=>106, 'notnull'=>0, 'visible'=>0,),
		'fundoc6check' => array('type'=>'smallint', 'label'=>'fundoc6check', 'enabled'=>'1', 'position'=>106, 'notnull'=>0, 'visible'=>0,),
		'funfoldoc1' => array('type'=>'varchar(255)', 'label'=>'funfoldoc1', 'enabled'=>'1', 'position'=>110, 'notnull'=>0, 'visible'=>0,),
		'funfoldoc2' => array('type'=>'varchar(255)', 'label'=>'funfoldoc2', 'enabled'=>'1', 'position'=>111, 'notnull'=>0, 'visible'=>0,),
		'funfoldoc3' => array('type'=>'varchar(255)', 'label'=>'funfoldoc3', 'enabled'=>'1', 'position'=>112, 'notnull'=>0, 'visible'=>0,),
		'funfoldoc4' => array('type'=>'varchar(255)', 'label'=>'funfoldoc4', 'enabled'=>'1', 'position'=>113, 'notnull'=>0, 'visible'=>0,),
		'funfoldoc5' => array('type'=>'varchar(255)', 'label'=>'funfoldoc5', 'enabled'=>'1', 'position'=>114, 'notnull'=>0, 'visible'=>0,),
		'funfoldoc6' => array('type'=>'varchar(255)', 'label'=>'funfoldoc6', 'enabled'=>'1', 'position'=>115, 'notnull'=>0, 'visible'=>0,),
		'extension' => array('type'=>'smallint', 'label'=>'extension', 'enabled'=>'1', 'position'=>201, 'default'=>0, 'visible'=>0, 'arrayofkeyval'=>array('0'=>'No', '1'=>'Yes'),),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>400, 'notnull'=>0, 'visible'=>0, "cssview" => "wordbreak", 'alwayseditable'=>'0', 'copytoclipboard'=>2,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>401, 'notnull'=>0, 'visible'=>0, "cssview" => "wordbreak", 'alwayseditable'=>'0', 'copytoclipboard'=>2,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid', 'showoncombobox'=>'1',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2, 'showoncombobox'=>'1',),
		'origin' => array('type'=>'varchar(128)', 'label'=>'origin', 'enabled'=>'1', 'position'=>512, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'searchall'=>1,),
		'origin_id' => array('type'=>'integer', 'label'=>'origin_id', 'enabled'=>'1', 'position'=>513, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'searchall'=>1,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>0, 'default'=>'',),
		'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>'1', 'position'=>1010, 'notnull'=>-1, 'visible'=>0,),
		'last_main_doc' => array('type'=>'varchar(255)', 'label'=>'last_main_doc', 'enabled'=>'1', 'position'=>10, 'notnull'=>0, 'visible'=>0,),
		'billed' => array('type'=>'integer', 'label'=>'billed', 'enabled'=>'1', 'position'=>514, 'notnull'=>0, 'visible'=>-2, 'noteditable'=>'1', 'index'=>1, 'searchall'=>1,),
		'funcheck' => array('type'=>'smallint', 'label'=>'Checked', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>-2, 'default'=>'0', 'css'=>'center','arrayofkeyval'=>array('0'=>'No', '1'=>'Yes'),),
		'status_folder' => array('type'=>'smallint', 'label'=>'StatusFolder', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>2, 'default'=>'0', 'index'=>1, 'noteditable'=>'1', 'showoncombobox'=>'1', 'arrayofkeyval'=>array('1' => 'FundingStatusFolderSendOrgShort', '2' => 'FundingStatusFolderLackShort', '5' => 'FundingStatusFolderAcceptRetentionShort', '7' => 'FundingStatusFolderDenouncedShort', '8' => 'FundingStatusFolderRedeemedShort', '9' => 'FundingStatusFolderExtensionShort'),),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>2, 'default'=>'0', 'index'=>1, 'noteditable'=>'1', 'showoncombobox'=>'1', 'arrayofkeyval'=>array('0'=>'FundingStatusDraftShort', '1'=>'FundingStatusValidatedShort', '2'=>'FundingStatusUpdateShort',/* '3'=>'FundingStatusSendOrgShort', */'4'=>'FundingStatusAcceptShort', '5'=>'FundingStatusDeniedShort', '6'=>'FundingStatusRunningShort', '7'=>'FundingStatusEndShort', '8'=>'FundingStatusDisabledShort'),),
	);
	public $rowid;
	public $ref;
	public $entity;
	public $study_number;
	public $folder_number;
	public $fk_soc;
	public $fk_soc_invoice;
	public $amount;
	public $amount_maint;
	public $amount_total;
	public $fk_duration;
	public $coef;
	public $fk_scale;
	public $amount_rent;
	public $amount_rent_edit;
	public $date_delivery;
	public $date_end_calculated;
	public $date_signature;
	public $date_end;
	public $fk_funding_type;
	public $redemption;
	public $redemption_number;
	public $retention;
	public $retention_rate;
	public $retention_mount;
	public $fk_org;
	public $fk_user_comm;
	public $description;
	public $fundoc1;
	public $fundoc1check;
	public $fundoc2;
	public $fundoc2check;
	public $fundoc3;
	public $fundoc3check;
	public $fundoc4;
	public $fundoc4check;
	public $fundoc5;
	public $fundoc5check;
	public $fundoc6;
	public $fundoc6check;
	public $funfoldoc1;
	public $funfoldoc2;
	public $funfoldoc3;
	public $funfoldoc4;
	public $funfoldoc5;
	public $funfoldoc6;
	public $extension;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $origin;
	public $origin_id;
	public $last_main_doc;
	public $import_key;
	public $model_pdf;
	public $billed;
	public $funcheck;
	public $status_folder;
	public $status;
	// END MODULEBUILDER PROPERTIES


	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		// Force display to 3 decimals
		$conf->global->MAIN_MAX_DECIMALS_SHOWN = 3;

		// Backward compatibility
		if (version_compare(DOL_VERSION, '17.0.0', '<')) {
			$this->fields['fk_soc']['type'] = 'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)';
			$this->fields['fk_soc_invoice']['type'] = 'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)';
			$this->fields['fk_org']['type'] = 'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)';
		}

		if (!empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 1;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Activation of custom rent
		if (!empty($conf->global->FUNDING_ENABLED_RENTEDIT)) {
			$this->fields['amount_rent_edit']['enabled'] = 1;
		}
		if (!empty($conf->global->FUNDING_FILTRE_ORGANIZATION) && $conf->global->FUNDING_FILTRE_ORGANIZATION > 0 && isset($this->fields['fk_org'])) {
			if (DOL_VERSION < '17.0.0') {
				$this->fields['fk_org']['type'] .= " AND fk_typent=".$conf->global->FUNDING_FILTRE_ORGANIZATION;
			} else {
				$this->fields['fk_org']['type'] .= " AND (fk_typent:=:".$conf->global->FUNDING_FILTRE_ORGANIZATION.")";
			}
		}
		if (GETPOST('action', 'alpha') == 'edit') {
			$this->fields['amount_rent_edit']['visible'] = 1 & $this->fields['amount_rent']['visible'] = 1;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}
		// Fix PHP8 add "isset($val['arrayofkeyval']) &&"
		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (isset($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}

		// Load the duration dictionary
		$arrayofkeyval=array();
		$sql = 'SELECT c.rowid, c.code, c.label, c.active';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'c_funding_duration as c';
		$sql.= ' WHERE c.active = 1';
		$sql.= ' ORDER BY c.label ASC';

		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			if ($num > 0) {
				$arrayofkeyval = array();
				$i = 0;
				while ($i < $num) {
					$obj = $db->fetch_object($resql);
					$arrayofkeyval[$obj->rowid] = $obj->label;
					$i = $i +1;
				}
				$this->fields['fk_duration']['arrayofkeyval'] = $arrayofkeyval;
			}
			$db->free($resql);
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}

		// Load the scale dictionary
		$arrayofkeyval=array();
		$sql = 'SELECT c.rowid, c.code, c.label, c.active';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'c_funding_scale as c';
		$sql.= ' WHERE c.active = 1';
		$sql.= ' ORDER BY c.label ASC';

		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			if ($num > 0) {
				$arrayofkeyval = array();
				$i = 0;
				while ($i < $num) {
					$obj = $db->fetch_object($resql);
					$arrayofkeyval[$obj->rowid] = $obj->label;
					$i = $i +1;
				}
				$this->fields['fk_scale']['arrayofkeyval'] = $arrayofkeyval;
			}
			$db->free($resql);
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}

		// Load the type dictionary
		$arrayofkeyval=array();
		$sql = 'SELECT c.rowid, c.code, c.label, c.active';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'c_funding_type as c';
		$sql.= ' WHERE c.active = 1';
		$sql.= ' ORDER BY c.label ASC';

		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			if ($num > 0) {
				$arrayofkeyval = array();
				$i = 0;
				while ($i < $num) {
					$obj = $db->fetch_object($resql);
					$arrayofkeyval[$obj->rowid] = $obj->label;
					$i = $i +1;
				}
				$this->fields['fk_funding_type']['arrayofkeyval'] = $arrayofkeyval;
			}
			$db->free($resql);
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Fetch the third party salespeople
	 *
	 * @param  int      $socid          id thirdparty
	 * @return                          $idcomm = ok or -1 = nok
	 */
	public function commtiers($socid)
	{
		global $conf, $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe_commerciaux";
		$sql.= " WHERE fk_soc = ".$socid;
		$sql.= " ORDER BY rowid";
		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			$idcommercial = $row[2];
			return $idcommercial;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Fetch document information
	 *
	 * @param  int      $iddoc              id du document
	 * @param  string   $typedoc            Type de document PROPAL ORDER
	 * @return                              $document = ok or -1 = nok
	 */
	public function infodoc($iddoc, $typedoc)
	{
		global $conf, $db;

		$document = '';

		if ($typedoc == 'propal') {
			$document = new propal($db);
		}
		if ($typedoc == 'order') {
			$document = new commande($db);
		}

		$document->fetch($iddoc);

		if ($document) {
			return $document;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Fetch the corresponding coefficient
	 *
	 * @param  real     $total          Total to finance
	 * @param  real     $duration       Duration of financing
	 * @param  real     $scale          The rate
	 * @param  int      $org            Funding organization
	 * @return                          $coef = ok or -1 = nok
	 */
	public function searchCoef($total, $duration, $scale, $org)
	{
		global $conf, $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX.'funding_coefficient as c';
		$sql.= ' WHERE c.status = 1';
		$sql.= ' AND c.fk_org = '.$org;
		$sql.= ' AND c.fk_duration = '.$duration;
		$sql.= ' AND c.fk_scale = '.$scale;
		$sql.= ' AND c.amount_of <= '.$total;
		$sql.= ' AND c.amount_to >= '.$total;
		$resql = $db->query($sql);

		if ($resql) {
			$obj = $db->fetch_object($resql);
			return isset($obj->coef) ? $obj->coef : 0;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Fetch the retention guarantee rate
	 *
	 * @param   int     $org            Funding organization
	 * @return                          $rate = ok or -1 = nok
	 */
	public function searchRetentionRate($org)
	{
		global $conf, $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX.'funding_retention as c';
		$sql.= ' WHERE c.status = 1';
		$sql.= ' AND c.fk_soc = '.$org;
		$resql = $db->query($sql);

		if ($resql) {
			$obj = $db->fetch_object($resql);

			if (is_object($obj)) {
				return $obj->rate;
			} else {
				return 0;
			}
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = 0)
	{
		global $langs, $conf;
		$now        = dol_now();
		$typedoc    = GETPOST('typedoc', 'alpha');
		$iddoc      = GETPOST('iddoc', 'int');

		$coef = -1;
		$document = -1;
		$idcomm = -1;
		$duration = -1;

		$this->ref = $this->getNextNumRef();

		// Initialise les informations obligatoire non editable
		// Document
		if ($iddoc && $typedoc) {
			$this->origin = $typedoc;
			$this->origin_id = $iddoc;
			$document = $this->infodoc($iddoc, $typedoc);
			if (is_object($document)) {
				// Fetch if a different billing address exists
				$socpeopleinvoice   = $document->getIdContact('external', 'BILLING');
				if ($socpeopleinvoice) {
					$socinvoice         = $this->fetchSocinvoice($socpeopleinvoice[0]);
					if ($socinvoice > -1) {
						$this->fk_soc_invoice = $socinvoice;
					} else {
						setEventMessages($langs->trans("socinvoicenok".'-'.$socpeopleinvoice[0]), null, 'errors');
					}
				} else {
					$this->fk_soc_invoice = '';
				}

				$this->fk_soc       = $document->socid;
				$this->amount       = $document->total_ht;
				$this->amount_total = empty($this->amount_maint) ? $document->total_ht : $document->total_ht + $this->amount_maint;
				if ($this->retention == 1) {
					$this->retention_rate = $this->searchRetentionRate($this->fk_org);
					$this->retention_mount = price2num($this->amount_total / (1-($this->retention_rate/100)), 'MT') - $this->amount_total;
					$this->amount_total = price2num($this->amount_total / (1-($this->retention_rate/100)), 'MT');
				} else {
					$this->retention_rate = '';
					$this->retention_mount = '';
				}
				$coef = $this->searchCoef($this->amount_total, $this->fk_duration, $this->fk_scale, $this->fk_org);
				if ($coef > 0) {
					$this->coef         = $coef;
					$this->amount_rent  = price2num($this->amount_total * $coef / 100, 'MT');
					$this->amount_rent_edit = $this->amount_rent;

					// Information sur date de livraison date de fin
					$this->date_delivery = isset($document->date_livraison)?$document->date_livraison:'';

					// Commercial
					$idcomm = $this->commtiers($document->socid);
					if ($idcomm > 0) {
						$this->fk_user_comm = $idcomm;
						$this->status = self::STATUS_DRAFT;
						// Creating funding from an order passes directly to validated
						if ($document->mode_reglement_id == $conf->global->FUNDING_ID_REGLEMENT && $this->origin == 'order') {
							$this->status = self::STATUS_VALIDATED;
						}
						$create = $this->createCommon($user, $notrigger);
						if ($create > 0) {
							// Add object linked
							$ret = $this->add_object_linked($typedoc, $iddoc);
							if (!$ret) {
								$this->error = $this->db->lasterror();
								$error++;
							}
							// Create ref
							// Problem: does not retrieve document info with update afterwards
							//$this->update($user, true); //No trigger
							//$this->ref = "(PROV".$this->id.")";
							$this->date_creation = $now;
							//$this->validate($user);
						}
						return $create;
					} else {
						setEventMessages($langs->trans("commnok"), null, 'errors');
					}
				} else {
					setEventMessages($langs->trans("coefnok"), null, 'errors');
				}
			} else {
				setEventMessages($langs->trans("documentnotvalidated"), null, 'errors');
			}
		} else {
			setEventMessages($langs->trans("paramnok"), null, 'errors');
		}
	}

	/**
	 * Clone an object into another one
	 *
	 * @param   User    $user       User that creates
	 * @param   int     $fromid     Id of object to clone
	 * @param   text    $origin     Type of origin (propal, order, invoice)
	 * @param   int     $origin_id  Id of origin object to link to
	 * @param   int     $fk_soc     Id of third party to link to
	 * @param   int     $type       Type of clone 0=clone, 1=clone no documents, 2=1 + clean references
	 * @param   int     $notrigger  false=launch triggers after, true=disable triggers
	 * @return  mixed               New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid, $origin = '', $origin_id = 0, $fk_soc = 0, $type = 0, $notrigger = 0)
	{
		global $conf, $db, $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);
		$now = dol_now();
		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		$oldref = $object->ref;

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);
		unset($object->amount_rent_edit);

		if ($type >= 2) {
			// Reset some properties
			unset($object->study_number);
			unset($object->folder_number);
		}

		// Clear fields
		if (property_exists($object, 'ref')) {
			$object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		}
		//if (property_exists($object, 'label')) $object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		if (property_exists($object, 'origin') && !empty($origin)) {
			$object->origin = $origin;
		}
		if (property_exists($object, 'origin_id') && !empty($origin_id)) {
			$object->origin_id = $origin_id;
		}
		if (property_exists($object, 'fk_soc') && !empty($fk_soc)) {
			$object->fk_soc = $fk_soc;
		}
		if (property_exists($object, 'status')) {
			$object->status = self::STATUS_DRAFT;
		}
		if (property_exists($object, 'date_creation')) {
			$object->date_creation = dol_now();
		}
		if (property_exists($object, 'date_modification')) {
			$object->date_modification = null;
		}
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0) {
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option) {
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey])) {
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);

		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}
		$this->fetch($result);
		if (!$error) {
			// Add object linked
			if ($this->add_object_linked($origin, $origin_id) < 0) {
				$error++;
			}
		}

		if (!$error) {
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0) {
				$error++;
			}
		}

		if (!$error) {
			// copy external contacts if same company
			if (property_exists($this, 'socid') && $this->socid == $object->socid) {
				if ($this->copy_linked_contact($object, 'external') < 0) {
					$error++;
				}
			}
		}
		if (!$error) {
			// Validate
			$this->date_creation = $now;
			if ($object->validate($user, $notrigger) < 0) {
				$error++;
			}
		}
		unset($object->context['createfromclone']);

		if (!$error && $type == 0) {
			// Copy documents
			$oldref = dol_sanitizeFileName($oldref);
			$newref = dol_sanitizeFileName($object->ref);

			$dirsource = $conf->funding->multidir_output[$object->entity ? $object->entity : $conf->entity]."/".dol_sanitizeFileName($oldref).'/';
			$dirdest = $conf->funding->multidir_output[$object->entity ? $object->entity : $conf->entity]."/".dol_sanitizeFileName($newref).'/';
			$filesmove = array(
			'fundoc1'=>$object->fundoc1,
			'fundoc2'=>$object->fundoc2,
			'fundoc3'=>$object->fundoc3,
			'fundoc4'=>$object->fundoc4,
			'fundoc5'=>$object->fundoc5,
			'funfoldoc1'=>$object->funfoldoc1,
			'funfoldoc2'=>$object->funfoldoc2,
			'funfoldoc3'=>$object->funfoldoc3,
			'funfoldoc4'=>$object->funfoldoc4,
			'funfoldoc5'=>$object->funfoldoc5,
			'funfoldoc6'=>$object->funfoldoc6
			);

			if (!(is_dir($dirdest))) {
				$result = dol_mkdir($dirdest);
			}

			foreach ($filesmove as $key => $file) {
				if (!empty($file)) {
					if (!copy($dirsource.$file, $dirdest.$file)) {
						setEventMessages($langs->trans("filesmovenok").' '.$file, null, 'errors');
					}

					$newnamefile = str_replace($oldref, $newref, $file);

					if (file_exists($dirdest.$file)) {
						$rename = rename($dirdest.$file, $dirdest.$newnamefile);
					}

					if ($rename) {
						$sql = "UPDATE ".MAIN_DB_PREFIX.$object->table_element." SET ".$key." = '".$newnamefile."' WHERE rowid = ".$object->id;
						$resql = $db->query($sql);
						dol_syslog(__METHOD__." $object->id=".$object->id.", '".$doc."'=''", LOG_DEBUG);
					}
				}
			}
		}
		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);

		return $result;
	}

	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		} else {
			$sql .= ' WHERE 1 = 1';
		}
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				} elseif (strpos($key, 'date') !== false) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} else {
					$sqlwhere[] = $key.' LIKE \'%'.$this->db->escape($value).'%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' '.$this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $origin   Origin doc for funding (propal, order)
	 * @param string $origin_id  id doc for funding
	 * @return object         <object if OK, -1 if NOK
	 */
	public function fetchForDoc($origin, $origin_id)
	{
		$error = '';
		$result = 0;
		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."funding_funding";
		$sql.= " WHERE origin = '".$origin."' AND origin_id = ".$origin_id;
		$sql.= " ORDER BY rowid";
		$resql = $this->db->query($sql);
		$row = $this->db->fetch_row($resql);

		if (!empty($row[0])) {
			$idfinding = $row[0];
			$result = $this->fetch($idfinding);
		}

		// End
		if (!$error) {
			$this->db->commit();
			return $result;
		} else {
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1;
		}
	}



	/**
	 * Fetch the duration
	 *
	 * @param   int   $duration       id de la du financement
	 * @return $duration = ok or -1 = nok
	 */
	public function fetchDuration($duration)
	{
		global $conf, $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_funding_duration";
		$sql.= " WHERE rowid = ".$duration;
		$resql = $db->query($sql);
		if ($resql) {
			return $db->fetch_object($resql);
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Fetch the scale
	 *
	 * @param   ind     $scale       scale id de la retenu de garentie
	 * @return                      $scale = ok or -1 = nok
	 */
	public function fetchScale($scale)
	{
		global $conf, $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_funding_scale";
		$sql.= " WHERE rowid = ".$scale;
		$resql = $db->query($sql);
		if ($resql) {
			return $db->fetch_object($resql);
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Fetch the funding type
	 *
	 * @param  int  $type           id type
	 * @return                      $idsocinvoic = ok or -1 = nok
	 */
	public function fetchType($type)
	{
		global $conf, $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."c_funding_type";
		$sql.= " WHERE rowid = ".$type;
		$resql = $db->query($sql);
		if ($resql) {
			return $db->fetch_object($resql);
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Fetch the billing third party
	 *
	 * @param  int $socpeopleinvoice        id contact invoice
	 * @return                              $idsocinvoic = ok or -1 = nok
	 */
	public function fetchSocinvoice($socpeopleinvoice)
	{
		global $conf, $db;

		$sql = "SELECT fk_soc FROM ".MAIN_DB_PREFIX."socpeople";
		$sql.= " WHERE rowid = ".$socpeopleinvoice;
		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_object($resql);
			$socinvoice = $row->fk_soc;
			return $socinvoice;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Fetch the organization
	 *
	 * @param   int     $soc            id contact invoice
	 * @return                          $idsocinvoic = ok or -1 = nok
	*/
	public function fetchSoc($soc)
	{
		global $conf, $db;

		$sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe";
		$sql.= " WHERE rowid = ".$soc;
		$resql = $db->query($sql);
		if ($resql) {
			return $db->fetch_object($resql);
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = 0)
	{
		global $langs, $action;

		$error = 0;

		$coef = -1;
		$document = '';
		$idcomm = -1;
		$duration = -1;
		$typedoc = $this->origin;
		$iddoc = $this->origin_id;
		$oldamounttotal = $this->amount_total;

		//Document
		if ($iddoc && $typedoc) {
			$document = $this->infodoc($iddoc, $typedoc);

			if (is_object($document)) {
				// Fetch if a different billing address exists
				$socpeopleinvoice   = $document->getIdContact('external', 'BILLING');
				if ($socpeopleinvoice) {
					$socinvoice         = $this->fetchSocinvoice($socpeopleinvoice[0]);
					if ($socinvoice > -1) {
						$this->fk_soc_invoice = $socinvoice;
					} else {
						setEventMessages($langs->trans("socinvoicenok".'-'.$socpeopleinvoice[0]), null, 'errors');
					}
				} else {
					$this->fk_soc_invoice = '';
				}
				$this->amount       = $document->total_ht;
				$this->amount_total = empty($this->amount_maint) ? $document->total_ht : $document->total_ht + $this->amount_maint;
				if ($this->retention == 1) {
					// Update retention guarantee only on price change.
					// To avoid altering the rent sent to the client after rate updates.
					$newamounttotal = $this->amount_total + $this->retention_mount; // Add the retention guarantee amount for comparison if otherwise always true
					if ((empty($this->retention_rate) || ($oldamounttotal != $newamounttotal)) || $action == 'updateforce') {
						$this->retention_rate = $this->searchRetentionRate($this->fk_org);
					}
					$this->retention_mount = price2num($this->amount_total / (1-($this->retention_rate/100)), 'MT') - $this->amount_total;
					$this->amount_total = price2num($this->amount_total / (1-($this->retention_rate/100)), 'MT');
				} else {
					$this->retention_rate = '';
					$this->retention_mount = '';
				}
				// Update the coefficient only on a price change.
				// Do not change the rent sent to the client after rate updates.
				if (!empty($this->coef) && ($oldamounttotal == $this->amount_total) && $action != 'updateforce') {
					$coef = $this->coef;
				} else {
					$coef = $this->searchCoef($this->amount_total, $this->fk_duration, $this->fk_scale, $this->fk_org);
				}

				if ($coef > 0) {
					$this->coef = $coef;
					$this->amount_rent  = price2num($this->amount_total * $coef / 100, 'MT');

					if ($this->amount_rent_edit < $this->amount_rent) {
						$this->amount_rent_edit = $this->amount_rent;
						if ($this->origin == 'PROPAL') {
							setEventMessages($langs->trans("amountRentEdit<amountRent"), null, 'errors');
						}
					}
					if ($this->amount_rent_edit > $this->amount_rent && $this->origin == 'PROPAL') {
						setEventMessages($langs->trans("amountRentEdit>amountRent"), null);
					}

					$this->date_delivery = $document->delivery_date;
					// If version is down to 18
					if (version_compare(DOL_VERSION, '18.0.0') == -1) {
						$this->date_delivery = $document->date_livraison;
					}

					// Note to self:
					// Signature date filled when marked delivered
					// On invoice, request the signature date and prefill it if it already exists
					// JS to update the end date based on the signature date.

					// Calculate from the delivery date
					$this->date_end_calculated = '';
					if (!empty($this->date_delivery) && $this->status >= self::STATUS_RUNNING) {
						// Add the duration to the delivery date to calculate the end date
						$duration = $this->fetchDuration($this->fk_duration);
						if ($duration->code > 0) {
							$this->date_end_calculated = date('Y-m-d', strtotime('+'.$duration->code.' month', strtotime(date('Y-m-d', $this->date_delivery))));
						}
					}

					// Signature date filled if order is delivered and billed, and calculate end date from signature date
					// $this->date_end = '';
					if ($document->billed == 1) {
						$this->date_signature = !empty($this->date_signature) ? $this->date_signature : $this->date_delivery;

						// Add the duration to the signature date to calculate the end date
						$duration = $this->fetchDuration($this->fk_duration);
						if ($duration->code > 0) {
							$this->date_end_calculated = date('Y-m-d', strtotime('+'.$duration->code.' month', strtotime(date('Y-m-d', $this->date_signature))));
							if (empty($this->date_end)) {
								$this->date_end = $this->date_end_calculated;
							}
						}
					}
					// Change the status if the document amount changes and the funding is accepted
					if ($this->status >= self::STATUS_ACCEPT && $this->amount <> $document->total_ht) {
							$this->status = self::STATUS_UPDATE;
					}
					if (!$error) {
						return $this->updateCommon($user, $notrigger);
					}
				} else {
					setEventMessages($langs->trans("coefnok"), null, 'errors');
				}
			} else {
				setEventMessages($langs->trans("documentnotvalidated"), null, 'errors');
			}
		} else {
			setEventMessages($langs->trans("paramnok"), null, 'errors');
		}
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = 0)
	{
		global $langs, $conf;

		$error = 0;

		if ($this->status == self::STATUS_RUNNING) {
			setEventMessages($langs->trans("deletnok").' - '.$this->ref, null, 'errors');
			return -1;
		}

		// Removed extrafields of object
		if (!$error) {
			$result = $this->deleteExtraFields();
			if ($result < 0) {
				$error++;
				dol_syslog(get_class($this)."::delete error ".$this->error, LOG_ERR);
			}
		}

		if (!$error) {
			// Delete linked object
			$res = $this->deleteObjectLinked();
			if ($res < 0) {
				$error++;
			}
		}

		// Delete record into ECM index and physically
		if (!$error) {
			$res = $this->deleteEcmFiles(0); // Deleting files physically is done later with the dol_delete_dir_recursive
			if (!$res) {
				$error++;
			}
		}

		if (!$error) {
			// We remove directory
			$ref = dol_sanitizeFileName($this->ref);
			if (!empty($this->ref)) {
				$dir = $conf->funding->multidir_output[$this->entity ? $this->entity : $conf->entity]."/".$ref;
				$file = $dir."/".$ref.".pdf";
				if (file_exists($file)) {
					dol_delete_preview($this);

					if (!dol_delete_file($file, 0, 0, 0, $this)) {
						$this->error = 'ErrorFailToDeleteFile';
						$this->errors[] = $this->error;
						$error++;
					}
				}

				if (file_exists($dir)) {
					$res = @dol_delete_dir_recursive($dir);
					if (!$res) {
						$this->error = 'ErrorFailToDeleteDir';
						$this->errors[] = $this->error;
						$error++;
					}
				}
			}
		}


		if (!$error) {
			// Delete main
			$res = $this->deleteCommon($user, $notrigger);
			if ($res < 0) {
				$error++;
			}
		}

		if (!$error) {
			dol_syslog(get_class($this)."::delete ".$this->id." by ".$user->id, LOG_DEBUG);
			$this->db->commit();
			return 1;
		} else {
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 *  Delete a line of object in database
	 *
	 *  @param  User    $user       User that delete
	 *  @param  int     $idline     Id of line to delete
	 *  @param  bool    $notrigger  false=launch triggers after, true=disable triggers
	 *  @return int                 >0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = 0)
	{
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *  Validate object
	 *
	 *  @param      User    $user           User making status change
	 *  @param      int     $notrigger      1=Does not execute triggers, 0= execute triggers
	 *  @return     int                     <=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		//Document
		$typedoc = $this->origin;
		$iddoc = $this->origin_id;
		$document = $this->infodoc($iddoc, $typedoc);

		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}
		if ($document->mode_reglement_id != $conf->global->FUNDING_ID_REGLEMENT) {
			setEventMessages($langs->trans("novalidreg"), null, 'errors');
			return -1;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->funding->funding->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->funding->funding->funding_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happened, but when it occurs, the test save life
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) {
				$sql .= ", date_validation = '".$this->db->idate($now)."',";
			}
			if (!empty($this->fields['fk_user_valid'])) {
				$sql .= ", fk_user_valid = ".$user->id;
			}
			$sql .= " WHERE rowid = ".$this->id;

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger) {
				// Call trigger
				$result = $this->call_trigger('FUNDING_VALIDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'funding/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'funding/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++;
					$this->error = $this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->funding->dir_output.'/'.$oldref;
				$dirdest = $conf->funding->dir_output.'/'.$newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->funding->dir_output.'/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry) {
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error) {
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *  Set draft status
	 *
	 *  @param  User    $user           Object user that modify
	 *  @param  int     $notrigger      1=Does not execute triggers, 0=Execute triggers
	 *  @return int                     <0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->funding->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->funding->funding_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'FUNDING_UNVALIDATE');
	}

	/**
	 *  Set cancel status
	 *
	 *  @param  User    $user           Object user that modify
	 *  @param  int     $notrigger      1=Does not execute triggers, 0=Execute triggers
	 *  @return int                     <0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		global $conf, $langs;
		// Protection
		/*if ($this->status != self::STATUS_VALIDATED) {
			return 0;
		}*/
		$result = $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'FUNDING_CANCEL');

		if ($result > 0) {
			setEventMessages($langs->trans("fundingcancel"), null);
		} else {
			setEventMessages($langs->trans("statusfundingnok"), null, 'errors');
		}
		return $result;
	}


	/**
	 *  Set Accepted Refused  status
	 *
	 *  @param  User    $user           Object user that modify
	 *  @param  int     $status         value status
	 *  @param  string  $study_number   Study number
	 *  @param  string  $folder_number  File number
	 *  @param  string  $date_accepted  Date accepted
	 *  @param  float   $retention      Retention amount
	 *  @param  int     $notrigger      1=Does not execute triggers, 0=Execute triggers
	 *  @return int                     <0 if KO, 0=Nothing done, >0 if OK
	 */
	public function setAcceptedRefused($user, $status, $study_number = "", $folder_number = "", $date_accepted = "", $retention = 0, $notrigger = 0)
	{
		// Protection
		if ($this->status == self::STATUS_CANCELED) {
			return 0;
		}
		if ($status == self::STATUS_ACCEPT) {
			$triger = 'FUNDING_ACCEPT';
		}
		if ($status == self::STATUS_DENIED) {
			$triger = 'FUNDING_DENIED';
		}
		// Update study number upon acceptance
		if (!empty($study_number)) {
			$this->setStudyNumber($user, $study_number);
		}
		// Update file number upon acceptance
		if (!empty($folder_number)) {
			$this->setFolderNumber($user, $folder_number);
		}
		// Update acceptance date to calculate offer validity end date
		if (!empty($date_accepted)) {
			$this->setDateAccepted($user, $date_accepted);
		}
		// Update if retention guarantee is added upon acceptance
		if ($retention == 'on') {
			$this->setStatusFolder($user, $this::STATUS_FOLDER_ACCEPT_RETENTION);
		} else {
			$this->setStatusFolder($user, 'NULL');
		}

		return $this->setStatusCommon($user, $status, $notrigger, $triger);
	}

	/**
	 *  Set Run  status
	 *
	 *  @param  User    $user           Object user that modify
	 *  @param  bool    $notrigger      false=launch triggers after, true=disable triggers
	 *  @return int                     <0 if KO, 0=Nothing done, >0 if OK
	 */
	public function setRun($user, $notrigger = 0)
	{
		global $conf, $langs;

		$typedoc = $this->origin;
		$iddoc = $this->origin_id;

		if (!empty($typedoc) && !empty($iddoc)) {
			$document = $this->infodoc($iddoc, $typedoc);
			if (!empty($this->date_signature) && $this->status == self::STATUS_ACCEPT && $document->status > 0) {
				$status = self::STATUS_RUNNING;
				$triger = 'FUNDING_RUNNING';
				return $this->setStatusCommon($user, $status, $notrigger, $triger);
			} else {
				if ($document->status == 0) {
					setEventMessages($langs->trans('documentnotvalidated'), '', 'errors');
				} elseif ($this->status >= self::STATUS_RUNNING) {
					setEventMessages($langs->trans('statusfundingnok'), '', 'errors');
				} elseif (empty($this->date_delivery)) {
					setEventMessages($langs->trans('fundingnotdatedelivry'), '', 'errors');
				} elseif (empty($this->date_signature)) {
					setEventMessages($langs->trans('fundingnotdatesign'), '', 'errors');
				} else {
					setEventMessages($langs->trans('CantBeValidated'), '', 'errors');
				}
				return -1;
			}
		} else {
			setEventMessages($langs->trans("paramnok"), null, 'errors');
			return -1;
		}
	}

	/**
	 *  Set End  status
	 *
	 *  @param  User    $user           Object user that modify
	 *  @param  int     $statusfolder   Value status folder
	 *  @param  alpha   $note           Note to closed
	 *  @param  bool    $notrigger      false=launch triggers after, true=disable triggers
	 *  @return int                     <0 if KO, 0=Nothing done, >0 if OK
	 */
	public function setEnd($user, $statusfolder, $note = '', $notrigger = 0)
	{
		global $langs;

		$result = 0;

		if (!empty($note)) {
			$this->description = dol_concatdesc($this->description, $note);
			$result = $this->updateCommon($user, 1);
		}
		if ($result >= 0) {
			$result = $this->setStatusFolder($user, $statusfolder);
		}
		$status = self::STATUS_END;
		$triger = 'FUNDING_END';
		if ($statusfolder != self::STATUS_FOLDER_DENOUNCED) {
			if ($this->status == self::STATUS_RUNNING && $result >= 0) {
				return $this->setStatusCommon($user, $status, $notrigger, $triger);
			} else {
				setEventMessages($langs->trans("updatenok"), 'errors');
				return -1;
			}
		} else {
			return 0;
		}
	}

	/**
	 *  Set Check
	 *
	 *  @param  User    $user           Object user that modify
	 * 	@param  int    	$id           	id funding
	 *  @param  int    	$check          	Check or no
	 *  @return int                     <0 if KO, 0=Nothing done, >0 if OK
	 */
	public function setChecked($user, $id, $check = 1)
	{
		global $db;

		$error = 0;

		$this->db->begin();

		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET funcheck = ".$check." WHERE rowid = ".$id;
		$resql = $db->query($sql);

		dol_syslog(__METHOD__.' $this->id='.$this->id.', folder_number='.$folder_number, LOG_DEBUG);

		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->errors[] = $this->db->error();
			$error++;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__.' Error: '.$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1 * $error;
		}
	}


	/**
	 * Update object into database
	 *
	 * @param  User     $user               User that modifies
	 * @param  string   $study_number       Study number
	 * @param  bool     $notrigger          false=launch triggers after, true=disable triggers
	 * @return int                          <0 if KO, >0 if OK
	 */
	public function setStudyNumber($user, $study_number, $notrigger = 0)
	{
			$error = 0;

			$this->db->begin();

			$sql = "UPDATE ".MAIN_DB_PREFIX."funding_funding";
			$sql .= " SET study_number = ".($study_number != '' ? "'".$study_number."'" : 'null');
			$sql .= " WHERE rowid = ".$this->id;

			dol_syslog(__METHOD__.' $this->id='.$this->id.', study_number='.$study_number, LOG_DEBUG);

			$resql = $this->db->query($sql);
		if (!$resql) {
			$this->errors[] = $this->db->error();
			$error++;
		}

		if (!$error) {
			$this->db->commit();
			if (!$notrigger && empty($error)) {
				// Call trigger
				$result = $this->call_trigger('FUNDING_MODIFY', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
			return 1;
		} else {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__.' Error: '.$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1 * $error;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User     $user               User that modifies
	 * @param  string   $folder_number      File number
	 * @param  bool     $notrigger          false=launch triggers after, true=disable triggers
	 * @return int                          <0 if KO, >0 if OK
	 */
	public function setFolderNumber($user, $folder_number, $notrigger = 0)
	{
		$error = 0;

		$this->db->begin();

		$sql = "UPDATE ".MAIN_DB_PREFIX."funding_funding";
		$sql .= " SET folder_number = ".($folder_number != '' ? "'".$folder_number."'" : 'null');
		$sql .= " WHERE rowid = ".$this->id;

		dol_syslog(__METHOD__.' $this->id='.$this->id.', folder_number='.$folder_number, LOG_DEBUG);

		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->errors[] = $this->db->error();
			$error++;
		}

		if (!$error) {
			$this->db->commit();

			if (!$notrigger && empty($error)) {
				// Call trigger
				$result = $this->call_trigger('FUNDING_MODIFY', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
			return 1;
		} else {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__.' Error: '.$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1 * $error;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User     $user               User that modifies
	 * @param  date   $date_accepted      Date funding accepted
	 * @param  bool     $notrigger          false=launch triggers after, true=disable triggers
	 * @return int                          <0 if KO, >0 if OK
	 */
	public function setDateAccepted($user, $date_accepted, $notrigger = 0)
	{
		global $langs;

		$error = 0;
		$date_endvalidity = '';
		$duration_value = getDolGlobalString('FUNDING_VALIDITY_MONTH');
		$duration_unit = "m";

		// Si date d'acceptation calcul date de fin
		if (!empty($date_accepted) && !empty($duration_value)) {
			$time = strtotime(str_replace('/', '-', $date_accepted));
			// Add months
			$date_endvalidity = dol_time_plus_duree($time, $duration_value, $duration_unit);
			// Format dates
			$date_accepted = date('Y-m-d', $time);
			$date_endvalidity = date('Y-m-d', $date_endvalidity);
		} elseif (empty($duration_value)) {
			$this->errors[] = $langs->trans("NoDurationValue");
			$error++;
		}

		$this->db->begin();
			// Record acceptance date
		$sql = "UPDATE ".MAIN_DB_PREFIX."funding_funding";
		$sql .= " SET date_accepted = ".($date_accepted != '' ? "'".$date_accepted."'" : 'null');
		$sql .= " WHERE rowid = ".$this->id;

			// Execute the query
		$resql = $this->db->query($sql);

		if (!$resql) {
			$this->errors[] = $this->db->error();
			$error++;
		}

			// Record end validity date
		$sql = "UPDATE ".MAIN_DB_PREFIX."funding_funding";
		$sql .= " SET date_endvalidity = ".(!empty($date_endvalidity) ? "'".$date_endvalidity."'" : 'null');
		$sql .= " WHERE rowid = ".$this->id;

		dol_syslog(__METHOD__.' $this->id='.$this->id.', date_accepted ='.$date_accepted.', date_endvalidity ='.$date_endvalidity, LOG_DEBUG);

			// Execute the query
		$resql = $this->db->query($sql);

		if (!$resql) {
			$this->errors[] = $this->db->error();
			$error++;
		}

		if (!$error) {
			$this->db->commit();
			if (!$notrigger && empty($error)) {
				// Call trigger
				$result = $this->call_trigger('FUNDING_MODIFY', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
			return 1;
		} else {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__.' Error: '.$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1 * $error;
		}
	}
	/**
		 * Update object into database
		 *
		 * @param  User     $user               User that modifies
		 * @param  date   $date_end            Date funding end
		 * @param  bool     $notrigger          false=launch triggers after, true=disable triggers
		 * @return int                          <0 if KO, >0 if OK
		 */
	public function setDateEnd($user, $date_end, $notrigger = 0)
	{
		global $langs;

		$error = 0;

		// Record end date
		$sql = "UPDATE ".MAIN_DB_PREFIX."funding_funding";
		$sql .= " SET date_end = ".(!empty($date_end) ? "'".$date_end."'" : 'null');
		$sql .= " WHERE rowid = ".$this->id;

		dol_syslog(__METHOD__.' $this->id='.$this->id.', date_end ='.$date_end, LOG_DEBUG);

			// Execute the query
		$resql = $this->db->query($sql);

		if (!$resql) {
			$this->errors[] = $this->db->error();
			$error++;
		}

		if (!$error) {
			$this->db->commit();
			if (!$notrigger && empty($error)) {
				// Call trigger
				$result = $this->call_trigger('FUNDING_MODIFY', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
			return 1;
		} else {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__.' Error: '.$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1 * $error;
		}
	}

	/**
	 * Update staus folder object
	 *
	 * @param  User $user       User that modifies
	 * @param  $status          New status folder
	 * @param  bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int              <0 if KO, >0 if OK
	 */
	public function setStatusFolder($user, $status, $notrigger = 0)
	{
		$error = 0;
		$triger = ''; // FIX PHP8

		$this->db->begin();

		$sql = "UPDATE ".MAIN_DB_PREFIX."funding_funding";
		$sql .= " SET status_folder = ".$status;
		$sql .= " WHERE rowid = ".$this->id;

		dol_syslog(__METHOD__.' $this->id='.$this->id.', extension', LOG_DEBUG);

		$resql = $this->db->query($sql);
		if (!$resql) {
			$this->errors[] = $this->db->error();
			$error++;
		}

		if ($status == self::STATUS_FOLDER_SENDORG) {
			$triger = 'FUNDING_SENDORG';
		} elseif ($status == self::STATUS_FOLDER_LACK) {
			$triger = 'FUNDING_LACK';
		} elseif ($status == self::STATUS_FOLDER_LACKOK) {
			$triger = 'FUNDING_LACKOK';
		} elseif ($status == self::STATUS_FOLDER_EXTENSION) {
			$triger = 'FUNDING_EXTENSION';
		} else {
			$notriger = 1;
		}

		if (!$error) {
			$this->db->commit();
			if (!$notrigger && empty($error)) {
				// Call trigger
				$result = $this->call_trigger($triger, $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
			return 1;
		} else {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__.' Error: '.$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			setEventMessages($this->error, '', 'errors');
			$this->db->rollback();
			return -1 * $error;
		}
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';
		//$label = img_picto('', $this->picto).' <u class="paddingrightonly">'.$langs->trans("Proposal").'</u>';
		$label = '<u>'.$langs->trans("Funding").'</u>';
		$label .= '';
		$label .= '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;
		$label .= '<br><b>'.$langs->trans('Amount').':</b> '.price($this->amount_total, 0, $langs, 0, -1, -1, $conf->currency);
		$label .= '<br><b>'.$langs->trans('Rent').':</b> '.price($this->amount_rent, 0, $langs, 0, -1, -1, $conf->currency);
		$label .= '<br><b>'.$langs->trans('Duration').':</b> '.$this->fetchDuration($this->fk_duration)->label;
		if (isset($this->status)) {
			$label .= '<br><b>'.$langs->trans("Status").":</b> ".$this->getLibStatut(5);
		}

		$url = dol_buildpath('/funding/funding_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$label = $langs->trans("ShowFunding");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		$linkstart = '<a href="'.$url.'"';
		$linkstart .= $linkclose.'>';
		$linkend = '</a>';

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('fundingdao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return label of the status
	 *
	 *  @param  int     $mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string                 Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param  int     $status        Id status
	 *  @param  int     $mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string                 Label of status
	 */
	public function LibStatut($status, $mode = 6)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("funding");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('FundingStatusDraft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('FundingStatusValidated');
			$this->labelStatus[self::STATUS_UPDATE] = $langs->trans('FundingStatusUpdate');
			$this->labelStatus[self::STATUS_ACCEPT] = $langs->trans('FundingStatusAccept');
			$this->labelStatus[self::STATUS_DENIED] = $langs->trans('FundingStatusDenied');
			$this->labelStatus[self::STATUS_RUNNING] = $langs->trans('FundingStatusRunning');
			$this->labelStatus[self::STATUS_END] = $langs->trans('FundingStatusEnd');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->trans('FundingStatusDisabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('FundingStatusDraftShort');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('FundingStatusValidatedShort');
			$this->labelStatusShort[self::STATUS_UPDATE] = $langs->trans('FundingStatusUpdateShort');
			$this->labelStatusShort[self::STATUS_ACCEPT] = $langs->trans('FundingStatusAcceptShort');
			$this->labelStatusShort[self::STATUS_DENIED] = $langs->trans('FundingStatusDeniedShort');
			$this->labelStatusShort[self::STATUS_RUNNING] = $langs->trans('FundingStatusRunningShort');
			$this->labelStatusShort[self::STATUS_END] = $langs->trans('FundingStatusEndShort');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->trans('FundingStatusDisabledShort');
		}

		//Status correspondence with display formats
		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) {
			$statusType = 'status6';
		}

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *  Return label of the status folder
	 *
	 *  @param  int     $mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string                 Label of status folder
	 */
	public function getLibStatutFolder($mode = 0)
	{
		return $this->LibStatutFolder($this->status_folder, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status folder
	 *
	 *  @param  int     $status        Id status
	 *  @param  int     $mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string                 Label of status folder
	 */
	public function LibStatutFolder($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatusFolder) || empty($this->labelStatusFolderShort)) {
			global $langs;
			//$langs->load("funding");
			$this->labelStatusFolder[self::STATUS_FOLDER_SENDORG] = $langs->trans('FundingStatusFolderSendOrg');
			$this->labelStatusFolder[self::STATUS_FOLDER_LACK] = $langs->trans('FundingStatusFolderLack');
			$this->labelStatusFolder[self::STATUS_FOLDER_LACKOK] = $langs->trans('FundingStatusFolderLackOk');
			$this->labelStatusFolder[self::STATUS_FOLDER_ACCEPT_RETENTION] = $langs->trans('FundingStatusFolderAcceptRetention');
			$this->labelStatusFolder[self::STATUS_FOLDER_REDEEMED] = $langs->trans('FundingStatusFolderRedeemed');
			$this->labelStatusFolder[self::STATUS_FOLDER_EXTENSION] = $langs->trans('FundingStatusFolderExtension');

			$this->labelStatusFolder[self::STATUS_FOLDER_DENOUNCED] = $langs->trans('FundingStatusFolderDenounced');
			$this->labelStatusFolder[self::STATUS_FOLDER_CLOSED_LESSOR] = $langs->trans('FundingStatusFolderClosedLessor');
			$this->labelStatusFolder[self::STATUS_FOLDER_CLOSED_TRANSFER] = $langs->trans('FundingStatusFolderClosedTransfer');

			$this->labelStatusFolderShort[self::STATUS_FOLDER_SENDORG] = $langs->trans('FundingStatusFolderSendOrgShort');
			$this->labelStatusFolderShort[self::STATUS_FOLDER_LACK] = $langs->trans('FundingStatusFolderLackShort');
			$this->labelStatusFolderShort[self::STATUS_FOLDER_LACKOK] = $langs->trans('FundingStatusFolderLackOkShort');
			$this->labelStatusFolderShort[self::STATUS_FOLDER_ACCEPT_RETENTION] = $langs->trans('FundingStatusFolderAcceptRetentionShort');
			$this->labelStatusFolderShort[self::STATUS_FOLDER_REDEEMED] = $langs->trans('FundingStatusFolderRedeemedShort');
			$this->labelStatusFolderShort[self::STATUS_FOLDER_EXTENSION] = $langs->trans('FundingStatusFolderExtensionShort');

			$this->labelStatusFolderShort[self::STATUS_FOLDER_DENOUNCED] = $langs->trans('FundingStatusFolderDenouncedShort');
			$this->labelStatusFolderShort[self::STATUS_FOLDER_CLOSED_LESSOR] = $langs->trans('FundingStatusFolderClosedLessorShort');
			$this->labelStatusFolderShort[self::STATUS_FOLDER_CLOSED_TRANSFER] = $langs->trans('FundingStatusFolderClosedTransferShort');
		}

		// Status correspondence with display formats
		if (!empty($status)) {
			$statusType = 'status'.$status;
		}
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		/*
		if ($status == self::STATUS_CANCELED) {
			$statusType = 'status6';
		}*/

		// Fix PHP8 if (!empty($status)) {
		if (!empty($status)) {
			return dolGetStatus($this->labelStatusFolder[$status], $this->labelStatusFolderShort[$status], '', $statusType, $mode);
		}
	}

	/**
	 *  Load the info information in the object
	 *
	 *  @param  int     $id       Id of object
	 *  @return void
	 */
	public function info($id)
	{
		$sql = "SELECT rowid,";
		$sql .= " date_creation as datec, tms as datem";
		if (!empty($this->fields['date_validation'])) {
			$sql .= ", date_validation as datev";
		}
		if (!empty($this->fields['fk_user_creat'])) {
			$sql .= ", fk_user_creat";
		}
		if (!empty($this->fields['fk_user_modif'])) {
			$sql .= ", fk_user_modif";
		}
		if (!empty($this->fields['fk_user_valid'])) {
			$sql .= ", fk_user_valid";
		}
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);

				$this->id = $obj->rowid;

				if (!empty($this->fields['fk_user_creat'])) {
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_creat);
					$this->user_creation = $cuser;
				}

				if (!empty($this->fields['fk_user_valid'])) {
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if (!empty($this->fields['fk_user_cloture'])) {
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = empty($obj->datem) ? '' : $this->db->jdate($obj->datem);
				if (!empty($obj->datev)) {
					$this->date_validation   = empty($obj->datev) ? '' : $this->db->jdate($obj->datev);
				}
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 *  Create badge number label tab Tird-Party
	 *
	 * @param  int     $id       Id of object
	 * @param  object  $obj      object
	 *
	 * @return $nbfunding      	Nb funding
	 */
	public function getcountForThird($id, $obj)
	{
		global $db;

		$nb = 0;

		$sql = "SELECT rowid, fk_soc, origin FROM ".MAIN_DB_PREFIX.$obj->table_element;
		$sql .= " WHERE fk_soc = ".$id;
		// Setting to show funding records for orders only
		if (empty($conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL)) {
			$sql.= " AND origin <> 'propal'";
		}
		$resql = $this->db->query($sql);
		if ($resql) {
			$nb = $this->db->num_rows($resql);
		} else {
			dol_print_error($this->db);
		}

		return $nb;
	}


	/**
	 *  Create badge number label tab Propal
	 *
	 * @param  int     $id       Id of object
	 * @param  object  $obj      object
	 *
	 * @return $nbfunding      	Nb funding
	 */
	public function getcountForPropal($id, $obj)
	{
		global $db;

		$nb = 0;

		$sql = "SELECT rowid, fk_soc, origin FROM ".MAIN_DB_PREFIX.$obj->table_element;
		$sql .= " WHERE origin = 'PROPAL' and origin_id = ".$id;
		$resql = $this->db->query($sql);
		if ($resql) {
			$nb = $this->db->num_rows($resql);
		} else {
			dol_print_error($this->db);
		}

		return $nb;
	}

	/**
	 *  Create badge number label tab Order
	 *
	 * @param  int     $id       Id of object
	 * @param  object  $obj      object
	 *
	 * @return $nbfunding      	Nb funding
	 */
	public function getcountForOrder($id, $obj)
	{
		global $db;

		$nb = 0;

		$sql = "SELECT rowid, fk_soc, origin FROM ".MAIN_DB_PREFIX.$obj->table_element;
		$sql .= " WHERE origin = 'ORDER' and origin_id = ".$id;
		$resql = $this->db->query($sql);
		if ($resql) {
			$nb = $this->db->num_rows($resql);
		} else {
			dol_print_error($this->db);
		}

		return $nb;
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string              Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("funding@funding");

		if (empty($conf->global->FUNDING_FUNDING_ADDON)) {
			$conf->global->FUNDING_FUNDING_ADDON = 'mod_funding_standard';
		}

		if (!empty($conf->global->FUNDING_FUNDING_ADDON)) {
			$mybool = false;

			$file = $conf->global->FUNDING_FUNDING_ADDON.".php";
			$classname = $conf->global->FUNDING_FUNDING_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/funding/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false) {
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1') {
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param      string      $modele         Force template to use ('' to not force)
	 *  @param      Translate   $outputlangs    objet lang a utiliser pour traduction
	 *  @param      int         $hidedetails    Hide details of lines
	 *  @param      int         $hidedesc       Hide description
	 *  @param      int         $hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int                         0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$result = 0;
		$includedocgeneration = 1;

		$langs->load("funding@funding");

		if (!dol_strlen($modele)) {
			$modele = 'standard_funding';

			if ($this->modelpdf) {
				$modele = $this->modelpdf;
			} elseif (!empty($conf->global->FUNDING_ADDON_PDF)) {
				$modele = $conf->global->FUNDING_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/funding/doc/";

		if ($includedocgeneration) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}

	/**
	 * Search doc fundoc object
	 *
	 * @param		User		$user			User that modifies
	 * @param		string		$upload_dir		upload dir
	 * @return		int							<0 if KO, >0 if OK
	 */
	public function searchDoc($user, $upload_dir)
	{
		global $conf, $db, $langs;

		$error = 0;
		$this->msg = '';
		$this->msgs[] = '';
		// // Security verify if fundoc is empty
		// if (!empty($this->$doc)) {
		// 	return 0;
		// }

			$i = 1;
		while ($i <= 4) {
			$docSearch = '';
			$doc = 'fundoc'.$i;
			$doccheck = $doc.'check';

			// Security verify if fundoc
			// if (!isset($this->$doc)) {
			// 	$i++;
			// 	continue;
			// }

			$sql = "SELECT * FROM ".MAIN_DB_PREFIX.'funding_funding as c';
			$sql.= ' WHERE c.fk_soc = ' . $this->fk_soc;
			$sql.= ' AND c.'.$doc.' <> "" ';
			$resql = $db->query($sql);

			if ($resql) {
				$nbtotalofrecords = $db->num_rows($resql);
				if ($nbtotalofrecords > 0) {
					$j = 0;
					$total = 0;
					while ($j < $nbtotalofrecords) {
						$obj = $db->fetch_object($resql);
						if (empty($this->$doc) && is_object($obj) && !empty($obj->$doc)) {
							$docSearch = $obj->$doc;
						}
						$j++;
					}
				}
			} else {
				$this->error = 'ErrorFailsql';
				$this->errors[] = 'Error '.$this->db->lasterror();
				$error++;
				dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
			}
			if (!empty($docSearch)) {
				// On copie le document dans le bon dossier
				$upload_dir_orig = $upload_dir."/".dol_sanitizeFileName($obj->ref);
				$upload_dir_dest = $upload_dir."/".dol_sanitizeFileName($this->ref);

				$fileintputname = $obj->$doc;
				$fileoutputname = dol_string_nospecial(dol_sanitizeFileName(dol_string_nohtmltag($this->ref.'_'.$langs->trans($doc))));
				$fileoutputname = str_replace(array('\'' , '&nbsp;', ' '), '_', $fileoutputname.'.pdf');

				if (!(is_dir($upload_dir_dest))) {
					$result = dol_mkdir($upload_dir_dest);
				} else {
					$result = 1;
				}
				if ($result < 0) {
					$this->error = 'ErrorCreateFolder';
					$this->errors[] = 'Error create folder'.$langs->trans('ErrorFileNotFound');
					$error++;
					dol_syslog(__METHOD__.' $this->id='.$this->id.' '.join(',', $this->errors), LOG_ERR);
				}

				$result = dol_copy($upload_dir_orig.'/'.$fileintputname, $upload_dir_dest.'/'.$fileoutputname);
				if ($result < 0) {
					$this->error = 'ErrorCopyFile';
					$this->errors[] = 'Error copy file'.$langs->trans('ErrorFileNotFound');
					$error++;
					dol_syslog(__METHOD__.' $this->id='.$this->id.' '.join(',', $this->errors), LOG_ERR);
				}

				// Verify if the file was correctly created for database registration
				if (file_exists($upload_dir_dest.'/'.$fileoutputname)) {
					if (isset($this->$doccheck)) {
						$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET ".$doc." = '".$fileoutputname."',".$doccheck." = NULL WHERE rowid = ".$this->id;
					} else {
						$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET ".$doc." = '".$fileoutputname."'WHERE rowid = ".$this->id;
					}
					$resql = $db->query($sql);
					$db->free($resql);
					$this->fetch($this->id);
					if ($resql) {
						if (empty($this->fundoc1check) && empty($this->fundoc2check) && empty($this->fundoc3check) && empty($this->fundoc4check) && empty($this->fundoc5check) && $this->status_folder == $this::STATUS_FOLDER_LACK) {
							$this->setStatusFolder($user, $this::STATUS_FOLDER_LACKOK);
						}
						$this->msg = 'FileAdding';
						$this->msgs[] = '-> '.$langs->trans($doc);
					} else {
						$this->error = 'ErrorFailsql';
						$this->errors[] = 'Error '.$this->db->lasterror();
						$error++;
						dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);
					}
				}
			} else {
				$this->error = 'NoFileSearch';
				$this->errors[] = '-> '.$langs->trans($doc);
			}
			$i++;
		}


		if (empty($error)) {
			return 1;
		} else {
			return -1 * $error;
		}
	}

	/**
	 *  Upload and manage documents for funding.
	 *
	 *  @param      string      $fileupload        	Files send
	 *  @param      bool      	$cherchfile			Files cherch
	 * 	@param      string      $upload_dir			upload dir
	 * 	@param      string      $action				action
	 *  @return     int								0< if KO, 1 if OK
	 */
	public function sendDocumentFunding($fileupload, $cherchfile, $upload_dir, $action)
	{
		global $conf, $langs, $user, $db;

		$_POST['addfile'] = '';
		$doc = GETPOST('doc');  // Document sent
		$file = GETPOST('file'); // File to delete
		$filecheck = GETPOST('filecheck'); // If required file is true
		$fileoutputname = $fileupload;

				// Save the record if a file exists
		if (!empty($cherchfile)) {
				$fileoutputname = dol_string_nospecial(dol_sanitizeFileName(dol_string_nohtmltag($this->ref.'_'.$langs->trans($doc))));
				$fileoutputname = str_replace(array('\'' , '&nbsp;', ' '), '_', $fileoutputname.'.pdf');

				//Fusion des PDF
				// Libraries
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

			// Single file selector and the file is a PDF
			if (!is_countable($_FILES['userfile']['name'])) {
				// The file is a PDF
				if (strpos($_FILES['userfile']['type'], '/pdf') == true) {
					$result = dol_move($upload_dir.'/'.$fileupload, $upload_dir.'/'.$fileoutputname);
					if ($result == false) {
						setEventMessages($langs->trans('ErrorFileNotRename'), '', 'errors');
						if (!dol_delete_file($upload_dir.'/'.$fileupload, 0, 0, 0, $this)) {
							$this->error = 'ErrorFailToDeleteFile';
							$this->errors[] = $langs->trans('ErrorFileNotFound').' '.$this->error;
							$error++;
							setEventMessages($langs->trans('ErrorFileNotFound'), '', 'errors');
						} else {
							$this->message = 'FilesDeleted';
							$this->messages[] = $this->message;
						}
					}
				}
				// The file is an image
				if (strpos($_FILES['userfile']['type'], 'image/') === 0) {
					$file = $upload_dir.'/'.dol_sanitizeFileName($fileupload);
					// Lowercase extension
					$info = pathinfo($file);
					$file = $upload_dir.'/'.dol_sanitizeFileName($info['filename'].($info['extension'] != '' ? ('.'.strtolower($info['extension'])) : ''));

					if (file_exists($file) && is_readable($file)) {
						// Convert the image to PDF
						$pdf->AddPage();
						$pdf->Image($file, '', '', $page_largeur - $marge_gauche - $marge_droite);

						// Creation of the PDF file
						$pdf->Output($upload_dir.'/'.$fileoutputname, 'F');
						$pdf->Close();
						// dol_delete_file($file); // Old version to delete
						dol_delete_preview($this);

						if (!dol_delete_file($file, 0, 0, 0, $this)) {
							$this->error = 'ErrorFailToDeleteFile';
							$this->errors[] = $this->error;
							$error++;
						}
					}
				}
			}
			// Selector for multiple files
			if (is_countable($_FILES['userfile']['name'])) {
				$nbfiles = count($_FILES['userfile']['name']);
				// If only one file, do the same as a simple selector
				if ($nbfiles == 1) {
					foreach ($fileupload as $file) {
						$file = $upload_dir.'/'.dol_sanitizeFileName($file);
						// Extenssion lowercase
						$info = pathinfo($file);
						$file = $upload_dir.'/'.dol_sanitizeFileName($info['filename'].($info['extension'] != '' ? ('.'.strtolower($info['extension'])) : ''));
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$mtype = finfo_file($finfo, $file);
						finfo_close($finfo);

						if (file_exists($file)) {
							// The file is a PDF
							if (strpos($mtype, '/pdf') == true) {
								$result = dol_move($file, $upload_dir.'/'.$fileoutputname);
								if ($result == false) {
									setEventMessages($langs->trans('ErrorFileNotRename'), '', 'errors');
									if (!dol_delete_file($upload_dir.'/'.$fileupload, 0, 0, 0, $this)) {
										$this->error = 'ErrorFileNotFound';
										$this->errors[] = $this->error;
										$error++;
									} else {
										$this->message = 'FilesDeleted';
										$this->messages[] = $this->message;
									}
								}
							}
							// The file is an image
							if (strpos($mtype, 'image/') === 0) {
								if (file_exists($file) && is_readable($file)) {
									// Convert the image to PDF
									$pdf->AddPage();
									$pdf->Image($file, '', '', $page_largeur - $marge_gauche - $marge_droite);
									// Creation of the PDF file
									$pdf->Output($upload_dir.'/'.$fileoutputname, 'F');
									$pdf->Close();
									// dol_delete_file($file); // Old version to delete
									dol_delete_preview($this);

									if (!dol_delete_file($file, 0, 0, 0, $this)) {
										$this->error = 'ErrorFailToDeleteFile';
										$this->errors[] = $this->error;
										$error++;
									}
								}
							}
						}
					}
				} else {
					// If multiple files, create a PDF
					foreach ($fileupload as $file) {
						$file = $upload_dir.'/'.dol_sanitizeFileName($file);
						// Extenssion lowercase
						$info = pathinfo($file);
						$file = $upload_dir.'/'.dol_sanitizeFileName($info['filename'].($info['extension'] != '' ? ('.'.strtolower($info['extension'])) : ''));
						$finfo = finfo_open(FILEINFO_MIME_TYPE);
						$mtype = finfo_file($finfo, $file);
						finfo_close($finfo);
						// Verify if the file exists
						if (file_exists($file) && is_readable($file)) {
							// Si il y a une image on l'ajoute dans une page
							if (strpos($mtype, '/pdf') == true) {
								$pagecount = $pdf->setSourceFile($file);
								for ($i = 1; $i <= $pagecount; $i++) {
									$tplIdx = $pdf->importPage($i);
									if ($tplIdx !== false) {
										$s = $pdf->getTemplatesize($tplIdx);
										$pdf->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
										$pdf->useTemplate($tplIdx);
									} else {
										setEventMessages(null, array($file.' cannot be added, probably protected PDF'), 'warnings');
									}
								}
							} elseif (strpos($mtype, 'image/') === 0) {
								$pdf->AddPage();
								$pdf->Image($file, '', '', $page_largeur - $marge_gauche - $marge_droite);
							}
						}
					}
					// Create the PDF file
					$pdf->Output($upload_dir.'/'.$fileoutputname, 'F');
					$pdf->Close();
					// Delete the source files
					foreach ($fileupload as $file) {
						$file = $upload_dir.'/'.dol_sanitizeFileName($file);
						// Extenssion lowercase
						$info = pathinfo($file);
						$file = $upload_dir.'/'.dol_sanitizeFileName($info['filename'].($info['extension'] != '' ? ('.'.strtolower($info['extension'])) : ''));
						// dol_delete_file($file); // Old version to delete file
						dol_delete_preview($this);

						if (!dol_delete_file($file, 0, 0, 0, $this)) {
							$this->error = 'ErrorFailToDeleteFile';
							$this->errors[] = $this->error;
							$error++;
						}
					}
				}
			}
			// Delete the 'thumbs' folder created by image upload
			if (is_dir($upload_dir.'/thumbs')) {
				dol_delete_dir_recursive($upload_dir.'/thumbs');
			}
			// Verify that the file was created successfully for database registration
			if (file_exists($upload_dir.'/'.$fileoutputname)) {
				$doccheck = $doc.'check';
				if (isset($this->$doccheck)) {
					$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET ".$doc." = '".$fileoutputname."',".$doc."check = NULL WHERE rowid = ".$this->id;
				} else {
					$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET ".$doc." = '".$fileoutputname."'WHERE rowid = ".$this->id;
				}
				$resql = $db->query($sql);
				$db->free($resql);
				if ($resql) {
					$this->fetch($this->id);
					if (empty($this->fundoc1check) && empty($this->fundoc2check) && empty($this->fundoc3check) && empty($this->fundoc4check) && empty($this->fundoc5check) && $this->status_folder == $this::STATUS_FOLDER_LACK) {
						$this->setStatusFolder($user, $this::STATUS_FOLDER_LACKOK);
					}
					$this->message = 'FileAdded';
					$this->messages[] = $this->message;
				} else {
					$this->error = 'ErrorFailToAddedFile';
					$this->errors[] = 'Error '.$this->db->lasterror();
					$error++;
					dol_syslog(__METHOD__." $this->id=".$this->id.", '".$doc."'=''", LOG_DEBUG);
				}
			}
			// Delete document
		} elseif ($action == 'deletefile' && !empty($upload_dir) && $file) {
			$file = $upload_dir.'/'.$file;
			if (file_exists($file)) {
				$result = dol_delete_file($file);
			}
			if ($result >= 0) {
				$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET ".$doc." = '' WHERE rowid = ".$this->id;
				$resql = $db->query($sql);
				$this->db->free($resql);
				$this->message = 'FilesDeleted';
				$this->messages[] = $this->message;
				if (!$resql) {
					$this->error = 'ErrorFailToDeleteFile';
					$this->errors[] = 'Error '.$this->db->lasterror();
					$error++;
					dol_syslog(__METHOD__." $this->id=".$this->id.", '".$doc."'=''", LOG_DEBUG);
				}
			} else {
				$this->error = 'ErrorFailToDeleteFile';
				$this->errors[] = $this->error;
				$error++;
				dol_syslog(__METHOD__." $this->id=".$this->id.", ".$doc."=''", LOG_DEBUG);
				setEventMessages($langs->trans('ErrorFileNotFound'), '', 'errors');
			}
			// Mark document request as required
		} elseif (!empty($filecheck) && empty($cherchfile)) {
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET ".$filecheck." = 1 WHERE rowid = ".$this->id;
			$resql = $db->query($sql);
			$db->free($resql);
			if ($resql) {
				$this->setStatusFolder($user, $this::STATUS_FOLDER_LACK);
				$this->message = 'FilesChecked';
				$this->messages[] = $this->message;
			} else {
				$this->error = 'ErrorFailToFilesChecked';
				$this->errors[] = 'Error '.$this->db->lasterror();
				$error++;
				dol_syslog(__METHOD__." $this->id=".$this->id.", '".$doc."'=''", LOG_DEBUG);
			}

			// Mark document request as not required
		} elseif (empty($filecheck) && empty($cherchfile)) {
			$doccheck = $doc.'check';
			if (isset($this->$doccheck)) {
				$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET ".$doc."check = NULL WHERE rowid = ".$this->id;
				$resql = $db->query($sql);
				$db->free($resql);
				if ($resql) {
					$this->fetch($this->id);
					if (empty($this->fundoc1check) && empty($this->fundoc2check) && empty($this->fundoc3check) && empty($this->fundoc4check) && empty($this->fundoc5check) && $this->status_folder == $this::STATUS_FOLDER_LACK) {
						$this->setStatusFolder($user, $this::STATUS_FOLDER_LACKOK);
					}
					$this->message = 'FilesUnChecked';
					$this->messages[] = $this->message;
				} else {
					$this->error = 'ErrorFailToFilesUnChecked';
					$this->errors[] = 'Error '.$this->db->lasterror();
					$error++;
					dol_syslog(__METHOD__."id=".$this->id.", ".$doc."=''", LOG_DEBUG);
				}
			}
		}
		if (empty($error)) {
			return 1;
		} else {
			return -1 * $error;
		}
	}

	/**
	* @return  int	0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	*/
	public function cronFundingEnd()
	{
		global $user, $conf, $langs, $db;
		// setDefaultLang(getDolGlobalString('MAIN_LANG_DEFAULT'));
		$langs->load("funding@funding");

		$date = dol_now('tzserver');
		$result = '';
		$error = 0;
		$errormsg = '';
		$output = '';
		$output1 = '';
		$output2 = '';
		$subject = getDolGlobalString('MAIN_APPLICATION_TITLE'). ' - ' . $langs->transnoentitiesnoconv("OutputCronFundingEnd");



		$sql = 'SELECT rowid, ref, date_end, fk_funding_type, status_folder, status';
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as f';
		$sql .= ' WHERE f.date_end < "'.dol_print_date($date, 'dayrfc').'"';
		//$sql .= ' AND (f.status_folder <> '.self::STATUS_FOLDER_EXTENSION.' OR f.status_folder IS NULL)';
		$sql .= ' AND f.status = '.self::STATUS_RUNNING;
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($num = $this->db->num_rows($resql)) {
				$i = 1;
				$soc = new Societe($db);
				$funding = new Funding($db);
				while ($i <= $num) {
					$obj = $this->db->fetch_object($resql);
					$funding->fetch($obj->rowid);
					$soc->fetch($funding->fk_soc);
					if (empty($obj)) {
						break; // Should not happen
					}
					if (!empty($conf->global->FUNDING_NOCLOSEDFINISHAUTO_EXTENSION) && $obj->fk_funding_type != $conf->global->FUNDING_NOCLOSEDFINISHAUTO_EXTENSION || $obj->status_folder == self::STATUS_FOLDER_DENOUNCED) {
						$status = self::STATUS_END;
						$triger = 'FUNDING_END';
						$result = $funding->setStatusCommon($user, $status, $notriger, $triger);
						if ($result >= 0) {
							$oldstatus = $funding->getLibStatut(1);
							$funding->fetch($obj->rowid);
							$output1 .= '<tr>';
							$output1 .= '<td><a href="'.DOL_MAIN_URL_ROOT.'/custom/funding/funding_card.php?id='.$obj->rowid.'">'.$obj->ref.'</a></td>';
							$output1 .= '<td> '.date('d-m-Y', strtotime($obj->date_end)).'</td>';
							$output1 .= '<td>'.$soc->nom.' ('.$soc->name_alias.')</td>';
							$output1 .= '<td>'.$funding->getLibStatutFolder(1).'</td>';
							$output1 .= '<td>'.$oldstatus.'->'.$funding->getLibStatut(1).'</td>';
							$output1 .= '</tr>';
						} else {
							$error++;
							$errormsg .= 'Set status funding end failed for funding '.$obj->ref.' ('.$funding->id.')';
						}
					} else {
						$status = self::STATUS_FOLDER_EXTENSION;
						if ($funding->status_folder != self::STATUS_FOLDER_EXTENSION) {
							$oldstatusfolder = $funding->getLibStatutFolder(1);
							$result = $funding->setStatusFolder($user, $status, $notriger = 0);
							if ($result >= 0) {
								$funding->fetch($obj->rowid);
								$output2 .= '<tr>';
								$output2 .= '<td><a href="'.DOL_MAIN_URL_ROOT.'/custom/funding/funding_card.php?id='.$obj->rowid.'">'.$obj->ref.'</a></td>';
								$output2 .= '<td> '.date('d-m-Y', strtotime($obj->date_end)).'</td>';
								$output2 .= '<td>'.$soc->nom.' ('.$soc->name_alias.')</td>';
								$output2 .= '<td>'.$oldstatusfolder.'<br/>'.$funding->getLibStatutFolder(1).'</td>';
								$output2 .= '<td>'.$funding->getLibStatut(1).'</td>';
								$output2 .= '</tr>';
							} else {
								$error++;
								$errormsg .= 'Set status folder funding extension failed for funding '.$obj->ref.' ('.$funding->id.')';
							}
						}
					}
					$i++;
				}
			}
			$this->db->free($resql);
		} else {
			$error = $this->db;
		}

		if (!empty($num)) {
			if (!empty($output1)) {
				$output = '<table>';
				$output .= '<tr style="border:1px solid black; text-color: black; text-align: center; font-weight: bold; background-color: #bed0ec87;">';
				$output .= '<td colspan="5" style="text-color: #000;">';
				$output .= '<a href="'.DOL_MAIN_URL_ROOT.'/custom/funding/funding_list.php?search_status='.self::STATUS_END.'">'.$subject.' '.$this->LibStatut(self::STATUS_END, 1).'</a>';
				$output .= '</td></tr>';
				$output .= '<tr style="border:1px solid black; font-weight: bold; background-color: #bed0ec87;">';
				$output .= '<th>'.$langs->transnoentitiesnoconv("Ref").'</th>';
				$output .= '<th>'.$langs->transnoentitiesnoconv("Date").'</th>';
				$output .= '<th>'.$langs->transnoentitiesnoconv("ThirdParty").'</th>';
				$output .= '<th>'.$langs->transnoentitiesnoconv("StatusFolder").'</th>';
				$output .= '<th>'.$langs->transnoentitiesnoconv("Status").'</th>';
				$output .= '</tr>';
				$output .= $output1 . '</table><br/><br/>';
			}
			if (!empty($output2)) {
				$output = '<table>';
				$output .= '<tr style="border:1px solid black; text-color: black; text-align: center; font-weight: bold; background-color: #bed0ec87;">';
				$output .= '<td colspan="5" style="text-color: #000;">';
				$output .= '<a href="'.DOL_MAIN_URL_ROOT.'/custom/funding/funding_list.php?search_statusfolder='.self::STATUS_FOLDER_EXTENSION.'">'.$subject.' '.$this->LibStatutFolder(self::STATUS_FOLDER_EXTENSION, 1).'</a>';
				$output .= '</td></tr>';
				$output .= '<tr style="border:1px solid black; font-weight: bold; background-color: #bed0ec87;">';
				$output .= '<th>'.$langs->transnoentitiesnoconv("Ref").'</th>';
				$output .= '<th>'.$langs->transnoentitiesnoconv("Date").'</th>';
				$output .= '<th>'.$langs->transnoentitiesnoconv("ThirdParty").'</th>';
				$output .= '<th>'.$langs->transnoentitiesnoconv("StatusFolder").'</th>';
				$output .= '<th>'.$langs->transnoentitiesnoconv("Status").'</th>';
				$output .= '</tr>';
				$output .= $output2 . '</table>';
			}
		}
		if (empty($num) && empty($output)) {
			$output = $langs->transnoentitiesnoconv("OutputNoSetStatusCronFundingEnd");
		} elseif (!empty($output)) {
			$result = $this->sendMail('', $conf->global->FUNDING_MAIL_DEFAULT, $subject, $output);
			if ($result <= 0) {
				$error++;
				$errormsg .= 'Send mail not found';
			}
		}

		dol_syslog(__METHOD__, LOG_DEBUG);

		$this->output = $output;
		$this->error = $errormsg;

		if (!empty($error)) {
			return $error;
		} else {
			return 0;
		}
	}

	/**
	* @param   int          $duration    delais de comparaison (mois)
	* @return  int	0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	*/
	public function cronFundingSoonFinished($duration = 6)
	{
		global $user, $conf, $langs, $db;
		// setDefaultLang(getDolGlobalString('MAIN_LANG_DEFAULT'));
		$langs->load("funding@funding");

		$date = dol_now('tzserver');
		$dateEnd = date('Y-m-d', strtotime('+'.$duration.' month', $date));
		$outputinit = '';
		$output = '';
		$outputtotal = '';
		$error = 0;
		$errormsg = '';
		$beforusersale = 0;
		$beforusersalemail = "";

		$subject = getDolGlobalString('MAIN_APPLICATION_TITLE'). ' - ' . $langs->trans("OutputCronFundingsSoonFinished");

		$outputinit = '<table>';
		$outputinit .= '<tr style="border:1px solid black; text-color: black; text-align: center; font-weight: bold; background-color: #bed0ec87;">';
		$outputinit .= '<td colspan="5" style="text-color: #000;">';
		$outputinit .= '<a href="'.DOL_MAIN_URL_ROOT.'/custom/funding/funding_list.php?search_status='.self::STATUS_RUNNING.'">'.$subject.'</a>';
		$outputinit .= '</td></tr>';
		$outputinit .= '<tr style="border:1px solid black; font-weight: bold; background-color: #bed0ec87;">';
		$outputinit.= '<th>'.$langs->transnoentitiesnoconv("Ref").'</th>';
		$outputinit .= '<th>'.$langs->transnoentitiesnoconv("Date").'</th>';
		$outputinit .= '<th>'.$langs->transnoentitiesnoconv("ThirdParty").'</th>';
		$outputinit .= '<th>'.$langs->transnoentitiesnoconv("StatusFolder").'</th>';
		$outputinit .= '<th>'.$langs->transnoentitiesnoconv("Status").'</th>';
		$outputinit .= '</tr>';

		$output = $outputinit;

		$sql = 'SELECT rowid, ref, fk_soc, fk_user_comm, date_end, status_folder, status';
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as f';
		// $sql .= ' WHERE f.date_end >= "'.dol_print_date($date, 'dayrfc').'"';
		// $sql .= ' AND f.date_end <= "'.$dateEnd.'"';
		$sql .= ' WHERE f.date_end <= "'.$dateEnd.'"';
		$sql .= ' AND f.status = '.self::STATUS_RUNNING;
		$sql .= ' ORDER BY f.fk_user_comm, f.date_end ASC'; //DESC
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if (!empty($num)) {
				$i = 1;
				$soc = new Societe($db);
				$comm = new User($db);
				$funding = new Funding($db);
				// $FundingSoonFinished = array();
				// On rajoute 1 a num pour envoyer le dernier mail
				while ($i <= $num+1) {
					if ($i < $num+1) {
						// Retrieving information
						$obj = $this->db->fetch_object($resql);
						$funding->fetch($obj->rowid);
						$soc->fetch($obj->fk_soc);
						$comm->fetch($obj->fk_user_comm);
					}
					if ($comm->id != $beforusersale && $i > 1) {
						// On referme la liste
						$output .= '</table>';
						// On stock la liste total pour envoyer le rapport
						$outputtotal .= '<h1 style="text-align: center;">'.$beforusersalename.'</h1>'.$output;
						// Envoie du mail
						if (!empty($beforusersalemail) && !empty($output)) {
							$output = $langs->transnoentitiesnoconv("FundingMessageMailIntroText", $beforusersalename) .'<br/><br/>' .$langs->transnoentitiesnoconv("FundingMessageMailMessageText").'<br/><br/>'. $output;
							$result = $funding->sendMail('', $beforusersalemail, dol_string_nohtmltag($subject), $output);
							if ($result <= 0) {
								$error++;
								$errormsg .= 'Send mail not found';
							}
							// Reset variables to send the mail to the previous salesperson
							$beforusersale = 0;
							$beforusersalename = "";
							$beforusersalemail = "";
							// Reset the list
							$output = $outputinit;
						}
					}
					if (empty($obj)) {
						break; // Should not happen
					}
					$output .= '<tr>';
					$output .= '<td><a href="'.DOL_MAIN_URL_ROOT.'/custom/funding/funding_card.php?id='.$obj->rowid.'">'.$obj->ref.'</a></td>';
					$output .= '<td> '.date('d-m-Y', strtotime($obj->date_end)).'</td>';
					$output .= '<td>'.$soc->nom.' ('.$soc->name_alias.')</td>';
					$output .= '<td>'.$funding->getLibStatutFolder(1).'</td>';
					$output .= '<td>'.$funding->getLibStatut(1).'</td>';
					$output .= '</tr>';
					// Store salesperson info to send the mail
					$beforusersale = $comm->id;
					$beforusersalename = $comm->firstname.' '.$comm->lastname;
					$beforusersalemail = $comm->email;
					$i++;
				}
				// Send mail with all fundings that will soon finish
				if (!empty($conf->global->FUNDING_MAIL_REPORT) && !empty($outputtotal)) {
					$subject = getDolGlobalString('MAIN_APPLICATION_TITLE'). ' - ' . $langs->transnoentitiesnoconv("OutputCronFundingsSoonFinishedAll");
					$result = $funding->sendMail('', $conf->global->FUNDING_MAIL_REPORT, dol_string_nohtmltag($subject), $outputtotal);
					if ($result <= 0) {
						$error++;
						$errormsg .= 'Send mail not found';
					}
				}
			} else {
				$output = $langs->transnoentitiesnoconv("NotFundingSoonFinished");
			}
			$this->db->free($resql);
		} else {
			$error = $this->db;
		}

		dol_syslog(__METHOD__, LOG_DEBUG);

		$this->output = $outputtotal;
		$this->error = $errormsg;

		if (!empty($error)) {
			return $error;
		} else {
			return 0;
		}
	}

	/**
	* @param   int          $from   	from
	* @param   int          $sendto    	sender
	* @param   int          $subject    subject
	* @param   int          $message    message
	* @param   int          $filename   filename
	* @return  int	0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	*/
	public function sendMail($from = '', $sendto = '', $subject = '', $message = '', $filename = '')
	{
		global $conf, $langs;

		// Send email to assigned user

		if (empty($from)) {
			$from = dol_escape_htmltag($conf->global->MAIN_MAIL_EMAIL_FROM);
		} else {
			$from = dol_escape_htmltag($from);
		}

		if (empty($sendto)) {
			$sendto = dol_escape_htmltag($conf->global->FUNDING_MAIL_DEFAULT);
		} else {
			$sendto = dol_escape_htmltag($sendto);
		}

		if (empty($subject)) {
			$subject = dol_escape_htmltag($langs->convToOutputCharset($langs->transnoentitiesnoconv("Funding")));
		} else {
			$subject = mb_encode_mimeheader($subject, 'UTF-8');
			$subject = dol_escape_htmltag($langs->convToOutputCharset($subject));
		}

		if (empty($message)) {
			$message = $langs->transnoentitiesnoconv("Funding");
		}

		if (empty($filename)) {
			$filename = $filename;
		}

		include_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';
		$mailfile = new CMailFile(utf8_encode($subject), $sendto, $from, $message, '', '', '', '', '', 0, 1);
		$result = $mailfile->sendfile();

		return $result;
	}
}
