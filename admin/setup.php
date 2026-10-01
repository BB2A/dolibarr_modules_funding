<?php
/* Copyright (C) 2004-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2020 BERTON Anthony <a.berton@gest-mag.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    funding/admin/setup.php
 * \ingroup funding
 * \brief   Funding setup page.
 */

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
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/funding.lib.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/funding/class/funding.class.php');
dol_include_once('/funding/lib/funding_funding.lib.php');

//require_once "../class/myclass.class.php";

// Translations
$langs->loadLangs(array("admin", "funding@funding"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');
$value = GETPOST('value', 'alpha');

isset($conf->global->FUNDING_ID_REGLEMENT) ? $conf->global->FUNDING_ID_REGLEMENT : $conf->global->FUNDING_ID_REGLEMENT = '';
isset($conf->global->FUNDING_DEFAULT_DURATION) ? $conf->global->FUNDING_DEFAULT_DURATION : $conf->global->FUNDING_DEFAULT_DURATION = '';
isset($conf->global->FUNDING_DEFAULT_SCALE) ? $conf->global->FUNDING_DEFAULT_SCALE : $conf->global->FUNDING_DEFAULT_SCALE = '';
isset($conf->global->FUNDING_DEFAULT_REDEMPTION) ? $conf->global->FUNDING_DEFAULT_REDEMPTION : $conf->global->FUNDING_DEFAULT_REDEMPTION = '';
isset($conf->global->FUNDING_DEFAULT_TYPE) ? $conf->global->FUNDING_DEFAULT_TYPE : $conf->global->FUNDING_DEFAULT_TYPE= '';
isset($conf->global->FUNDING_VALIDITY_MONTH) ? $conf->global->FUNDING_VALIDITY_MONTH : $conf->global->FUNDING_VALIDITY_MONTH= '';


isset($conf->global->FUNDING_FILTRE_ORGANIZATION) ? $conf->global->FUNDING_FILTRE_ORGANIZATION : $conf->global->FUNDING_FILTRE_ORGANIZATION = '';
isset($conf->global->FUNDING_DEFAULT_ORGANIZATION) ? $conf->global->FUNDING_DEFAULT_ORGANIZATION : $conf->global->FUNDING_DEFAULT_ORGANIZATION = '';

isset($conf->global->FUNDING_MAIL_DEFAULT) ? $conf->global->FUNDING_MAIL_DEFAULT : $conf->global->FUNDING_MAIL_DEFAULT = '';
isset($conf->global->FUNDING_MAIL_AUTOCOPY_TO) ? $conf->global->FUNDING_MAIL_AUTOCOPY_TO : $conf->global->FUNDING_MAIL_AUTOCOPY_TO = '';
isset($conf->global->FUNDING_MAIL_VALIDATION) ? $conf->global->FUNDING_MAIL_VALIDATION : $conf->global->FUNDING_MAIL_VALIDATION = '';
isset($conf->global->FUNDING_MAIL_REPORT) ? $conf->global->FUNDING_MAIL_REPORT : $conf->global->FUNDING_MAIL_REPORT = '';


isset($conf->global->FUNDING_NOCLOSEDFINISHAUTO_EXTENSION) ? $conf->global->FUNDING_NOCLOSEDFINISHAUTO_EXTENSION : $conf->global->FUNDING_NOCLOSEDFINISHAUTO_EXTENSION = '';

isset($conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL) ? $conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL: $conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL = '';
isset($conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL_SHORTLIST) ? $conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL_SHORTLIST : $conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL_SHORTLIST = '';

$arrayofparameters = array(

	'FUNDING_ID_REGLEMENT'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_DEFAULT_DURATION'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_DEFAULT_SCALE'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_DEFAULT_REDEMPTION'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_DEFAULT_TYPE'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_VALIDITY_MONTH'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>'number', 'step'=>'1', 'min'=>'0'),

	'FUNDING_FILTRE_ORGANIZATION'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_DEFAULT_ORGANIZATION'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),

	'FUNDING_MAIL_DEFAULT'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_MAIL_AUTOCOPY_TO'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_MAIL_VALIDATION'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_MAIL_REPORT'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),

	'FUNDING_NOCLOSEDFINISHAUTO_EXTENSION'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),

	'FUNDING_LISTE_THIRDPARTY_PROPAL'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_LISTE_THIRDPARTY_PROPAL_SHORTLIST'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),

	'FUNDING_ENABLED_RENTEDIT'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),
	'FUNDING_ENABLE_USER_NOTIFICATIONS'=>array('css'=>'minwidth200','enabled'=>1, 'default'=>'', 'type'=>''),

);

$error = 0;
$setupnotempty = 1;


/*
 * Actions
 */

if ((float) DOL_VERSION >= 6) {
	include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';
}

if ($action == 'updateMask') {
	$maskconstorder = GETPOST('maskconstorder', 'alpha');
	$maskorder = GETPOST('maskorder', 'alpha');

	if ($maskconstorder) {
		$res = dolibarr_set_const($db, $maskconstorder, $maskorder, 'chaine', 0, '', $conf->entity);
	}

	if (!$res > 0) {
		$error++;
	}

	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
} elseif ($action == 'specimen') {
	$modele = GETPOST('module', 'alpha');
	$tmpobjectkey = GETPOST('object');

	$tmpobject = new $tmpobjectkey($db);
	$tmpobject->initAsSpecimen();

	// Search template files
	$file = '';
	$classname = '';
	$filefound = 0;
	$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
	foreach ($dirmodels as $reldir) {
		$file = dol_buildpath($reldir."core/modules/funding/doc/pdf_".$modele."_".strtolower($tmpobjectkey).".modules.php", 0);
		if (file_exists($file)) {
			$filefound = 1;
			$classname = "pdf_".$modele;
			break;
		}
	}

	if ($filefound) {
		require_once $file;

		$module = new $classname($db);

		if ($module->write_file($tmpobject, $langs) > 0) {
			header("Location: ".DOL_URL_ROOT."/document.php?modulepart=".strtolower($tmpobjectkey)."&file=SPECIMEN.pdf");
			return;
		} else {
			setEventMessages($module->error, null, 'errors');
			dol_syslog($module->error, LOG_ERR);
		}
	} else {
		setEventMessages($langs->trans("ErrorModuleNotFound"), null, 'errors');
		dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
	}
} elseif ($action == 'set') { // Activate a model
	$type = 'funding';
	$label = 'Funding';
	$scandir = '';
	$ret = addDocumentModel($value, $type, $label, $scandir);
} elseif ($action == 'del') {
	$tmpobjectkey = GETPOST('object');
	$type = strtolower($tmpobjectkey);

	$ret = delDocumentModel($value, $type);
	if ($ret > 0) {
		$constforval = 'FUNDING_'.strtoupper($tmpobjectkey).'_ADDON_PDF';
		if (getDolGlobalString($constforval) == "$value") {
			dolibarr_del_const($db, $constforval, $conf->entity);
		}
	}
} elseif ($action == 'setdoc') { // Set default model
	$tmpobjectkey = GETPOST('object');
	$type = strtolower($tmpobjectkey);
	$label = ucfirst($tmpobjectkey);
	$scandir = '';
	$constforval = 'FUNDING_'.strtoupper($tmpobjectkey).'_ADDON_PDF';
	if (dolibarr_set_const($db, $constforval, $value, 'chaine', 0, '', $conf->entity)) {
		// The constant that was read before the new set
		// We therefore requires a variable to have a coherent view
		$conf->global->$constforval = $value;
	}

	// On active le modele
	$ret = delDocumentModel($value, $type);
	if ($ret > 0) {
		$ret = addDocumentModel($value, $type, $label, $scandir);
	}
} elseif ($action == 'setmod') {
	// TODO Check if numbering module chosen can be activated
	// by calling method canBeActivated
	$tmpobjectkey = GETPOST('object');
	$constforval = 'FUNDING_'.strtoupper($tmpobjectkey)."_ADDON";
	dolibarr_set_const($db, $constforval, $value, 'chaine', 0, '', $conf->entity);
}



/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formcompany = new FormCompany($db);
$object = new Funding($db);


$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

$page_name = "FundingSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_'.$object->picto.' infobox-contrat valignmiddle widthpictotitle pictotitle');

// Configuration header
$head = fundingAdminPrepareHead();
dol_fiche_head($head, 'settings', '', -1, "funding@funding");

// Setup page goes here
echo '<span class="opacitymedium">'.$langs->trans("FundingSetupPage").'</span><br><br>';


print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="update">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td class="titlefield" style="width:auto">'.$langs->trans("Parameter").'</td><td align="center">'.$langs->trans("Value").'</td></tr>';

foreach ($arrayofparameters as $key => $val) {
	print '<tr class="oddeven"><td>';
	$tooltiphelp = (($langs->trans($key.'Tooltip') != $key.'Tooltip') ? $langs->trans($key.'Tooltip') : '');
	print $form->textwithpicto($langs->trans($key), $tooltiphelp);
	if ($key == 'FUNDING_ID_REGLEMENT') {
		print '</td><td align="right" width="230">';
		$form->select_types_paiements($conf->global->FUNDING_ID_REGLEMENT, 'FUNDING_ID_REGLEMENT', 'CRDT', 0, 1, 1, 0, 1);
		print '</td></tr>';
	} elseif ($key == 'FUNDING_DEFAULT_DURATION') {
		print '<td align="right" width="230">'.$form->selectarray('FUNDING_DEFAULT_DURATION', $object->fields['fk_duration']['arrayofkeyval'], $conf->global->FUNDING_DEFAULT_DURATION);
	} elseif ($key == 'FUNDING_DEFAULT_SCALE') {
		print '<td align="right" width="230">'.$form->selectarray('FUNDING_DEFAULT_SCALE', $object->fields['fk_scale']['arrayofkeyval'], $conf->global->FUNDING_DEFAULT_SCALE);
	} elseif ($key == 'FUNDING_DEFAULT_REDEMPTION') {
		$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
		print '<td align="right" width="230">'.$form->selectarray('FUNDING_DEFAULT_REDEMPTION', $arrval, $conf->global->FUNDING_DEFAULT_REDEMPTION);
	} elseif ($key == 'FUNDING_DEFAULT_TYPE') {
		print '<td align="right" width="230">'.$form->selectarray('FUNDING_DEFAULT_TYPE', $object->fields['fk_funding_type']['arrayofkeyval'], $conf->global->FUNDING_DEFAULT_TYPE);
	} elseif ($key == 'FUNDING_FILTRE_ORGANIZATION') {
		print '</td><td align="right" width="230">'.$form->selectarray("FUNDING_FILTRE_ORGANIZATION", $formcompany->typent_array(0), $conf->global->FUNDING_FILTRE_ORGANIZATION, 1, 0, 0, '', 0, 0, 0, (empty($conf->global->SOCIETE_SORT_ON_TYPEENT) ? 'ASC' : $conf->global->SOCIETE_SORT_ON_TYPEENT), '', 1).'</td></tr>';
	} elseif ($key == 'FUNDING_DEFAULT_ORGANIZATION') {
		print '</td><td align="right" width="230">'.$form->select_company($conf->global->FUNDING_DEFAULT_ORGANIZATION, 'FUNDING_DEFAULT_ORGANIZATION', $filter = '(s.fk_typent:=:'.$conf->global->FUNDING_FILTRE_ORGANIZATION.')', 'SelectThirdParty', '', '', '', '', '', 'maxwidth100').'</td></tr>';
	} elseif ($key == 'FUNDING_NOCLOSEDFINISHAUTO_EXTENSION') {
		print '</td>';
		print '<td align="right" width="230">'.$form->selectarray('FUNDING_NOCLOSEDFINISHAUTO_EXTENSION', $object->fields['fk_funding_type']['arrayofkeyval'], $conf->global->FUNDING_NOCLOSEDFINISHAUTO_EXTENSION);
	} elseif ($key == 'FUNDING_LISTE_THIRDPARTY_PROPAL') {
		print '</td>';
		print '<td align="right" width="230">';
		if ($conf->use_javascript_ajax) {
			print ajax_constantonoff('FUNDING_LISTE_THIRDPARTY_PROPAL');
		} else {
			$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
			print $form->selectarray("FUNDING_LISTE_THIRDPARTY_PROPAL", $arrval, $conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL);
		}
	} elseif ($key == 'FUNDING_LISTE_THIRDPARTY_PROPAL_SHORTLIST') {
		print '</td>';
		print '<td align="right" width="230">';
		if ($conf->use_javascript_ajax) {
			print ajax_constantonoff('FUNDING_LISTE_THIRDPARTY_PROPAL_SHORTLIST');
		} else {
			$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
			print $form->selectarray("FUNDING_LISTE_THIRDPARTY_PROPAL_SHORTLIST", $arrval, $conf->global->FUNDING_LISTE_THIRDPARTY_PROPAL_SHORTLIST);
		}
	} elseif ($key == 'FUNDING_ENABLED_RENTEDIT') {
		print '</td>';
		print '<td align="right" width="230">';
		if ($conf->use_javascript_ajax) {
			print ajax_constantonoff('FUNDING_ENABLED_RENTEDIT');
		} else {
			$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
			print $form->selectarray("FUNDING_ENABLED_RENTEDIT", $arrval, $conf->global->FUNDING_ENABLED_RENTEDIT);
		}
	} elseif ($key == 'FUNDING_ENABLE_USER_NOTIFICATIONS') {
		print '</td>';
		print '<td align="right" width="230">';
		if ($conf->use_javascript_ajax) {
			print ajax_constantonoff('FUNDING_ENABLE_USER_NOTIFICATIONS');
		} else {
			$arrval = array('0' => $langs->trans("No"), '1' => $langs->trans("Yes"));
			print $form->selectarray("FUNDING_ENABLE_USER_NOTIFICATIONS", $arrval, $conf->global->FUNDING_ENABLE_USER_NOTIFICATIONS);
		}
	} elseif ($val["type"] == "number") {
		print '</td><td align="right" width="230"><input type="number" min='.$val["min"].' step='.$val["step"] . ' name="'.$key.'"  class="flat '.(empty($val['css']) ? 'minwidth200' : $val['css']).'" value="'.$conf->global->$key.'"></td></tr>';
	} else {
		print '</td><td align="right" width="230"><input name="'.$key.'"  class="flat '.(empty($val['css']) ? 'minwidth200' : $val['css']).'" value="'.$conf->global->$key.'"></td></tr>';
	}
}
print '</table>';

print '<br><div class="center">';
print '<input class="button" type="submit" value="'.$langs->trans("Save").'">';
print '</div>';

print '</form>';
print '<br>';

$moduledir = 'funding';
$myTmpObjects = array();
$myTmpObjects['Funding']=array('includerefgeneration'=>0, 'includedocgeneration'=>1);


foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
	if ($myTmpObjectKey == 'MyObject') continue;
	if ($myTmpObjectArray['includerefgeneration']) {
		/*
		 * Orders Numbering model
		 */
		$setupnotempty++;

		print load_fiche_titre($langs->trans("NumberingModules", $myTmpObjectKey), '', '');

		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("Name").'</td>';
		print '<td>'.$langs->trans("Description").'</td>';
		print '<td class="nowrap">'.$langs->trans("Example").'</td>';
		print '<td class="center" width="60">'.$langs->trans("Status").'</td>';
		print '<td class="center" width="16">'.$langs->trans("ShortInfo").'</td>';
		print '</tr>'."\n";

		clearstatcache();

		foreach ($dirmodels as $reldir) {
			$dir = dol_buildpath($reldir."core/modules/".$moduledir);

			if (is_dir($dir)) {
				$handle = opendir($dir);
				if (is_resource($handle)) {
					while (($file = readdir($handle)) !== false) {
						if (strpos($file, 'mod_'.strtolower($myTmpObjectKey).'_') === 0 && substr($file, dol_strlen($file) - 3, 3) == 'php') {
							$file = substr($file, 0, dol_strlen($file) - 4);

							require_once $dir.'/'.$file.'.php';

							$module = new $file($db);

							// Show modules according to features level
							if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
							if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

							if ($module->isEnabled()) {
								dol_include_once('/'.$moduledir.'/class/'.strtolower($myTmpObjectKey).'.class.php');

								print '<tr class="oddeven"><td>'.$module->name."</td><td>\n";
								print $module->info();
								print '</td>';

								// Show example of numbering model
								print '<td class="nowrap">';
								$tmp = $module->getExample();
								if (preg_match('/^Error/', $tmp)) print '<div class="error">'.$langs->trans($tmp).'</div>';
								elseif ($tmp == 'NotConfigured') print $langs->trans($tmp);
								else print $tmp;
								print '</td>'."\n";

								print '<td class="center">';
								$constforvar = 'FUNDING_'.strtoupper($myTmpObjectKey).'_ADDON';
								if ($conf->global->$constforvar == $file) {
									print img_picto($langs->trans("Activated"), 'switch_on');
								} else {
									print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&object='.strtolower($myTmpObjectKey).'&value='.$file.'&token='.newToken().'">';
									print img_picto($langs->trans("Disabled"), 'switch_off');
									print '</a>';
								}
								print '</td>';

								$mytmpinstance = new $myTmpObjectKey($db);
								$mytmpinstance->initAsSpecimen();

								// Info
								$htmltooltip = '';
								$htmltooltip .= ''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';

								$nextval = $module->getNextValue($mytmpinstance);
								if ("$nextval" != $langs->trans("NotAvailable")) {  // Keep " on nextval
									$htmltooltip .= ''.$langs->trans("NextValue").': ';
									if ($nextval) {
										if (preg_match('/^Error/', $nextval) || $nextval == 'NotConfigured')
											$nextval = $langs->trans($nextval);
											$htmltooltip .= $nextval.'<br>';
									} else {
										$htmltooltip .= $langs->trans($module->error).'<br>';
									}
								}

								print '<td class="center">';
								print $form->textwithpicto('', $htmltooltip, 1, 0);
								print '</td>';

								print "</tr>\n";
							}
						}
					}
					closedir($handle);
				}
			}
		}
		print "</table><br>\n";
	}

	if ($myTmpObjectArray['includedocgeneration']) {
		/*
		 * Document templates generators
		 */
		$setupnotempty++;
		$type = strtolower($myTmpObjectKey);

		print load_fiche_titre($langs->trans("DocumentModules", $myTmpObjectKey), '', '');

		// Load array def with activated templates
		$def = array();
		$sql = "SELECT nom";
		$sql .= " FROM ".MAIN_DB_PREFIX."document_model";
		$sql .= " WHERE type = '".$type."'";
		$sql .= " AND entity = ".$conf->entity;
		$resql = $db->query($sql);
		if ($resql) {
			$i = 0;
			$num_rows = $db->num_rows($resql);
			while ($i < $num_rows) {
				$array = $db->fetch_array($resql);
				array_push($def, $array[0]);
				$i++;
			}
		} else {
			dol_print_error($db);
		}

		print "<table class=\"noborder\" width=\"100%\">\n";
		print "<tr class=\"liste_titre\">\n";
		print '<td>'.$langs->trans("Name").'</td>';
		print '<td>'.$langs->trans("Description").'</td>';
		print '<td class="center" width="60">'.$langs->trans("Status")."</td>\n";
		print '<td class="center" width="60">'.$langs->trans("Default")."</td>\n";
		print '<td class="center" width="38">'.$langs->trans("ShortInfo").'</td>';
		print '<td class="center" width="38">'.$langs->trans("Preview").'</td>';
		print "</tr>\n";

		clearstatcache();

		foreach ($dirmodels as $reldir) {
			foreach (array('', '/doc') as $valdir) {
				$realpath = $reldir."core/modules/".$moduledir.$valdir;
				$dir = dol_buildpath($realpath);

				if (is_dir($dir)) {
					$handle = opendir($dir);
					if (is_resource($handle)) {
						while (($file = readdir($handle)) !== false) {
							$filelist[] = $file;
						}
						closedir($handle);
						arsort($filelist);
						foreach ($filelist as $file) {
							if (preg_match('/\.modules\.php$/i', $file) && preg_match('/^(pdf_|doc_)/', $file) && preg_match('/'.strtolower($myTmpObjectKey).'/i', $file)) {
								if (file_exists($dir.'/'.$file)) {
									$name = substr($file, 4, dol_strlen($file) - 16);
									$classname = substr($file, 0, dol_strlen($file) - 12);

									require_once $dir.'/'.$file;
									$module = new $classname($db);

									$modulequalified = 1;
									if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2) $modulequalified = 0;
									if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) $modulequalified = 0;

									if ($modulequalified) {
										print '<tr class="oddeven"><td width="100">';
										print (empty($module->name) ? $name : $module->name);
										print "</td><td>\n";
										if (method_exists($module, 'info')) print $module->info($langs);
										else print $module->description;
										print '</td>';

										// Active
										if (in_array($name, $def)) {
											print '<td class="center">'."\n";
											print '<a href="'.$_SERVER["PHP_SELF"].'?action=del&object='.strtolower($myTmpObjectKey).'&value='.$name.'&token='.newToken().'">';
											print img_picto($langs->trans("Enabled"), 'switch_on');
											print '</a>';
											print '</td>';
										} else {
											print '<td class="center">'."\n";
											print '<a href="'.$_SERVER["PHP_SELF"].'?action=set&object='.strtolower($myTmpObjectKey).'&value='.$name.'&amp;scan_dir='.$module->scandir.'&amp;label='.urlencode($module->name).'&token='.newToken().'">'.img_picto($langs->trans("Disabled"), 'switch_off').'</a>';
											print "</td>";
										}

										// Default
										print '<td class="center">';
										$constforvar = 'FUNDING_'.strtoupper($myTmpObjectKey).'_ADDON_PDF';
										if (getDolGlobalString($constforvar) == $name) {
											print img_picto($langs->trans("Default"), 'on');
										} else {
											print '<a href="'.$_SERVER["PHP_SELF"].'?action=setdoc&object='.strtolower($myTmpObjectKey).'&value='.$name.'&amp;scan_dir='.$module->scandir.'&amp;label='.urlencode($module->name).'&token='.newToken().'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"), 'off').'</a>';
										}
										print '</td>';

										// Info
										$htmltooltip = ''.$langs->trans("Name").': '.$module->name;
										$htmltooltip .= '<br>'.$langs->trans("Type").': '.($module->type ? $module->type : $langs->trans("Unknown"));
										if ($module->type == 'pdf') {
											$htmltooltip .= '<br>'.$langs->trans("Width").'/'.$langs->trans("Height").': '.$module->page_largeur.'/'.$module->page_hauteur;
										}
										$htmltooltip .= '<br>'.$langs->trans("Path").': '.preg_replace('/^\//', '', $realpath).'/'.$file;

										$htmltooltip .= '<br><br><u>'.$langs->trans("FeaturesSupported").':</u>';
										$htmltooltip .= '<br>'.$langs->trans("Logo").': '.yn($module->option_logo, 1, 1);
										$htmltooltip .= '<br>'.$langs->trans("MultiLanguage").': '.yn($module->option_multilang, 1, 1);

										print '<td class="center">';
										print $form->textwithpicto('', $htmltooltip, 1, 0);
										print '</td>';

										// Preview
										print '<td class="center">';
										if ($module->type == 'pdf') {
											print '<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&module='.$name.'&object='.$myTmpObjectKey.'">'.img_object($langs->trans("Preview"), 'generic').'</a>';
										} else {
											print img_object($langs->trans("PreviewNotAvailable"), 'generic');
										}
										print '</td>';

										print "</tr>\n";
									}
								}
							}
						}
					}
				}
			}
		}

		print '</table>';
	}
}

// if (empty($setupnotempty)) {
// 	print '<br>'.$langs->trans("NothingToSetup");
// }

// Page end
dol_fiche_end();

llxFooter();
$db->close();
