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
 *   	\file       funding_card.php
 *		\ingroup    funding
 *		\brief      Page to create/edit/view funding
 */

// Do not create database handler $db
//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB','1');
// Do not load object $user
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER','1');
// Do not load object $mysoc
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC','1');
// Do not load object $langs
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN','1');
// Do not check injection attack on GET parameters
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION','1');
// Do not check injection attack on POST parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION','1');
// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK','1');
// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL','1');
// Do not check style html tag into posted data
// if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK','1');
// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU','1');
// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML','1');
// Do not load ajax.lib.php library
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX','1');
// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined("NOLOGIN"))                  define("NOLOGIN",'1');
// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined('NOIPCHECK'))                define('NOIPCHECK','1');
// Force lang to a particular value
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT','auto');
// Force authentication handler
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE','aloginmodule');
// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN',1);
// Disable all Content Security Policies
//if (! defined("FORCECSP"))                 define('FORCECSP','none');


// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';

// Code copied from funding_document.php
// Attached files
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
dol_include_once('/funding/class/funding.class.php');
dol_include_once('/funding/lib/funding_funding.lib.php');

// for other modules
require_once DOL_DOCUMENT_ROOT.'/core/lib/propal.lib.php';
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/order.lib.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';

// Load translation files required by the page
$langs->loadLangs(array("funding@funding", "Propal", "Orders", "other"));

// Get parameters
$id                     = GETPOST('id', 'int');
$ref                    = GETPOST('ref', 'alpha');
$action                 = GETPOST('action', 'aZ09');
$confirm                = GETPOST('confirm', 'alpha');
$cancel                 = GETPOST('cancel', 'aZ09');
// To manage different context of search
$contextpage            = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'fundingcard';
$backtopage             = GETPOST('backtopage', 'alpha');
$backtopageforcancel    = GETPOST('backtopageforcancel', 'alpha');
$backtopagelist         = GETPOST('backtopagelist', 'alpha');

//$lineid   = GETPOST('lineid', 'int');

$typedoc                = GETPOST('typedoc', 'alpha');
$iddoc                  = GETPOST('iddoc', 'int');
$crea                   = GETPOST('crea', 'int');

// Initialize technical objects
$object = new Funding($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->funding->dir_output.'/temp/massgeneration/'.$user->id;
// Note that conf->hooks_modules contains array
$hookmanager->initHooks(array('fundingcard', 'globalcard'));
$notrigger = 0;

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
$search = array();
foreach ($object->fields as $key => $val) {
	if (GETPOST('search_'.$key, 'alpha')) {
		$search[$key] = GETPOST('search_'.$key, 'alpha');
	}
}

 // Display the card in the proposal or order
if ($typedoc && $iddoc && empty($crea)) {
	$sql = 'SELECT t.rowid, t.origin, t.origin_id';
	$sql .= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
	if ($object->ismultientitymanaged == 1) {
		$sql .= " WHERE t.entity IN (".getEntity($object->element).")";
	} else {
		$sql .= " WHERE 1 = 1";
	}
	// Filter document origin
	$sql.= " AND t.origin = '".$typedoc."' AND t.origin_id = ".$iddoc;
	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		if ($num > 0) {
			$obj = $db->fetch_object($resql);
			$id = $obj->rowid;
		} else {
			$action = 'create';
		}
	} else {
		dol_print_error($db);
	}
}

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.

// Set date_end field to always editable if status is less than STATUS_END
if ($object->status < $object::STATUS_END) {
	$object->fields['date_end']['alwayseditable'] = 1;
}

// Fetch organization (error?)
if (!empty($object->fk_org)) {
	$org = $object->fetchSoc($object->fk_org);
}
if (!empty($object->fk_soc_invoice)) {
	$soc_invoice =  $object->fetchSoc($object->fk_soc_invoice);
}

$permissiontoread = $user->hasRight('funding', 'read');
// Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontoadd = $user->hasRight('funding', 'write');
$permissiontodelete = $user->hasRight('funding', 'delete') || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
// Used by the include of actions_dellink.inc.php
$permissiondellink = $user->hasRight('funding', 'write');
// Used by the function send_mail_org
$permissionmanage = $user->hasRight('funding', 'manage');
// Used by the include of actions_setnotes.inc.php
$permissionnote = $user->hasRight('funding', 'write');

$upload_dir = $conf->funding->multidir_output[isset($object->entity) ? $object->entity : 1];

// Security check - Protection if external user
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}
if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->statut == $object::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'funding', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);
if (!isModEnabled("funding")) {
	accessforbidden();
}
if (!$permissiontoread) accessforbidden();

/*
 * Actions
 */

$parameters = array();
// Note that $action and $object may have been modified by some hooks
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	// Preserve the HTML generated by CKEditor for private notes.
	if ($action == 'setnote_private' && !empty($permissionnote) && !GETPOST('cancel', 'alpha') && empty($user->socid)) {
		$notePrivate = isset($_POST['note_private']) ? (string) $_POST['note_private'] : '';
		$result = $object->update_note(dol_html_entity_decode($notePrivate, ENT_QUOTES | ENT_HTML5, 'UTF-8', 1), '_private');
		if ($result < 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
		$action = '';
	}

	// Must be include, not include_once
	include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php';

	if ($action == 'confirm_delete' && !empty($iddoc)) {
		$backurlforlist = dol_buildpath('/funding/funding_card.php', 1).'?id=&typedoc='.$typedoc.'&iddoc='.$iddoc;
	} else {
		$backurlforlist = dol_buildpath('/funding/funding_list.php', 1);
	}

	if (!empty($typedoc) && !empty($iddoc) && $typedoc == 'propal') {
		$backurl = dol_buildpath('/comm/propal/card.php?id='.$iddoc, 1);
	} elseif (!empty($typedoc) && !empty($iddoc) && $typedoc == 'order') {
		$backurl = dol_buildpath('/commande/card.php?id='.$iddoc, 1);
	} else {
		$backurl = dol_buildpath('/funding/funding_list.php', 1);
	}

	//$backtopage = $backurl;
	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/funding/funding_card.php', 1).'?id='.($id > 0 ? $id : '__ID__').'&typedoc='.$typedoc.'&iddoc='.$iddoc;
			}
		}
	}

	// Name of trigger action code to execute when we modify record
	$triggermodname = 'FUNDING_MODIFY';

	// Set study number
	if ($action == 'setstudy_number' && $permissiontoadd) {
		$result = $object->setStudyNumber($user, GETPOST('study_number'));
		if ($result < 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	// Set folder number
	if ($action == 'setfolder_number' && $permissiontoadd) {
		$result = $object->setFolderNumber($user, GETPOST('folder_number'));
		if ($result < 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	// Set acceptance date
	if ($action == 'setdate_accepted' && $permissiontoadd) {
		$result = $object->setDateAccepted($user, GETPOST('date_accepted'));
		if ($result < 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	if ($action == 'sendorg' && $permissiontoadd) {
		$result = $object->setStatusFolder($user, $object::STATUS_FOLDER_SENDORG);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	// Force update coefficient and retention
	if ($action == 'updateforce' && $permissiontoadd) {
		$result = $object->update($user);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	// Search documents in other funding records for the third party
	if ($action == 'searchdoc' && $permissiontoadd) {
		$result = $object->searchDoc($user, $upload_dir);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		} else {
			setEventMessages($object->msg, $object->msgs);
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	if ($action == 'clean' && $permissiontoadd) {
		$object->amount_maint = '';
		$object->amount_rent_edit = '';
		$result = $object->update($user);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	if ($action == 'lack' && $permissionmanage) {
		$result = $object->setStatusFolder($user, $object::STATUS_FOLDER_LACK);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	if ($action == 'extension' && $permissionmanage) {
		$result = $object->setStatusFolder($user, $object::STATUS_FOLDER_EXTENSION);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	if ($action == 'run' && $permissiontoadd) {
		$result = $object->setRun($user);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	if ($action == 'reopen' && $permissiontoadd) {
		$result = $object->setStatusCommon($user, $object::STATUS_ACCEPT, $notrigger, 'FUNDING_REOPEN');
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	if ($action == 'set_thirdparty' && $permissiontoadd) {
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, 'FUNDING_MODIFY');
	}
	if ($action == 'classin' && $permissiontoadd) {
		$object->setProject(GETPOST('projectid', 'int'));
	}
	if ($action == 'setAcceptedRefused' && $permissiontoadd && !GETPOST('cancel', 'alpha')) {
		if (!(GETPOST('statut', 'int') > 0)) {
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("CloseAs")), null, 'errors');
			$action = 'statut';
		} else {
			// prevent browser refresh from closing funding several times
			if ($object->status >= $object::STATUS_VALIDATED) {
				$db->begin();
				$result = $object->setAcceptedRefused($user, GETPOST('statut', 'int'), GETPOST('study_number', 'alpha'), GETPOST('folder_number', 'alpha'), GETPOST('date_accepted'), GETPOST('retention', 'alpha'));
				if ($result <= 0) {
					setEventMessages($object->error, $object->errors, 'errors');
					$error++;
				}
				if (!$error) {
					$db->commit();
				} else {
					$db->rollback();
				}
			}
		}
	}
	if ($action == 'confirm_setdraft' && $permissionmanage) {
		$object->setStatusFolder($user, 'NULL');
	}
	if ($action == 'setCloseFinich' && $permissiontoadd) {
		// prevent browser refresh from closing funding several times
		if ($object->status == $object::STATUS_RUNNING) {
			$db->begin();
			$result = $object->setEnd($user, GETPOST('statutfolder', 'int'), GETPOST('description', 'restricthtml'), $notrigger);
			if ($result < 0) {
				setEventMessages($object->error, $object->errors, 'errors');
				$error++;
			}
			if (!$error) {
				$db->commit();
			} else {
				$db->rollback();
			}
		}
	}

	// Documents
	if ($id > 0 || !empty($ref)) {
		$upload_dir = $conf->funding->multidir_output[$object->entity ? $object->entity : $conf->entity]."/".dol_sanitizeFileName($object->ref);
	}
	// Disabled because adding an attachment sent an email
	// $_POST['addfile'] = '';

	$documenturl = DOL_URL_ROOT.'/document.php';
	if (isset($conf->global->DOL_URL_ROOT_DOCUMENT_PHP)) {
		$documenturl = $conf->global->DOL_URL_ROOT_DOCUMENT_PHP;
	}
	$modulepart = 'funding';

	if ($action == 'savedoc' && $permissiontoadd || $action == 'deletefile' && $permissiontoadd) {
		// Sent document
		$doc = GETPOST('doc');
		// File to delete
		$file = GETPOST('file');
		// Whether the required file is present
		$filecheck = GETPOST('filecheck');
		$fileupload = '';
		$cherchfile = '';

		if ($action == 'savedoc') {
			$fileupload = $_FILES['userfile']['name'];

			if (is_countable($_FILES['userfile']['name']) && empty($fileupload[0])) {
				// No file, so deletion or check only
				$cherchfile  = 0;
			} elseif (empty($fileupload)) {
				// No file, so deletion or check only
				$cherchfile = 0;
			} else {
				// File present, so save it
				$cherchfile = 1;
			}
		}

		if ($action == 'deletefile' || $cherchfile == 1) {
			include_once DOL_DOCUMENT_ROOT.'/core/actions_linkedfiles.inc.php';
		}

		$result = $object->sendDocumentFunding($fileupload, $cherchfile, $upload_dir, $action);
		if ($result <= 0) {
			setEventMessages($object->error, $object->errors, 'errors');
		} else {
			setEventMessages($object->message, $object->messages);
		}
		// Old code saved in a cloud file
	}

	// Load object after actions to have most recent data into $object
	// Must be include, not include_once.
	include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	//include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	// Send mail marker
	// Actions to send emails
	$triggersendname = 'FUNDING_SENTBYMAIL';
	$autocopy = 'FUNDING_MAIL_AUTOCOPY_TO';
	$trackid = 'funding'.$object->id;

	//$sendtosocid = $object->fk_org;
	//$parameters = array('notifcode'=>$notifcode, 'sendto'=>$sendto, 'replyto'=>$replyto, 'file'=>$filename_list, 'mimefile'=>$mimetype_list, 'filename'=>$mimefilename_list);

	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}

/*
 * View
 *
 * Put here all code to build page
 */
$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("Funding");
if ($object->id > 0) {
	$title .= " ".$object->ref;
}
$help_url = 'EN:Module_Funding_En|FR:Module_Funding_Fr';
llxHeader('', $title, $help_url, '', 0, 0, '', '', '', 'mod-funding page-card');

 // Example: adding jQuery code
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

// Check whether we are in a document or funding card
if (!empty($typedoc) && !empty($iddoc) || !empty($object->origin) && !empty($object->origin_id)) {
	// Retrieve proposal data
	if ($typedoc == 'propal' || $object->origin == 'propal') {
		$prop = new Propal($db);
		$result = $prop->fetch(empty($iddoc)?$object->origin_id:$iddoc);

		// Check whether the proposal is linked to an order and display a warning. #20240510
		// TODO: check whether this proposal has an invoice
		$prop->fetchObjectLinked(null, '', null, 'order', 'OR', 0, 'sourcetype', 'commande');
		$orderlinkeds = $prop->linkedObjects;
		if (!empty($orderlinkeds)) {
			if (count($orderlinkeds) == 1) {
				foreach ($orderlinkeds['commande'] as $arrayorderlinkeds => $orderlink) {
					// $objorderlinked = new Commande($db);
					// $result = $objorderlinked->fetch($orderlink->id);
					$orderreginfo = '';
					if ($orderlink->mode_reglement_id <> $conf->global->FUNDING_ID_REGLEMENT) {
						$orderreginfo = '('.$langs->trans("OrderRegIsNotFunding").')';
					}
					$orderfundurl = dol_buildpath('/funding/funding_card.php?typedoc=order&iddoc='.$orderlink->id, 1);
					Print info_admin($langs->trans("OrderExistForPropal"). ' <a href='. $orderfundurl.'>'.$langs->trans("LinkFundingOrder").'</a> '.$orderreginfo, 0, 0, 'error', 'clearboth');
				}
			} else {
				Print info_admin($langs->trans("OrdersExistForPropal"), 0, 0, 'error', 'clearboth');
			}
		}
	}
	// Retrieve order data
	if ($typedoc == 'order' || $object->origin == 'order') {
		$ord = new Commande($db);
		$result = $ord->fetch(empty($iddoc)?$object->origin_id:$iddoc);
	}
	// Enable custom rent
	if ((!empty($typedoc) && $typedoc != 'propal') || (!empty($object->origin) && $object->origin != 'propal')) {
		if (!empty($conf->global->FUNDING_ENABLED_RENTEDIT)) {
			// Hide field already shown in banner
			unset($object->fields['amount_rent_edit']);
		}
	}
}

// Check whether we are in a document to display the correct header
if ($typedoc == 'propal') {
	// Display the proposal frame
	$prop->fetch_thirdparty();

	$head = propal_prepare_head($prop);
	dol_fiche_head($head, 'Funding', $langs->trans("Proposal"), -1, 'propal');
} elseif ($typedoc == 'order') {
	// Display the order frame
	$head = commande_prepare_head($ord);
	dol_fiche_head($head, 'Funding', $langs->trans("CustomerOrder"), -1, 'order');
}

if ($object->id > 0 && $permissiontoread && (empty($action) || ($action != 'create'))) {
	if (empty($typedoc) && empty($iddoc)) {
		print load_fiche_titre($langs->trans("Funding"), '', 'object_'.$object->picto);
		$head = fundingPrepareHead($object);
		print dol_get_fiche_head($head, 'card', $langs->trans("Funding"), -1, $object->picto  .' infobox-contrat valignmiddle widthpictotitle pictotitle', 0, '', '', 0, '', 1);
	}
	$morehtmlref = '';
	$morehtmlleft = '';
	$morehtmlright = '';
	$morehtmlstatus = '';

	if (!empty($typedoc) && $typedoc == 'propal') {
		$linkback = '<a href="'.dol_buildpath('/comm/propal/list.php?restore_lastsearch_values=1&mainmenu=commercial&leftmenu=propals', 1).'">'.$langs->trans("BackToList").'</a>';
	} elseif (!empty($typedoc) && $typedoc == 'order') {
		$linkback = '<a href="'.dol_buildpath('/commande/list.php?restore_lastsearch_values=1&mainmenu=commercial&leftmenu=orders', 1).'">'.$langs->trans("BackToList").'</a>';
	} else {
		$linkback = '<a href="'.dol_buildpath('/funding/funding_list.php?restore_lastsearch_values=1&mainmenu=funding&leftmenu=', 1).'">'.$langs->trans("BackToList").'</a>';
	}

	$morehtmlref = '<div class="refidno">';
	// Numbers
	$morehtmlref .= $form->editfieldkey("StudyNumber", 'study_number', $object->study_number, $object, $permissiontoadd, 'string', '', 0, 1);
	$morehtmlref .= $form->editfieldval("StudyNumber", 'study_number', $object->study_number, $object, $permissiontoadd, 'string', '', null, null, '', 1);
	$morehtmlref .= '<br/>'.$form->editfieldkey("FolderNumber", 'folder_number', $object->folder_number, $object, $permissiontoadd, 'string', '', 0, 1);
	$morehtmlref .= $form->editfieldval("FolderNumber", 'folder_number', $object->folder_number, $object, $permissiontoadd, 'string', '', null, null, '', 1);
	$morehtmlref .= '<br/>'.$form->editfieldkey("DateAccepted", 'date_accepted', $object->date_accepted, $object, $permissiontoadd, 'datetime', '', 0, 1);
	$morehtmlref .= $form->editfieldval("DateAccepted", 'date_accepted', $object->date_accepted, $object, $permissiontoadd, 'datetime', '', null, null, '', 1);
	// $morehtmlref .= '<br/>'.$langs->trans('DateAccepted') . ' : ' . dol_print_date($object->date_accepted)	;
	!empty($object->date_accepted && $action != "editdate_accepted") ? $morehtmlref .= " - " . $langs->trans('DateValidity') . ' : ' . dol_print_date($object->date_endvalidity, "dayhour") :'';

	// Thirdparty
	$morehtmlref .= '<br/>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');

	$morehtmlref .= '</div>';
	if ($object->origin == 'propal') {
		$tabBartitle = $langs->trans('fundingpropal').' ';
	} elseif ($object->origin == 'order') {
		$tabBartitle = $langs->trans('Funding').' ';
	}
	$checked = ($object->funcheck)?img_picto('', 'check', 'class="pictofixedwidth"'):img_picto('', 'uncheck', 'class="pictofixedwidth"');
	$morehtmlstatus .= '<h3>'.$checked.$tabBartitle.'</h3>';

	if (!empty($object->status_folder)) {
		$morehtmlstatus .= '<div>'.$object->getLibStatutFolder(4).'</div><br/>';
	}
	$morehtmlref .= '';

	if ($permissiontoadd || $permissionmanage) {
		dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, '', '', $morehtmlleft, $morehtmlstatus, '', $morehtmlright);
	} else {
		dol_banner_tab($object, 'ref', $linkback, 0, 'ref', 'ref', $morehtmlref, '', '', $morehtmlleft, $morehtmlstatus, '', $morehtmlright);
	}
}

// Part to create
if ($action == 'create' && $permissiontoadd) {
	if (empty($typedoc) && empty($iddoc)) {
		print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Funding")), '', 'object_'.$object->picto.' infobox-contrat valignmiddle widthpictotitle pictotitle');
	}
	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?crea=1&typedoc='.$typedoc.'&iddoc='.$iddoc.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	dol_fiche_head(array(), '');

	// Set some default values
	if (! GETPOSTISSET('fk_duration')) $_POST['fk_duration'] = isset($conf->global->FUNDING_DEFAULT_DURATION) ? $conf->global->FUNDING_DEFAULT_DURATION : '';
	if (! GETPOSTISSET('fk_scale')) $_POST['fk_scale'] = isset($conf->global->FUNDING_DEFAULT_SCALE) ? $conf->global->FUNDING_DEFAULT_SCALE : '';
	if (! GETPOSTISSET('fk_funding_type')) $_POST['fk_funding_type'] = isset($conf->global->FUNDING_DEFAULT_TYPE) ? $conf->global->FUNDING_DEFAULT_TYPE : '';
	if (! GETPOSTISSET('redemption')) $_POST['redemption'] = isset($conf->global->FUNDING_DEFAULT_REDEMPTION) ? $conf->global->FUNDING_DEFAULT_REDEMPTION : '';
	if (! GETPOSTISSET('fk_org')) $_POST['fk_org'] = isset($conf->global->FUNDING_DEFAULT_ORGANIZATION) ? $conf->global->FUNDING_DEFAULT_ORGANIZATION : '';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";
	print '</div>';
	dol_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	if (empty($iddoc)) {
		// Cancel for create does not post form if we don't know the backtopage
		print '<input type="'.($backtopage ? "submit" : "button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage ? '' : ' onclick="javascript:history.go(-1)"').'>';
	}
	print '</div>';

	print '</form>';

	//dol_set_focus('input[name="ref"]');
} elseif (empty($object->id)) {
	print load_fiche_titre($langs->trans('Funding'), '', 'object_'.$object->picto.' infobox-contrat valignmiddle widthpictotitle pictotitle');
	print '<h2 align="center">'.$langs->trans("NoFunding").'</h2>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit' && $permissiontoadd) {
	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?typedoc='.$typedoc.'&iddoc='.$iddoc.'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	dol_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';
	print '</form>';
}

// Part to show record
if ($object->id > 0 && $permissiontoread && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();
	$formconfirm = '';
	$lineid = '';

	// Confirmation of action refresh supprimer?
	if ($action == 'refresh' && $permissiontoadd) {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&typedoc='.$typedoc.'&iddoc='.$iddoc, $langs->trans('RefreshFunding'), $langs->trans('ConfirmRefreshFunding'), 'confirm_refresh', '', 0, 1);
	}

	// Select accepted or refused status
	if ($action == 'AcceptedRefused') {
		// Form (signed or unsigned)
		$formquestion = array(
			array('type' => 'select', 'name' => 'statut', 'label' => '<span class="fieldrequired">'.$langs->trans("CloseAs").'</span>', 'values' => array($object::STATUS_ACCEPT=>$object->LibStatut($object::STATUS_ACCEPT, 1), $object::STATUS_DENIED=>$object->LibStatut($object::STATUS_DENIED, 1))),
			array('type' => 'text', 'name' => 'study_number', 'label' => $langs->trans("StudyNumber"), 'value' => $object->study_number),
			array('type' => 'text', 'name' => 'folder_number', 'label' => $langs->trans("FolderNumber"), 'value' => $object->folder_number),
			array('type' => 'date', 'name' => 'date_accepted', 'label' => $langs->trans("DateAccepted"), 'value' => $object->date_accepted, 'datenow' => 1),
			array('type' => 'checkbox', 'name' => 'retention', 'label' => $langs->trans("QuestionRetention"), 'value' => $object->retention)
		);

		// Notification for status change
		if (!empty($conf->notification->enabled)) {
			require_once DOL_DOCUMENT_ROOT.'/core/class/notify.class.php';
			$notify = new Notify($db);
			$formquestion = array_merge($formquestion, array(
				array('type' => 'onecolumn', 'value' => $notify->confirmMessage('FUNDING_ACCEPT_DENIED', $object->fk_soc, $object)),
			));
		}
		$text = '';
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&typedoc='.$typedoc.'&iddoc='.$iddoc, $langs->trans('SetAcceptedRefused'), $text, 'setAcceptedRefused', $formquestion, '', 1, 350);
	}

	// Select closing status
	if ($action == 'closefinich') {
		// Form (signed or unsigned)
		$formquestion = array(
			array('type' => 'select', 'name' => 'statutfolder', 'label' => '<span class="fieldrequired">'.$langs->trans("CloseAs").'</span>', 'values' => array($object::STATUS_FOLDER_REDEEMED=>$object->LibStatutFolder($object::STATUS_FOLDER_REDEEMED, 1), $object::STATUS_FOLDER_DENOUNCED=>$object->LibStatutFolder($object::STATUS_FOLDER_DENOUNCED, 1), $object::STATUS_FOLDER_CLOSED_TRANSFER=>$object->LibStatutFolder($object::STATUS_FOLDER_CLOSED_TRANSFER, 1), $object::STATUS_FOLDER_CLOSED_LESSOR=>$object->LibStatutFolder($object::STATUS_FOLDER_CLOSED_LESSOR, 1))),
			array('type' => 'text', 'name' => 'description', 'label' => $langs->trans("Note"), 'value' => '')
		);
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&typedoc='.$typedoc.'&iddoc='.$iddoc, $langs->trans('closefinich'), $text, 'setCloseFinich', $formquestion, '', 1, 250);
	}

	// Confirmation to delete
	if ($action == 'delete_object') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&typedoc='.$typedoc.'&iddoc='.$iddoc, $langs->trans('DeleteFunding'), $langs->trans('ConfirmDeleteFunding'), 'confirm_delete', '', 0, 1);
	}

	// Confirmation to delete line
	/*
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	*/

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	// Note that $action and $object may have been modified by hook
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action);
	if (empty($reshook)) {
		$formconfirm .= $hookmanager->resPrint;
	} elseif ($reshook > 0) {
		$formconfirm = $hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent  tableforfield">'."\n";

	// Common attributes
	// We change column just before this field
	//$keyforbreak='fieldkeytoswitchonsecondcolumn';
	// Hide field already shown in banner
	//unset($object->fields['fk_project']);
	// Hide field already shown in banner
	//unset($object->fields['fk_soc']);
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';
	$param = '';
	$file = '';
	print '</table>';
	print '<div class="div-table-responsive-no-min">';
	// Documents for funding
	print '<table class="centpercent noborder">';
	print '<tr class="liste_titre">';
	print '<td colspan="2">'.$langs->trans("DocumentsForFunding").'</td>';
	print '<td align="center">'.$langs->trans("Lack").'</td>';
	print '<td></td>';
	print '</tr>';

	$i = 1;
	while ($i <= 6) {
		print '<tr class="">';
		print '<td>'.$form->editfieldkey('fundoc'.$i, 'fundoc'.$i, '', $object, 0).'</td>';
		if ($permissiontoadd && empty($object->{'fundoc'.$i})) {
			print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&typedoc='.$typedoc.'&iddoc='.$iddoc.'" method="post" name="formdoc">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="savedoc">';
			print '<input type="hidden" name="doc" value="fundoc'.$i.'">';
			print '<td><input type="file" accept=".pdf,.jpg,.png" class="flat"  name="userfile[]" multiple id="fundoc'.$i.'input"></td>';
			print '<td align="center"><input type="checkbox" '.((!$permissionmanage || $object->status > $object::STATUS_ACCEPT) ? 'disabled' : '').' class="flat" name="filecheck" id="fundoc'.$i.'check" '.($object->{'fundoc'.$i.'check'} ? 'value="fundoc'.$i.'checkchecked" checked' : 'value="fundoc'.$i.'check"').'></td>';
			print '<td align="center"><button  type="submit" class="butAction" name="sendit" value="'.$langs->trans("Save").'">'.img_picto($langs->trans("Save"), 'save', 'class=""').'</button></td>';
			print '</form>';
		} else {
			$relativepath = $object->ref.'/'.$object->{'fundoc'.$i};
			($object->{'fundoc'.$i})? print '<td><a href="'.$documenturl.'?modulepart='.$modulepart.'&amp;file='.urlencode($relativepath).(!empty($param) ? '&'.$param : '').'">'.$object->{'fundoc'.$i}.'</a>'.$formfile->showPreview($file, $modulepart, $relativepath, 0, $param):print'<td></td>';
			print '<td></td>';
			($object->{'fundoc'.$i} && $permissiontoadd)? print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&token='.newToken().'&typedoc='.$typedoc.'&iddoc='.$iddoc.'&action=deletefile&doc=fundoc'.$i.'&file='.$object->{'fundoc'.$i}.'">'.img_picto($langs->trans("Delete"), 'delete', 'class=""').'</a></td>' : print '<td></td>';
		}
		print '</tr>';
		$i++;
	}
	print '</table">';

	// Funding documents

	//print '<table class="noborder tableforfield centpercent margintable">';
	print '<table class="centpercent noborder">';
	print '<tr class="liste_titre">';
	print '<td td colspan="2">'.$langs->trans("FundingFolder").'</td>';
	print '<td></td>';
	print '<td></td>';
	print '</tr>';

	$i = 1;
	while ($i <= 6) {
		print '<tr class="">';
		print '<td>'.$form->editfieldkey('funfoldoc'.$i, 'funfoldoc'.$i, '', $object, 0).'</td>';
		if ($permissiontoadd && empty($object->{'funfoldoc'.$i})) {
			print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&typedoc='.$typedoc.'&iddoc='.$iddoc.'" method="post" name="formdoc">';
			print '<input type="hidden" name="token" value="'.newToken().'">';
			print '<input type="hidden" name="action" value="savedoc">';
			print '<input type="hidden" name="doc" value="funfoldoc'.$i.'">';
			print '<td><input type="file" accept=".pdf,.jpg,.png" class="flat"  name="userfile[]" multiple id="funfoldoc'.$i.'input"></td>';
			print '<td></td>';
			print '<td align="center"><button  type="submit" class="butAction"  name="sendit" value="'.$langs->trans("Save").'">'.img_picto($langs->trans("Save"), 'save', 'class=""').'</button></td>';
			print '</form>';
		} else {
			$relativepath = $object->ref.'/'.$object->{'funfoldoc'.$i};
			($object->{'funfoldoc'.$i})? print '<td><a href="'.$documenturl.'?modulepart='.$modulepart.'&amp;file='.urlencode($relativepath).(!empty($param) ? '&'.$param : '').'">'.$object->{'funfoldoc'.$i}.'</a>'.$formfile->showPreview($file, $modulepart, $relativepath, 0, $param):print'<td></td>';
			print '<td></td>';
			($object->{'funfoldoc'.$i} && $permissiontoadd)? print '<td align="center"><a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&token='.newToken().'&typedoc='.$typedoc.'&iddoc='.$iddoc.'&action=deletefile&doc=funfoldoc'.$i.'&file='.$object->{'funfoldoc'.$i}.'">'.img_picto($langs->trans("Delete"), 'trash', 'class=""').'</a></td>':print'<td></td>';
		}
		print '</tr>';
		$i++;
	}
	print '</table>';
	print '</div>';
	print '<div class="center">';
	print '</div>';
	print '</form>';
	print '</div>';
	print '</div>';


	// Show notes
	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';


	$cssclass = "titlefield";
	$dirtpls = array_merge($conf->modules_parts['tpl'], array('/core/tpl'));
	foreach ($dirtpls as $reldir) {
		$res = @include dol_buildpath($reldir.'/notes.tpl.php');
		if ($res) {
			break;
		}
	}
	print '</div>';

	print '<div class="clearboth"></div>';

	dol_fiche_end();

	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		// Note that $action and $object may have been modified by hook
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (empty($reshook) && empty($user->socid)) {
			// Force update maj coef and Retention
			if ($permissiontoadd && $object->status < $object::STATUS_ACCEPT) {
				print dolGetButtonAction('', $langs->trans('UpdateForce'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=updateforce&typedoc='.$typedoc.'&iddoc'.$iddoc.'&token='.newToken());
			}
			// Search doc
			if ($permissiontoadd && empty($object->fundoc1)) {
				print dolGetButtonAction('', $langs->trans('searchdocinotherfunding'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=searchdoc&typedoc='.$typedoc.'&iddoc'.$iddoc.'&token='.newToken());
			}
			// Clean
			if ($permissiontoadd && $object->status >= $object::STATUS_VALIDATED && $object->status < $object::STATUS_ACCEPT) {
				print dolGetButtonAction('', $langs->trans('Clean'), 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=clean&typedoc='.$typedoc.'&iddoc'.$iddoc.'&token='.newToken());
			}
			// Folder status
			if ($permissionmanage && $object->status >= $object::STATUS_VALIDATED && $object->status < $object::STATUS_ACCEPT) {
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=sendorg&token='.newToken().'">'.$langs->trans('BtnSendorg').'</a>'."\n";
			} elseif ($permissiontoadd && $object->status == $object::STATUS_RUNNING && $object->origin <> 'propal' && $object->status_folder != $object::STATUS_FOLDER_EXTENSION) {
				if (empty($object->status_folder)) {
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=extension&typedoc='.$typedoc.'&iddoc'.$iddoc.'&token='.newToken().'">'.$langs->trans('BtnExtension').'</a>'."\n";
				}
			}

			// Set status accepted/refused
			if ($object->status < $object::STATUS_ACCEPT && $object->status >= $object::STATUS_VALIDATED && $permissiontoadd) {
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=AcceptedRefused&typedoc='.$typedoc.'&iddoc'.$iddoc.'&token='.newToken().''.(empty($conf->global->MAIN_JUMP_TAG) ? '' : '#close').'&typedoc='.$typedoc.'&iddoc'.$iddoc.'">'.$langs->trans('SetAcceptedRefused').'</a>';
			}

			// Send
			if ($permissiontoadd && $object->status >= $object::STATUS_VALIDATED) {
				$sendto = $conf->global->FUNDING_MAIL_DEFAULT;
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&typedoc='.$typedoc.'&iddoc'.$iddoc.'&sendto='.$sendto.'&action=presend&mode=init#formmailbeforetitle&token='.newToken().'">'.$langs->trans('SendMail').'</a>'."\n";
			}

			// closefinich
			if ($permissionmanage && $object->status == $object::STATUS_RUNNING) {
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=closefinich&token='.newToken().'&typedoc='.$typedoc.'&iddoc='.$iddoc.'">'.$langs->trans('closefinich').'</a>'."\n";
			}

			// Modify
			if ($permissiontoadd && $object->status < $object::STATUS_RUNNING) {
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken().'&typedoc='.$typedoc.'&iddoc='.$iddoc.'">'.$langs->trans("Modify").'</a>'."\n";
			} else {
				print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
			}

			//Back to draft
			if ($permissiontoadd && $object->status >= $object::STATUS_VALIDATED) {
				print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_setdraft&token='.newToken().'&confirm=yes&typedoc='.$typedoc.'&iddoc='.$iddoc.'">'.$langs->trans("SetToDraft").'</a>';
			}

			// Validate
			if ($permissiontoadd && $object->status == $object::STATUS_DRAFT) {
				if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0)) {
					print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&token='.newToken().'&confirm=yes&typedoc='.$typedoc.'&iddoc='.$iddoc.'">'.$langs->trans("Validate").'</a>';
				} else {
					$langs->load("errors");
					print '<a class="butActionRefused" href="" title="'.$langs->trans("ErrorAddAtLeastOneLineFirst").'">'.$langs->trans("Validate").'</a>';
				}
			}

			// Runing funding
			if ($permissiontoadd && $object->origin <> 'propal') {
				if ($object->status == $object::STATUS_ACCEPT) {
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=run&token='.newToken().'&typedoc='.$typedoc.'&iddoc='.$iddoc.'">'.$langs->trans("BtnRunning").'</a>'."\n";
				} elseif ($object->status >= $object::STATUS_RUNNING) {
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=reopen&token='.newToken().'&typedoc='.$typedoc.'&iddoc='.$iddoc.'">'.$langs->trans("ReOpen").'</a>'."\n";
				}
			}

			// Delete (need delete permission, or if draft, just need create/modify permission)
			if ($permissiontodelete || ($object->status == $object::STATUS_DRAFT && $permissiontoadd)) {
				print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete_object&token='.newToken().'&backtopage='.$backtopage.'">'.$langs->trans('Delete').'</a>'."\n";
			} else {
				print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
			}
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Display events, documents and linked objects

	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		// Anchor for document generation
		print '<a name="builddoc"></a>';

		$includedocgeneration = 1;

		// Documents
		if ($includedocgeneration) {
			// $upload_dir = $conf->funding->multidir_output[isset($object->entity) ? $object->entity : 1];
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref . '/' . $objref . '.pdf';
			$filedir = $conf->funding->multidir_output[isset($object->entity) ? $object->entity : 1].'/'. $objref.'/other';
			$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
			// If you can read, you can build the PDF to read content
			$genallowed = $user->hasRight('funding', 'read');
			// If you can create/edit, you can remove a file on card
			$delallowed = 0;
			print $formfile->showdocuments('funding:funding', $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('funding_funding'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

		print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		$MAXEVENT = 10;

		$morehtmlright .= '<a href="'.dol_buildpath('/funding/funding_agenda.php', 1).'?id='.$object->id.'">';
		$morehtmlright .= $langs->trans("SeeAll");
		$morehtmlright .= '</a>';

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element.'@'.$object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlright);

		print '</div></div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	// Presend form
	$modelmail = 'funding_send';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->funding->multidir_output[$object->entity ? $object->entity : $conf->entity];

	$trackid = 'funding'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
