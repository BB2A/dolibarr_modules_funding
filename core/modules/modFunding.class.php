<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
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
 * 	\defgroup   funding     Module Funding
 *  \brief      Funding module descriptor.
 *
 *  \file       htdocs/funding/core/modules/modFunding.class.php
 *  \ingroup    funding
 *  \brief      Description and activation file for module Funding
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module Funding
 */
class modFunding extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 183004; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'funding';
		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "financial";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';
		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleFundingName' not found (Funding is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModuleFundingDesc' not found (Funding is name of module).
		$this->description = "ModuleFundingDesc";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "ModuleFundingDescLong";
		$this->editor_name = 'BB2A';
		$this->editor_url = 'https://www.bb2a.fr';
		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.1.6';
		// Url to the file with your last numberversion of this module
		$this->url_last_version = 'https://www.dolibarrversion.bb2a.fr/versionmodulefunding.txt';
		//$this->needUpdate = '1.0.1';

		// Key used in llx_const table to save module status enabled/disabled (where FUNDING is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'fa-piggy-bank infobox-action';
		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 1,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 1,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
			// '/funding/css/funding.css.php',
			),
			// Set this to 1 if module is a web service module
			'api' => 1,
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/funding/js/funding.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
				  'data' => array(
					  'emailtemplates',
					  'globalcard',
					  'formmail',
					  'notification',
					  'onlinesign',
					  'propallist',
					  'orderlist',
					  'invoicelist',
				  ),
				 // 'entity' => '0',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);
		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/funding/temp","/funding/subdir");
		$this->dirs = array("/funding/temp");
		// Config pages. Put here list of php page, stored into funding/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@funding");
		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array();
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->langfiles = array("funding@funding");
		$this->phpmin = array(8); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(18); // Minimum version of Dolibarr required by module
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'FundingWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;                             // If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('FUNDING_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('FUNDING_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array(
			//1 => array('FUNDING_FUND_PREFIX', 'chaine', 'FUND', '', 0, 1, 0),
			//2 => array('FUNDING_COEF_PREFIX', 'chaine', 'COEF', '', 0, 1, 0),
		);

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->funding) || !isset($conf->funding->enabled)) {
			$conf->funding = new stdClass();
			$conf->funding->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
		if (DOL_VERSION >= '16.0.0') {
			$this->tabs[0] = array('data'=>'thirdparty:+Funding:Fundings,funding,/funding/class/funding.class.php,getcountForThird:mylangfile@funding:$user->rights->funding->lists:/funding/funding_list.php?socid=__ID__');
			$this->tabs[1] = array('data'=>'propal:+Funding:Funding,funding,/funding/class/funding.class.php,getcountForPropal:mylangfile@funding:$user->rights->funding->read:/funding/funding_card.php?typedoc=propal&iddoc=__ID__');
			$this->tabs[2] = array('data'=>'order:+Funding:Funding,funding,/funding/class/funding.class.php,getcountForOrder:mylangfile@funding:$user->rights->funding->read:/funding/funding_card.php?typedoc=order&iddoc=__ID__');
		} else {
			$this->tabs[0] = array('data'=>'thirdparty:+Funding:Fundings:mylangfile@funding:$user->rights->funding->lists:/funding/funding_list.php?socid=__ID__');
			$this->tabs[1] = array('data'=>'propal:+Funding:Funding:mylangfile@funding:$user->rights->funding->read:/funding/funding_card.php?typedoc=propal&iddoc=__ID__');
			$this->tabs[2] = array('data'=>'order:+Funding:Funding:mylangfile@funding:$user->rights->funding->read:/funding/funding_card.php?typedoc=order&iddoc=__ID__');
		}

		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@funding:$user->rights->funding->read:/funding/mynewtab1.php?id=__ID__');                   // To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@funding:$user->rights->othermodule->read:/funding/mynewtab2.php?id=__ID__',   // To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                                                           // To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'     to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'   to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'          to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		$this->dictionaries=array(
			'langs'=>'funding@funding',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array("c_funding_duration","c_funding_scale","c_funding_type"),
			// Label of tables
			'tablib'=>array("Funding_duration","Funding_scale","Funding_type"),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'c_funding_duration as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'c_funding_scale as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'c_funding_type as f'),
			// Sort order
			'tabsqlsort'=>array("label ASC","label ASC","label ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("code,label","code,label","code,label"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("code,label","code,label","code,label"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("code,label","code,label","code,label"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid","rowid","rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->funding->enabled,$conf->funding->enabled,$conf->funding->enabled)
		);

		// Boxes/Widgets
		// Add here list of php file(s) stored in funding/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'fundingwidget1.php@funding',
			//      'note' => 'Widget provided by Funding',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			0 => array(
				'entity'=>1,
				'label' => 'CronFundingEnd',
				'jobtype' => 'method',
				'class' => '/funding/class/funding.class.php',
				'objectname' => 'Funding',
				'method' => 'cronFundingEnd',
				'parameters' => '',
				'comment' => 'CommentCronFundingEnd',
				'frequency' => 1,
				'unitfrequency' => 86400,
				'status' => 0,
				'test' => isModEnabled('funding'),
				'priority' => 50,
			 ),
			 1 => array(
				'entity'=>1,
				'label' => 'CronFundingSoonFinished',
				'jobtype' => 'method',
				'class' => '/funding/class/funding.class.php',
				'objectname' => 'Funding',
				'method' => 'cronFundingSoonFinished',
				'parameters' => '',
				'comment' => 'CommentCronFundingSoonFinished',
				'frequency' => 4,
				'unitfrequency' => 604800,
				'status' => 0,
				'test' => isModEnabled('funding'),
				'priority' => 50,
			 ),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>isModEnabled('funding'), 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>isModEnabled('funding'), 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightReadFunding'; // Permission label
		$this->rights[$r][4] = 'read'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightCreateUpdateFunding'; // Permission label
		$this->rights[$r][4] = 'write'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightDeleteFunding'; // Permission label
		$this->rights[$r][4] = 'delete'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightManageFunding'; // Permission label
		$this->rights[$r][4] = 'manage'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightListsFunding'; // Permission label
		$this->rights[$r][4] = 'lists'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		//Permission for coefficient
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightReadCoefficient'; // Permission label
		$this->rights[$r][4] = 'coefficient'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightCreateUpdateCoefficient'; // Permission label
		$this->rights[$r][4] = 'coefficient'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightDeleteCoefficient'; // Permission label
		$this->rights[$r][4] = 'coefficient'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		//Permission for retention
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightReadRetention'; // Permission label
		$this->rights[$r][4] = 'retention'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightCreateUpdateRetention'; // Permission label
		$this->rights[$r][4] = 'retention'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'RightDeleteRetention'; // Permission label
		$this->rights[$r][4] = 'retention'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->funding->level1->level2)
		$r++;
		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// $this->menu[$r++]=array(
		// 	'fk_menu'=>'',                          // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
		// 	'type'=>'top',                          // This is a Top menu entry
		// 	'titre'=>'ModuleFundingName',
		// 	'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
		// 	'mainmenu'=>'funding',
		// 	'url'=>'/funding/fundingindex.php',
		// 	'langs'=>'funding@funding',         // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
		// 	'position'=>50,
		// 	'enabled'=>'$conf->funding->enabled',  // Define condition to show or hide menu entry. Use '$conf->listes->enabled' if entry must be visible if module is enabled.
		// 	'perms'=>'$user->rights->funding->lists',                            // Use 'perms'=>'$user->rights->listes->level1->level2' if you want your menu with a permission rules
		// 	'target'=>'',
		// 	'user'=>2,                              // 0=Menu for internal users, 1=external users, 2=both
		// );
		// Add here entries to declare new menus
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=bank', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left', // This is a Top menu entry
			'titre'=>'ModuleFundingName',
			'prefix' =>img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'bank',
			'leftmenu'=>'funding',
			'url'=>'/funding/fundingindex.php',
			'langs'=>'funding@funding', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->funding->enabled', // Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->funding->read', // Use 'perms'=>'$user->rights->funding->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>0, // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=funding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFunding',
			'mainmenu'=>'bank',
			'leftmenu'=>'ListFunding',
			'url'=>'/funding/funding_list.php?search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFunding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'Draft',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=0&search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFunding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'New',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=1&search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFunding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFundingUpdate',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=2&search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFunding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFundingAccept',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=4&search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFunding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'Denied',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=5&search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFunding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFundingRunning',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=6&search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFunding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFundingEnd',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=7&search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFunding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFundingExtension',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_extension=1&search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFunding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'Canceled',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=8&search_origin=order',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=funding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFundingPropal',
			'mainmenu'=>'bank',
			'leftmenu'=>'ListFundingPropal',
			'url'=>'/funding/funding_list.php?search_origin=propal',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1200+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFundingPropal',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'Draft',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=0&search_origin=propal',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1200+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFundingPropal',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'New',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=1&search_origin=propal',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1200+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFundingPropal',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFundingAccept',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=4&search_origin=propal',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1200+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFundingPropal',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'Denied',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=5&search_origin=propal',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1200+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=ListFundingPropal',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'Canceled',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/funding_list.php?search_status=8&search_origin=propal',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1200+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=funding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFundingCoef',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'/funding/coefficient_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1300+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->coefficient->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=bank,fk_leftmenu=funding',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ListFundingRetention',
			'mainmenu'=>'bank',
			'leftmenu'=>'',
			'url'=>'funding/retention_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'funding@funding',
			'position'=>1300+$r,
			// Define condition to show or hide menu entry. Use '$conf->funding->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->funding->enabled',
			// Use 'perms'=>'$user->rights->funding->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->funding->retention->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>0
		);

		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT FUNDING */
		/*
		$langs->load("funding@funding");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='FundingLines'; // Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='funding@funding';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'Funding'; $keyforclassfile='/funding/class/funding.class.php'; $keyforelement='funding@funding';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'FundingLine'; $keyforclassfile='/funding/class/funding.class.php'; $keyforelement='fundingline@funding'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='funding'; $keyforaliasextra='extra'; $keyforelement='funding@funding';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='fundingline'; $keyforaliasextra='extraline'; $keyforelement='fundingline@funding';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('fundingline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'funding as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'funding_line as tl ON tl.fk_funding = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('funding').')';
		$r++; */
		/* END MODULEBUILDER EXPORT FUNDING */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT FUNDING */
		/*
		 $langs->load("funding@funding");
		 $this->export_code[$r]=$this->rights_class.'_'.$r;
		 $this->export_label[$r]='FundingLines';    // Translation key (used only if key ExportDataset_xxx_z not found)
		 $this->export_icon[$r]='funding@funding';
		 $keyforclass = 'Funding'; $keyforclassfile='/funding/class/funding.class.php'; $keyforelement='funding@funding';
		 include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		 $keyforselect='funding'; $keyforaliasextra='extra'; $keyforelement='funding@funding';
		 include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		 //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		 $this->export_sql_start[$r]='SELECT DISTINCT ';
		 $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'funding as t';
		 $this->export_sql_end[$r] .=' WHERE 1 = 1';
		 $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('funding').')';
		 $r++; */
		/* END MODULEBUILDER IMPORT FUNDING */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		$result = $this->_load_tables('/funding/sql/');
		if ($result < 0) {
			return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')
		}

		// Create extrafields during init
		//include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		//$extrafields = new ExtraFields($this->db);
		//$result1=$extrafields->addExtraField('funding_myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'funding@funding', '$conf->funding->enabled');
		//$result2=$extrafields->addExtraField('funding_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'funding@funding', '$conf->funding->enabled');
		//$result3=$extrafields->addExtraField('funding_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'funding@funding', '$conf->funding->enabled');
		//$result4=$extrafields->addExtraField('funding_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'funding@funding', '$conf->funding->enabled');
		//$result5=$extrafields->addExtraField('funding_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'funding@funding', '$conf->funding->enabled');

		// Permissions
		$this->remove($options);

		$sql = array();

		// Document templates
		$moduledir = 'funding';
		$myTmpObjects = array();
		$myTmpObjects['Funding']=array('includerefgeneration'=>0, 'includedocgeneration'=>0);

		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			if ($myTmpObjectKey == 'Funding') {
				continue;
			}
			if ($myTmpObjectArray['includerefgeneration']) {
				$src=DOL_DOCUMENT_ROOT.'/install/doctemplates/funding/template_fundings.odt';
				$dirodt=DOL_DATA_ROOT.'/doctemplates/funding';
				$dest=$dirodt.'/template_fundings.odt';

				if (file_exists($src) && ! file_exists($dest)) {
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result=dol_copy($src, $dest, 0, 0);
					if ($result < 0) {
						$langs->load("errors");
						$this->error=$langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					//"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."','".strtolower($myTmpObjectKey)."',".$conf->entity.")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					//"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".strtolower($myTmpObjectKey)."', ".$conf->entity.")"
				));
			}
		}
		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
