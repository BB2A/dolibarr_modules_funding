<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2020-2023 BERTON Anthony 			<anthony.berton@bb2a.fr>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *      \file       funding_list.php
 *      \ingroup    funding
 *      \brief      List page for funding
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');                    // Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');                  // Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');                   // Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');                  // Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');          // Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');         // Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');                    // Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');                 // Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');                   // Do not check style html tag into posted data
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');                      // Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');                  // If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');                  // If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');                  // Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');                        // If this page is public (can be called outside logged session)
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');           // Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');        // Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', '1');        // The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("XFRAMEOPTIONS_ALLOWALL"))   define('XFRAMEOPTIONS_ALLOWALL', '1');         // Do not add the HTTP header 'X-Frame-Options: SAMEORIGIN' but 'X-Frame-Options: ALLOWALL'

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

// load funding libraries
require_once __DIR__.'/class/funding.class.php';

// for other modules
require_once DOL_DOCUMENT_ROOT.'/core/lib/propal.lib.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

// Load translation files required by the page
$langs->loadLangs(array("funding@funding", "Propal", "Orders", "other"));

// The action 'add', 'create', 'edit', 'update', 'view', ...
$action         = GETPOST('action', 'aZ09') ?GETPOST('action', 'aZ09') : 'view';
// The bulk action (combo box choice in lists)
$massaction     = GETPOST('massaction', 'alpha');
// Show the files area generated by bulk actions
$show_files     = GETPOST('show_files', 'int');
// Result of a confirmation
$confirm        = GETPOST('confirm', 'alpha');
// A Cancel button was clicked
$cancel         = GETPOST('cancel', 'alpha');
// Array of IDs of elements selected in a list
$toselect       = GETPOST('toselect', 'array');
// To manage different search contexts
$contextpage    = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'fundinglist';
// Go back to a dedicated page
$backtopage     = GETPOST('backtopage', 'alpha');
// Option for CSS output (always '' except when 'print')
$optioncss      = GETPOST('optioncss', 'aZ');

$id             = GETPOST('id', 'int');
$socid          = GETPOST('socid', 'int');
$iddoc          = GETPOST('iddoc', 'int');
$typedoc        = GETPOST('typedoc', 'alpha');

$filter         = GETPOST('filter', 'alpha');

// Load variable for pagination
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	$page = 0;
}
// If $page is not defined, is empty or is -1, or if clear filters was clicked
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Initialize technical objects
$object = new Funding($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->funding->dir_output.'/temp/massgeneration/'.$user->id;
// Note that conf->hooks_modules contains an array
$hookmanager->initHooks(array('fundinglist'));

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);
//$extrafields->fetch_name_optionals_label($object->table_element_line);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Default sort order (if not yet defined by previous GETPOST)
if (!$sortfield) {
	$sortfield = "t.".key($object->fields); // Set here default search field. By default 1st field in definition.
}
if (!$sortorder) {
	$sortorder = "DESC";
}

// Initialize array of search criterias
$search_all = GETPOST('search_all', 'alphanohtml');
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha') !== '') {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
	if (preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
		$search[$key.'_dtstart'] = dol_mktime(0, 0, 0, GETPOST('search_'.$key.'_dtstartmonth', 'int'), GETPOST('search_'.$key.'_dtstartday', 'int'), GETPOST('search_'.$key.'_dtstartyear', 'int'));
		$search[$key.'_dtend'] = dol_mktime(23, 59, 59, GETPOST('search_'.$key.'_dtendmonth', 'int'), GETPOST('search_'.$key.'_dtendday', 'int'), GETPOST('search_'.$key.'_dtendyear', 'int'));
	}
}

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array();
foreach ($object->fields as $key => $val) {
	// Fix PHP8 isset($val['searchall'])
	if (isset($val['searchall'])) {
		$fieldstosearchall['t.'.$key] = $val['label'];
	}
}

// Enable custom rent
if (!empty($conf->global->FUNDING_ENABLED_RENTEDIT) && isset($search['origin']) && $search['origin'] != 'propal') {
	// Hide field already shown in banner
	unset($object->fields['amount_rent_edit']);
}

// Definition of fields for list
$arrayfields = array();
foreach ($object->fields as $key => $val) {
	// If $val['visible']==0, then we never show the field
	if (!empty($val['visible'])) {
		$arrayfields['t.'.$key] = array('label'=>$val['label'], 'checked'=>(($val['visible'] < 0) ? 0 : 1), 'enabled'=>($val['enabled'] && ($val['visible'] != 3)), 'position'=>$val['position']);
	}
}
// Extra fields
// Fix PHP8 isset($extrafields->attributes[$object->table_element]['label'])
if (is_array(isset($extrafields->attributes[$object->table_element]['label']) ? $extrafields->attributes[$object->table_element]['label'] : '') && count($extrafields->attributes[$object->table_element]['label']) > 0) {
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) {
		if (!empty($extrafields->attributes[$object->table_element]['list'][$key])) {
			$arrayfields["ef.".$key] = array(
				'label'=>$extrafields->attributes[$object->table_element]['label'][$key],
				'checked'=>(($extrafields->attributes[$object->table_element]['list'][$key] < 0) ? 0 : 1),
				'position'=>$extrafields->attributes[$object->table_element]['pos'][$key],
				'enabled'=>(abs($extrafields->attributes[$object->table_element]['list'][$key]) != 3 && $extrafields->attributes[$object->table_element]['perms'][$key])
			);
		}
	}
}
$object->fields = dol_sort_array($object->fields, 'position');
$arrayfields = dol_sort_array($arrayfields, 'position');

$permissiontoread = $user->rights->funding->read;
$permissiontoadd = $user->rights->funding->write;
$permissiontodelete = $user->rights->funding->delete;
// Used by the send_mail_org function
$permissionmanage = $user->rights->funding->manage;

// Security check - Protection if external user
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}
if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->statut == $object::STATUS_DISABLE) ? 1 : 0);
//$result = restrictedArea($user, 'funding', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

if (!isModEnabled("funding")) {
	accessforbidden();
}
if (!$permissiontoread) accessforbidden();



/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) {
	$action = 'list';
	$massaction = '';
}
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') {
	$massaction = '';
}

if ($action == 'validate' && $permissiontoadd) {
	if (GETPOST('confirm') == 'yes') {
		$tmpfunding = new Funding($db);
		$db->begin();
		$error = 0;
		foreach ($toselect as $checked) {
			if ($tmpfunding->fetch($checked)) {
				if ($tmpfunding->status == $object::STATUS_DRAFT) {
					if ($tmpfunding->validate($user) > 0) {
						$validateok .= $langs->trans('hasBeenValidated', $tmpfunding->ref)."<br/>";
					} else {
						setEventMessage($langs->trans('CantBeValidated'), 'errors');
						$error++;
					}
				} else {
					$langs->load("errors");
					setEventMessage($langs->trans('ErrorIsNotADraft', $tmpfunding->ref), 'errors');
					$error++;
				}
			} else {
				dol_print_error($db);
				$error++;
			}
		}
		if ($error) {
			$db->rollback();
		} else {
			setEventMessage($validateok, 'mesgs');
			$db->commit();
		}
	}
}

if ($action == 'sendorg' && $permissionmanage) {
	if (GETPOST('confirm') == 'yes') {
		$tmpfunding = new Funding($db);
		$db->begin();
		$error = 0;
		foreach ($toselect as $checked) {
			if ($tmpfunding->fetch($checked)) {
				if ($tmpfunding->status == $tmpfunding::STATUS_VALIDATED  && $tmpfunding->status < $tmpfunding::STATUS_ACCEPT) {
					if ($tmpfunding->setStatusFolder($user, 1) > 0) {
						$validateok .= $langs->trans('hasBeenSendOrg', $tmpfunding->ref)."<br/>";
					} else {
						setEventMessage($langs->trans('CantBeSendOrg', $tmpfunding->ref), 'errors');
						$error++;
					}
				} else {
					$langs->load("errors");
					setEventMessage($langs->trans('ErrorIsNotValidateOrAccepted', $tmpfunding->ref), 'errors');
					$error++;
				}
			} else {
				dol_print_error($db);
				$error++;
			}
		}
		if ($error) {
			$db->rollback();
		} else {
			setEventMessage($validateok, 'mesgs');
			$db->commit();
		}
	}
}

if ($action == 'lack' && $permissionmanage) {
	if (GETPOST('confirm') == 'yes') {
		$tmpfunding = new Funding($db);
		$db->begin();
		$error = 0;
		foreach ($toselect as $checked) {
			if ($tmpfunding->fetch($checked)) {
				if ($tmpfunding->status >= $tmpfunding::STATUS_VALIDATED && $tmpfunding->status <= $tmpfunding::STATUS_ACCEPT) {
					if ($tmpfunding->setStatusFolder($user, 2) > 0) {
						$validateok .= $langs->trans('hasBeenLack', $tmpfunding->ref)."<br/>";
					} else {
						setEventMessage($langs->trans('CantBeLack', $tmpfunding->ref), 'errors');
						$error++;
					}
				} else {
					$langs->load("errors");
					setEventMessage($langs->trans('ErrorIsNotValidateOrAccepted', $tmpfunding->ref), 'errors');
					$error++;
				}
			} else {
				dol_print_error($db);
				$error++;
			}
		}
		if ($error) {
			$db->rollback();
		} else {
			setEventMessage($validateok, 'mesgs');
			$db->commit();
		}
	}
}

if ($action == 'accepted' && $permissiontoadd) {
	if (GETPOST('confirm') == 'yes') {
		$tmpfunding = new Funding($db);
		$db->begin();
		$error = 0;
		foreach ($toselect as $checked) {
			if ($tmpfunding->fetch($checked)) {
				if ($tmpfunding->status == $object::STATUS_VALIDATED) {
					if ($tmpfunding->setAcceptedRefused($user, $object::STATUS_ACCEPT) > 0) {
						$validateok .= $langs->trans('fundingaccepted', $tmpfunding->ref)."<br/>";
					} else {
						setEventMessage($langs->trans('fundingnotaccepted', $tmpfunding->ref), 'errors');
						$error++;
					}
				} else {
					$langs->load("errors");
					setEventMessage($langs->trans('ErrorIsNotAValidate', $tmpfunding->ref), 'errors');
					$error++;
				}
			} else {
				dol_print_error($db);
				$error++;
			}
		}
		if ($error) {
			$db->rollback();
		} else {
			setEventMessage($validateok, 'mesgs');
			$db->commit();
		}
	}
}

if ($action == 'refused' && $permissiontoadd) {
	if (GETPOST('confirm') == 'yes') {
		$tmpfunding = new Funding($db);
		$db->begin();
		$error = 0;
		foreach ($toselect as $checked) {
			if ($tmpfunding->fetch($checked)) {
				if ($tmpfunding->status == $object::STATUS_VALIDATED) {
					if ($tmpfunding->setAcceptedRefused($user, $object::STATUS_DENIED) > 0) {
						$validateok .= $langs->trans('Refused', $tmpfunding->ref)."<br/>";
					} else {
						setEventMessage($langs->trans('CantBeDenied', $tmpfunding->ref), 'errors');
						$error++;
					}
				} else {
					$langs->load("errors");
					setEventMessage($langs->trans('ErrorIsNotAValidate', $tmpfunding->ref), 'errors');
					$error++;
				}
			} else {
				dol_print_error($db);
				$error++;
			}
		}
		if ($error) {
			$db->rollback();
		} else {
			setEventMessage($validateok, 'mesgs');
			$db->commit();
		}
	}
}

if ($action == 'running' && $permissiontoadd) {
	if (GETPOST('confirm') == 'yes') {
		$tmpfunding = new Funding($db);
		$db->begin();
		$error = 0;
		foreach ($toselect as $checked) {
			if ($tmpfunding->fetch($checked)) {
				if ($tmpfunding->status == $object::STATUS_ACCEPT && $tmpfunding->origin <> 'propal') {
					if ($tmpfunding->setRun($user) > 0) {
						$validateok .= $langs->trans('hasBeenRun', $tmpfunding->ref)."<br/>";
					} else {
						setEventMessage($langs->trans('CantBeRun', $tmpfunding->ref), 'errors');
						$error++;
					}
				} else {
					$langs->load("errors");
					setEventMessage($langs->trans('ErrorIsNotAccept', $tmpfunding->ref), 'errors');
					$error++;
				}
			} else {
				dol_print_error($db);
				$error++;
			}
		}
		if ($error) {
			$db->rollback();
		} else {
			setEventMessage($validateok, 'mesgs');
			$db->commit();
		}
	}
}

if ($action == 'extension' && $permissiontoadd) {
	if (GETPOST('confirm') == 'yes') {
		$tmpfunding = new Funding($db);
		$db->begin();
		$error = 0;
		foreach ($toselect as $checked) {
			if ($tmpfunding->fetch($checked)) {
				if ($tmpfunding->status == $tmpfunding::STATUS_RUNNING && $tmpfunding->origin == 'order') {
					if ($tmpfunding->SetStatusFolder($user, 3) > 0) {
						$validateok .= $langs->trans('hasBeenExtension', $tmpfunding->ref)."<br/>";
					} else {
						setEventMessage($langs->trans('CantBeExtension', $tmpfunding->ref), 'errors');
						$error++;
					}
				} else {
					$langs->load("errors");
					setEventMessage($langs->trans('ErrorIsNoRunning', $tmpfunding->ref), 'errors');
					$error++;
				}
			} else {
				dol_print_error($db);
				$error++;
			}
		}
		if ($error) {
			$db->rollback();
		} else {
			setEventMessage($validateok, 'mesgs');
			$db->commit();
		}
	}
}

if ($action == 'check' && $permissiontoadd) {
	if (GETPOST('confirm') == 'yes') {
		$tmpfunding = new Funding($db);
		$db->begin();
		$error = 0;
		foreach ($toselect as $checked) {
			if ($tmpfunding->fetch($checked)) {
				if ($tmpfunding->setChecked($user, $tmpfunding->id)) {
					$validateok .= $langs->trans('fundingcheked', $tmpfunding->ref)."<br/>";
				} else {
					setEventMessage($langs->trans('fundingnotcheked', $tmpfunding->ref), 'errors');
					$error++;
				}
			} else {
				dol_print_error($db);
				$error++;
			}
		}
		if ($error) {
			$db->rollback();
		} else {
			setEventMessage($validateok, 'mesgs');
			$db->commit();
		}
	}
}

if ($action == 'uncheck' && $permissiontoadd) {
	if (GETPOST('confirm') == 'yes') {
		$tmpfunding = new Funding($db);
		$db->begin();
		$error = 0;
		foreach ($toselect as $checked) {
			if ($tmpfunding->fetch($checked)) {
				if ($tmpfunding->setChecked($user, $tmpfunding->id, 0)) {
					$validateok .= $langs->trans('fundinguncheked', $tmpfunding->ref)."<br/>";
				} else {
					setEventMessage($langs->trans('fundingnotuncheked', $tmpfunding->ref), 'errors');
					$error++;
				}
			} else {
				dol_print_error($db);
				$error++;
			}
		}
		if ($error) {
			$db->rollback();
		} else {
			setEventMessage($validateok, 'mesgs');
			$db->commit();
		}
	}
}

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	// Selection of new fields
	include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

	// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
		foreach ($object->fields as $key => $val) {
			$search[$key] = '';
			if (preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
				$search[$key.'_dtstart'] = '';
				$search[$key.'_dtend'] = '';
			}
		}
		$toselect = array();
		$search_array_options = array();
	}
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')
		|| GETPOST('button_search_x', 'alpha') || GETPOST('button_search.x', 'alpha') || GETPOST('button_search', 'alpha')) {
		$massaction = ''; // Protection to avoid mass action if we force a new search during a mass action confirmation
	}

	// Mass actions
	$objectclass = 'Funding';
	$objectlabel = 'Funding';
	$uploaddir = $conf->funding->dir_output;
	include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';
}



/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

$now = dol_now();

//$help_url="EN:Module_Funding|FR:Module_Funding_FR|ES:Módulo_Funding";
$help_url = '';
$title = $langs->trans('ListOf', $langs->transnoentitiesnoconv("Fundings"));


// Build and execute select
// --------------------------------------------------------------------
$sql = 'SELECT ';
foreach ($object->fields as $key => $val) {
	$sql .= 't.'.$key.', ';
}
// Add fields from extrafields
// Fix PHP8 isset($extrafields->attributes[$object->table_element]['label'])
if (isset($extrafields->attributes[$object->table_element]['label'])  && !empty($extrafields->attributes[$object->table_element]['label'])) {
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) {
		$sql .= ($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? "ef.".$key.' as options_'.$key.', ' : '');
	}
}
// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters, $object); // Note that $action and $object may have been modified by hook
$sql .= preg_replace('/^,/', '', $hookmanager->resPrint);
$sql = preg_replace('/,\s*$/', '', $sql);
$sql .= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
// Fix PHP8 isset($extrafields->attributes[$object->table_element]['label'])
if (is_array(isset($extrafields->attributes[$object->table_element]['label'])?$extrafields->attributes[$object->table_element]['label']:'') && count($extrafields->attributes[$object->table_element]['label'])) {
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX.$object->table_element."_extrafields as ef on (t.rowid = ef.fk_object)";
}
if ($object->ismultientitymanaged == 1) {
	$sql .= " WHERE t.entity IN (".getEntity($object->element).")";
} else {
	$sql .= " WHERE 1 = 1";
}
// Filter when displayed in a third party - BB2A
if ($socid > 0) {
	$sql.= " AND (t.fk_soc = ".$socid." OR t.fk_soc_invoice = ".$socid." OR t.fk_org = ".$socid.")";
	// Setting to hide financial proposals
	if (empty($conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL)) {
		$sql.= " AND t.origin <> 'propal'";
	}
}
// Filter authorization to view specific funding records - BB2A
if (empty($user->rights->societe->client->voir) && empty($socid)) {
	$sql.= " AND (t.fk_user_comm = ".$user->id." OR t.fk_user_creat = ".$user->id." OR t.fk_user_modif = ".$user->id.")";
}

foreach ($search as $key => $val) {
	if (array_key_exists($key, $object->fields)) {
		if ($key == 'status' && $search[$key] == -1) {
			continue;
		}
		$mode_search = (($object->isInt($object->fields[$key]) || $object->isFloat($object->fields[$key])) ? 1 : 0);
		if ((strpos($object->fields[$key]['type'], 'integer:') === 0) || (strpos($object->fields[$key]['type'], 'sellist:') === 0) || !empty($object->fields[$key]['arrayofkeyval'])) {
			if ($search[$key] == '-1' || ($search[$key] === '0' && (empty($object->fields[$key]['arrayofkeyval']) || !array_key_exists('0', $object->fields[$key]['arrayofkeyval'])))) {
				$search[$key] = '';
			}
			$mode_search = 2;
		}
		if ($search[$key] != '') {
			$sql .= natural_search($key, $search[$key], (($key == 'status') ? 2 : $mode_search));
		}
	} else {
		if (preg_match('/(_dtstart|_dtend)$/', $key) && $search[$key] != '') {
			$columnName = preg_replace('/(_dtstart|_dtend)$/', '', $key);
			if (preg_match('/^(date|timestamp|datetime)/', $object->fields[$columnName]['type'])) {
				if (preg_match('/_dtstart$/', $key)) {
					$sql .= " AND t.".$columnName." >= '".$db->idate($search[$key])."'";
				}
				if (preg_match('/_dtend$/', $key)) {
					$sql .= " AND t." . $columnName . " <= '" . $db->idate($search[$key]) . "'";
				}
			}
		}
	}
}

if ($search_all) {
	$sql .= natural_search(array_keys($fieldstosearchall), $search_all);
}
//$sql.= dolSqlDateFilter("t.field", $search_xxxday, $search_xxxmonth, $search_xxxyear);
// Add where from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';
// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object); // Note that $action and $object may have been modified by hook
$sql .= $hookmanager->resPrint;

/* If a group by is required
$sql.= " GROUP BY ";
foreach($object->fields as $key => $val)
{
	$sql.='t.'.$key.', ';
}
// Add fields from extrafields
if (! empty($extrafields->attributes[$object->table_element]['label'])) {
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) $sql.=($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? "ef.".$key.', ' : '');
}
// Add where from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListGroupBy',$parameters);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
$sql=preg_replace('/,\s*$/','', $sql);
*/

$sql .= $db->order($sortfield, $sortorder);

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
	if (($page * $limit) > $nbtotalofrecords) { // if total of record found is smaller than page * limit, goto and load page 0
		$page = 0;
		$offset = 0;
	}
}
// if total of record found is smaller than limit, no need to do paging and to restart another select with limits set.
if (is_numeric($nbtotalofrecords) && ($limit > $nbtotalofrecords || empty($limit))) {
	$num = $nbtotalofrecords;
} else {
	if ($limit) {
		$sql .= $db->plimit($limit + 1, $offset);
	}

	$resql = $db->query($sql);
	if (!$resql) {
		dol_print_error($db);
		exit;
	}

	$num = $db->num_rows($resql);
}

// Direct jump if only one record found
if ($num == 1 && !empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && $search_all && !$page) {
	$obj = $db->fetch_object($resql);
	$id = $obj->rowid;
	header("Location: ".dol_buildpath('/funding/funding_card.php', 1).'?id='.$id);
	exit;
}

// Output page
// --------------------------------------------------------------------

llxHeader('', $title, $help_url);

// BB2A create third-party tabs
if (!empty($socid)) {
	// BB2A retrieve third-party data
	$soc = new Societe($db);
	if ($socid > 0 || ! empty($ref)) {
		$result = $soc->fetch($socid);
	}

	$head = societe_prepare_head($soc);
	dol_fiche_head($head, 'Funding', $langs->trans("ThirdParty"), 0, 'company');


	// BB2A display third-party frame

	$linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

	dol_banner_tab($soc, 'socid', $linkback, (empty($user->societe_id) ? 0 : 1), 'rowid', 'nom');

	$cssclass='titlefield';

	print '<div class="fichecenter">';

	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">';

	if (! empty($conf->global->SOCIETE_USEPREFIX)) {  // Old not used prefix field
		print '<tr><td class="'.$cssclass.'">'.$langs->trans('Prefix').'</td><td colspan="3">'.$soc->prefix_comm.'</td></tr>';
	}

	if ($soc->client) {
		print '<tr><td class="'.$cssclass.'">';
			print $langs->trans('CustomerCode').'</td><td colspan="3">';
			print $soc->code_client;
		if ($soc->check_codeclient() <> 0) {
			print ' <font class="error">('.$langs->trans("WrongCustomerCode").')</font>';
		}
			print '</td></tr>';
	}

	if ($soc->fournisseur) {
			print '<tr><td class="'.$cssclass.'">';
			print $langs->trans('SupplierCode').'</td><td colspan="3">';
			print $soc->code_fournisseur;
		if ($soc->check_codefournisseur() <> 0) {
			print ' <font class="error">('.$langs->trans("WrongSupplierCode").')</font>';
		}
			print '</td></tr>';
	}

	print '</table></div></div>';
}
 // BB2A end third-party frame display

// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';

$arrayofselected = is_array($toselect) ? $toselect : array();

$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
	$param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
	$param .= '&limit='.urlencode($limit);
}
foreach ($search as $key => $val) {
	if (is_array($search[$key]) && count($search[$key])) {
		foreach ($search[$key] as $skey) {
			$param .= '&search_'.$key.'[]='.urlencode($skey);
		}
	} else {
		$param .= '&search_'.$key.'='.urlencode($search[$key]);
	}
}
if ($optioncss != '') {
	$param .= '&optioncss='.urlencode($optioncss);
}
if (!empty($socid)) {
	$param = '&socid='.urlencode($socid);
}
// Add $param from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';

// List of mass actions available
$arrayofmassactions = array(
	//'validate'=>img_picto('', 'check', 'class="pictofixedwidth"').$langs->trans("Validate"),
	//'generate_doc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("ReGeneratePDF"),
	//'builddoc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("PDFMerge"),
	//'presend'=>img_picto('', 'email', 'class="pictofixedwidth"').$langs->trans("SendByMail"),
);
if ($permissiontoadd) {
	$arrayofmassactions['prevalidate'] = img_picto('', 'check', 'class="pictofixedwidth"').$langs->trans("Validate");
}
if ($permissionmanage) {
	$arrayofmassactions['presedorg'] = img_picto('', 'email', 'class="pictofixedwidth"').$langs->trans("BtnSendorg");
	$arrayofmassactions['prelack'] = img_picto('', 'folder', 'class="pictofixedwidth"').$langs->trans("BtnLack");
	$arrayofmassactions['preaccepted'] = img_picto('', 'check', 'class="pictofixedwidth"').$langs->trans("Accepted");
	$arrayofmassactions['prerefused'] = img_picto('', 'error', 'class="pictofixedwidth"').$langs->trans("Refused");
}
if ($permissiontoadd) {
	$arrayofmassactions['prerunning'] = img_picto('', 'clock', 'class="pictofixedwidth"').$langs->trans("BtnRunning");
	$arrayofmassactions['preextension'] = img_picto('', 'movement', 'class="pictofixedwidth"').$langs->trans("BtnExtension");
	$arrayofmassactions['precheck'] = img_picto('', 'check', 'class="pictofixedwidth"').$langs->trans("FunCheck");
	$arrayofmassactions['preuncheck'] = img_picto('', 'uncheck', 'class="pictofixedwidth"').$langs->trans("FunUnCheck");
}
if ($permissiontodelete) {
	$arrayofmassactions['predelete'] = img_picto('', 'delete', 'class="pictofixedwidth"').$langs->trans("Delete");
}
if (GETPOST('nomassaction', 'int') || in_array($massaction, array('presend', 'predelete'))) {
	$arrayofmassactions = array();
}
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

$urlform = '?';

if (!empty($socid)) {
	$urlform .= 'socid='.$socid;
}
if (!empty($search['origin'])) {
	$urlform .= 'search_origin='.$search['origin'];
}

print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].$urlform.'">'."\n";
if ($optioncss != '') {
	print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
}
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
//print '<input type="hidden" name="page" value="'.$page.'">';
print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';

//BB2A
if ($iddoc) {
	$newcardbutton = dolGetButtonTitle($langs->trans('New'), '', 'fa fa-plus-circle', dol_buildpath('/funding/funding_card.php', 1).'?typedoc='.$typedoc.'&iddoc='.$iddoc.'&action=create&backtopage='.urlencode($_SERVER['REQUEST_URI']), '', $permissiontoadd);
}

// Fix PHP8 isset($newcardbutton)?$newcardbutton:''
print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'object_'.$object->picto.' infobox-contrat valignmiddle widthpictotitle pictotitle', 0, isset($newcardbutton)?$newcardbutton:'', '', $limit, 0, 0, 1);

// Add code for pre mass action (confirmation or email presend form)
$topicmail = "SendFundingRef";
$modelmail = "funding";
$objecttmp = new Funding($db);
$trackid = 'xxxx'.$object->id;
include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';


if ($massaction == 'prevalidate') {
	print $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans("ConfirmMassValidation"), $langs->trans("ConfirmMassValidationQuestion"), "validate", null, '', 0, 200, 500, 1);
}
if ($massaction == 'presedorg') {
	print $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans("ConfirmMassSendOrg"), $langs->trans("ConfirmMassSendOrgQuestion"), "sendorg", null, '', 0, 200, 500, 1);
}
if ($massaction == 'prelack') {
	print $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans("ConfirmMassLAck"), $langs->trans("ConfirmMassLackQuestion"), "lack", null, '', 0, 200, 500, 1);
}
if ($massaction == 'preaccepted') {
	print $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans("ConfirmMassAccepted"), $langs->trans("ConfirmMassAcceptedQuestion"), "accepted", null, '', 0, 200, 500, 1);
}
if ($massaction == 'prerefused') {
	print $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans("ConfirmMassRefused"), $langs->trans("ConfirmMassRefusedQuestion"), "refused", null, '', 0, 200, 500, 1);
}
if ($massaction == 'prerunning') {
	print $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans("ConfirmMassRunning"), $langs->trans("ConfirmMassRunningQuestion"), "running", null, '', 0, 200, 500, 1);
}
if ($massaction == 'preextension') {
	print $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans("ConfirmMassExtension"), $langs->trans("ConfirmMassExtensionQuestion"), "extension", null, '', 0, 200, 500, 1);
}
if ($massaction == 'precheck') {
	print $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans("ConfirmMassExtension"), $langs->trans("ConfirmMassExtensionQuestion"), "check", null, '', 0, 200, 500, 1);
}
if ($massaction == 'preuncheck') {
	print $form->formconfirm($_SERVER["PHP_SELF"], $langs->trans("ConfirmMassExtension"), $langs->trans("ConfirmMassExtensionQuestion"), "uncheck", null, '', 0, 200, 500, 1);
}

if ($search_all) {
	foreach ($fieldstosearchall as $key => $val) {
		$fieldstosearchall[$key] = $langs->trans($val);
	}
	print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $search_all).join(', ', $fieldstosearchall).'</div>';
}

$moreforfilter = '';
/*$moreforfilter.='<div class="divsearchfield">';
$moreforfilter.= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="'.dol_escape_htmltag($search_myfield).'">';
$moreforfilter.= '</div>';*/

$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object); // Note that $action and $object may have been modified by hook
if (empty($reshook)) {
	$moreforfilter .= $hookmanager->resPrint;
} else {
	$moreforfilter = $hookmanager->resPrint;
}

if (!empty($moreforfilter)) {
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	print '</div>';
}

$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage, getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN', '')); // This also change content of $arrayfields
$selectedfields .= (count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="tagtable nobottomiftotal liste'.($moreforfilter ? " listwithfilterbefore" : "").'">'."\n";


// Fields title search
// --------------------------------------------------------------------
print '<tr class="liste_titre">';
// Action column (Show the massaction button only when this page is not opend from the Extended POS)
if (!empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
	print '<td class="liste_titre maxwidthsearch">';
	$searchpicto = $form->showFilterButtons('left');
	print $searchpicto;
	print '</td>';
}
foreach ($object->fields as $key => $val) {
	$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
	if ($key == 'status' || $key == 'status_folder') {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
	} elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
	} elseif (in_array($val['type'], array('timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
	} elseif (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && $val['label'] != 'TechnicalID' && empty($val['arrayofkeyval'])) {
		$cssforfield .= ($cssforfield ? ' ' : '').'right';
	}
	if (!empty($arrayfields['t.'.$key]['checked'])) {
		print '<td class="liste_titre'.($cssforfield ? ' '.$cssforfield : '').'">';
		if ($key == 'fundoc1') {
			print '';
		} elseif (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
			print $form->selectarray('search_'.$key, $val['arrayofkeyval'], (isset($search[$key]) ? $search[$key] : ''), $val['notnull'], 0, 0, '', 1, 0, 0, '', 'maxwidth100', 1);
		} elseif ((strpos($val['type'], 'integer:') === 0) || (strpos($val['type'], 'sellist:') === 0)) {
			print $object->showInputField($val, $key, (isset($search[$key]) ? $search[$key] : ''), '', '', 'search_', 'maxwidth125', 1);
		} elseif (preg_match('/^(date|timestamp|datetime)/', $val['type'])) {
			print '<div class="nowrap">';
			print $form->selectDate($search[$key.'_dtstart'] ? $search[$key.'_dtstart'] : '', "search_".$key."_dtstart", 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('From'));
			print '</div>';
			print '<div class="nowrap">';
			print $form->selectDate($search[$key.'_dtend'] ? $search[$key.'_dtend'] : '', "search_".$key."_dtend", 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('to'));
			print '</div>';
		} elseif ($key == 'lang') {
			require_once DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php';
			$formadmin = new FormAdmin($db);
			print $formadmin->select_language($search[$key], 'search_lang', 0, null, 1, 0, 0, 'minwidth150 maxwidth200', 2);
		} else {
			print '<input type="text" class="flat maxwidth75" name="search_'.$key.'" value="'.dol_escape_htmltag(isset($search[$key]) ? $search[$key] : '').'">';
		}
		print '</td>';
	}
}
// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';

// Fields from hook
$parameters = array('arrayfields'=>$arrayfields);
$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters, $object); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
// Action column (Show the massaction button only when this page is not opend from the Extended POS)
if (empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
	print '<td class="liste_titre maxwidthsearch">';
	$searchpicto = $form->showFilterButtons();
	print $searchpicto;
	print '</td>';
}
print '</tr>'."\n";


// Fields title label
// --------------------------------------------------------------------
print '<tr class="liste_titre">';
// Action column (Show the massaction button only when this page is not opend from the Extended POS)
if (!empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', '', $sortfield, $sortorder, 'center maxwidthsearch actioncolumn ');
}
foreach ($object->fields as $key => $val) {
	$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
	if ($key == 'status' || $key == 'status_folder' || $key == 'fundoc1') {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
	} elseif (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'center';
	} elseif (in_array($val['type'], array('timestamp'))) {
		$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
	} elseif (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && $val['label'] != 'TechnicalID' && empty($val['arrayofkeyval'])) {
		$cssforfield .= ($cssforfield ? ' ' : '').'right';
	}
	if (!empty($arrayfields['t.'.$key]['checked'])) {
		print getTitleFieldOfList($arrayfields['t.'.$key]['label'], 0, $_SERVER['PHP_SELF'], 't.'.$key, '', $param, ($cssforfield ? 'class="'.$cssforfield.'"' : ''), $sortfield, $sortorder, ($cssforfield ? $cssforfield.' ' : ''))."\n";
	}
}
// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
// Hook fields
$parameters = array('arrayfields'=>$arrayfields, 'param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder);
$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters, $object); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
// Action column (Show the massaction button only when this page is not opend from the Extended POS)
if (empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
	print getTitleFieldOfList($selectedfields, 0, $_SERVER["PHP_SELF"], '', '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ')."\n";
}
print '</tr>'."\n";


// Detect if we need a fetch on each output line
$needToFetchEachLine = 0;
if (isset($extrafields->attributes[$object->table_element]['computed']) && is_array($extrafields->attributes[$object->table_element]['computed']) && count($extrafields->attributes[$object->table_element]['computed']) > 0) {
	foreach ($extrafields->attributes[$object->table_element]['computed'] as $key => $val) {
		if (preg_match('/\$object/', $val)) {
			$needToFetchEachLine++; // There is at least one compute field that use $object
		}
	}
}


// Loop on record
// --------------------------------------------------------------------
$i = 0;
$totalarray = array();
$totalarray['nbfield'] = 0;
while ($i < ($limit ? min($num, $limit) : $num)) {
	$obj = $db->fetch_object($resql);
	if (empty($obj)) {
		break; // Should not happen
	}

	// Store properties in $object
	$object->setVarsFromFetchObj($obj);

	// Show here line of result
	print '<tr class="oddeven">';
	// Action column (Show the massaction button only when this page is not opend from the Extended POS)
	if (!empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
		print '<td class="nowrap center actioncolumn">';
		if (($massactionbutton || $massaction) && $contextpage != 'poslist') {   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
			$selected = 0;
			if (in_array($obj->rowid, $arrayofselected)) {
				$selected = 1;
			}
			print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		print '</td>';
		if (!$i) {
			$totalarray['nbfield']++;
		}
	}
	foreach ($object->fields as $key => $val) {
		$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
		if (in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
			$cssforfield .= ($cssforfield ? ' ' : '').'center';
		} elseif ($key == 'status' || $key == 'status_folder' || $key == 'fundoc1') {
			$cssforfield .= ($cssforfield ? ' ' : '').'center';
		}

		if (in_array($val['type'], array('timestamp'))) {
			$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
		} elseif ($key == 'ref') {
			$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
		}

		if (in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && !in_array($key, array('rowid', 'status')) && empty($val['arrayofkeyval'])) {
			$cssforfield .= ($cssforfield ? ' ' : '').'right';
		}
		//if (in_array($key, array('fk_soc', 'fk_user', 'fk_warehouse'))) $cssforfield = 'tdoverflowmax100';
		if (!empty($arrayfields['t.'.$key]['checked'])) {
			print '<td'.($cssforfield ? ' class="'.$cssforfield.'"' : '').'>';
			if ($key == 'status') {
				print $object->getLibStatut(5);
			} elseif ($key == 'status_folder') {
				print $object->getLibStatutFolder(5);
			} elseif ($key == 'funcheck') {
				if ($obj->funcheck == 1) {
					print img_picto('', 'check');
				} else {
					print img_picto('', 'uncheck');
				}
			} elseif ($key == 'fundoc1') {
				if (!empty($obj->fundoc1)) {
					print img_picto('', 'pdf');
				} else {
					print img_picto('', 'uncheck');
				}
			} elseif ($key == 'rowid') {
				print $object->showOutputField($val, $key, $object->id, '');
			} elseif ($key == 'ref') {
				print $object->getNomUrl(1, '', 0, '', 1, 3);
				$filename = dol_sanitizeFileName($object->ref);
				$filedir = $conf->funding->multidir_output[$object->entity ? $object->entity : $conf->entity].'/'.dol_sanitizeFileName($object->ref);
				print $formfile->getDocumentsLink('funding', $filename, $filedir);
			} else {
				print $object->showOutputField($val, $key, $object->$key, '');
			}
			print '</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}
			if (!empty($val['isameasure']) && $val['isameasure'] == 1) {
				if (!$i) {
					$totalarray['pos'][$totalarray['nbfield']] = 't.'.$key;
				}
				if (!isset($totalarray['type']['t.'.$key])) {
					$totalarray['type'][$totalarray['nbfield']] = $val['type'];
				}
				if (!isset($totalarray['val'])) {
					$totalarray['val'] = array();
				}
				if (!isset($totalarray['val']['t.'.$key])) {
					$totalarray['val']['t.'.$key] = 0;
				}
				$totalarray['val']['t.'.$key] += $object->$key;
			}
		}
	}
	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
	// Fields from hook
	$parameters = array('arrayfields'=>$arrayfields, 'object'=>$object, 'obj'=>$obj, 'i'=>$i, 'totalarray'=>&$totalarray);
	$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters, $object); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Action column (Show the massaction button only when this page is not opend from the Extended POS)
	if (empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
		print '<td class="nowrap center">';
		if ($massactionbutton || $massaction) {   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
			$selected = 0;
			if (in_array($object->id, $arrayofselected)) $selected = 1;
			print '<input id="cb'.$object->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$object->id.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}
	print '</tr>'."\n";
	$i++;
}

// Show total line
include DOL_DOCUMENT_ROOT.'/core/tpl/list_print_total.tpl.php';

// If no record found
if ($num == 0) {
	$colspan = 1;
	foreach ($arrayfields as $key => $val) { if (!empty($val['checked'])) $colspan++; }
	print '<tr><td colspan="'.$colspan.'" class="opacitymedium">'.$langs->trans("NoRecordFound").'</td></tr>';
}


$db->free($resql);

$parameters = array('arrayfields'=>$arrayfields, 'sql'=>$sql);
$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object); // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print '</table>'."\n";
print '</div>'."\n";

print '</form>'."\n";

if (in_array('builddoc', $arrayofmassactions) && ($nbtotalofrecords === '' || $nbtotalofrecords)) {
	$hidegeneratedfilelistifempty = 1;
	if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) $hidegeneratedfilelistifempty = 0;

	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
	$formfile = new FormFile($db);

	// Show list of available documents
	$urlsource = $_SERVER['PHP_SELF'].'?sortfield='.$sortfield.'&sortorder='.$sortorder;
	$urlsource .= str_replace('&amp;', '&', $param);

	$filedir = $diroutputmassaction;
	$genallowed = $permissiontoread;
	$delallowed = $permissiontoadd;

	print $formfile->showdocuments('massfilesarea_funding', '', $filedir, $urlsource, 0, $delallowed, '', 1, 1, 0, 48, 1, $param, $title, '', '', '', null, $hidegeneratedfilelistifempty);
}

// End of page
llxFooter();
$db->close();
