<?php

require 'lib.php';
require 'help.php';

global $demo_version;
global $smarty;
global $tokenid;
global $tokenname;

$db_id = 0;
if (isset($_GET['db_id']))
{
    $db_id = intval($_GET['db_id']);
}
if ($db_id == 0)
{
  header("location: db_list.php?$tokenname=$tokenid");
  exit;
}

$db  = get_database($db_id);
$error = "";
$msg = "";

if (isset($_POST['submit']))
{
    #data posted, db need to be updated
    $db['create_perm'] = intval(trim($_POST['create_perm']));
    $db['drop_perm']   = intval(trim($_POST['drop_perm']));
    $db['alter_perm']  = intval(trim($_POST['alter_perm']));
    $db['info_perm']   = intval(trim($_POST['info_perm']));
    $db['block_q_perm']= intval(trim($_POST['block_q_perm']));
    $db['proxyid']     = intval(trim($_POST['proxyid']));
    $block_mode        = intval(trim($_POST['block_mode']));
    $db['db_name']     = trim(htmlspecialchars($_POST['db_name'])); 
    $db['dbpid']       = $db_id;
    $db['perms']       = 0;
    
    if ($_POST['proxyid'] != 0 && !($proxy = get_proxy($db['proxyid'])))
    {
        $error .= "Wrong proxy id. Proxy was not found in the database.<br/>\n";
    }

    if ($db['create_perm'] != 0 && $db['create_perm'] != 1)
    {
        $error .= "Create table permission is invalid.<br/>\n";
    } else if ($db['create_perm'] == 1) {
        $db['perms'] = $db['perms'] | 1;
    }

    if ($db['drop_perm'] != 0 && $db['drop_perm'] != 1)
    {
        $error .= "Drop permission is invalid.<br/>\n";
    } else if ($db['drop_perm'] == 1) {
        $db['perms'] = $db['perms'] | 2;
    }

    if ($db['alter_perm'] != 0 && $db['alter_perm']  != 1)
    {
        $error = "Change table structure permission is invalid.<br/>\n";
    } else if ($db['alter_perm'] == 1) {
        $db['perms'] = $db['perms'] | 4;
    }

    if ($db['info_perm'] != 0 && $db['info_perm'] != 1)
    {
        $error .= "Disclose table structure permission is invalid.<br/>\n";
    } else if ($db['info_perm'] == 1) {
        $db['perms'] = $db['perms'] | 8;
    }

    if ($db['block_q_perm'] != 0 && $db['block_q_perm'] != 1)
    {
        $error .= "Block sensitive queries permission is invalid.<br/>\n";
    } else if ($db['block_q_perm'] == 1) {
        $db['perms'] = $db['perms'] | 16;
    }

    if ($block_mode > 13 || $block_mode < 0)
    {
        $error .= "Block Status value is invalid.<br/>\n";
    }

    if (strlen($db['db_name']) > 20)
    {
        $error .= "Database name is too long.<br/>\n";
    }

    if (strlen($db['db_name']) == 0)
    {
       $error .= "Database name can not be empty.<br/>\n";
    } else if (!ereg("^[a-zA-Z0-9_\ -]+$",$db['db_name']))
    {
        $error .= "Database Name is invalid. It contains illegal characters. Valid characters are a-z, A-Z, 0-9 and '_'.<br/>\n";
    }

    if ($block_status != $db['status'] && $demo_version)
    {
       $error .= "Blocking status can not be changed in demo version.<br/>\n";
    } else {
       $db['status'] = $block_mode;
    }
    # default database - do not change it's status
    if ($db['proxyid'] == 0 && $db['status'] >= 10)
    {
       $db['status'] = 0;  
    }

    if (!$error)
    {
        $error = update_database($db);
	if (!$error)
	{
	    $msg = "Database has been successfully updated.";
	}
    }
    if ($error)
        $msg = "<font color='red'>$error</font>";

    $smarty->assign("msg", $msg);
}

$dbs = get_databases_list();

$smarty->assign("databases", $dbs);

$smarty->assign("Name","Edit database - ".$db['db_name']);
$smarty->assign("Page","db_edit.tpl");
$smarty->assign("PrimaryMenu", get_primary_menu());
$smarty->assign("SecondaryMenu", get_top_db_menu());

$smarty->assign("DB_Name", $db['db_name']);
$smarty->assign("DB_ProxyName", $db['proxyname']);
$smarty->assign("DB_ProxyID", $db['proxyid']);
$smarty->assign("DB_Listener", $db['listener']);
$smarty->assign("DB_Backend", $db['backend']);
$smarty->assign("DB_Type", $db['dbtype']);
$smarty->assign("DB_SysDBType", $db['sysdbtype']);
$smarty->assign("DB_ID", $db_id);
if ($db_id)
{
  $smarty->assign("DB_Menu", get_local_db_menu($db['db_name'], $db_id) );
}
$enabled_str = "<font color=\"red\">enabled</font>";

$smarty->assign("DB_StatusId", $db['status']);
if ($db['status'] == 1)
{
    $smarty->assign("DB_Status", "<font color=\"green\">Listener is 'OK'</font>");
}

$smarty->assign("DB_Alter", $db['alter_perm']);
$smarty->assign("DB_Create", $db['create_perm']);
$smarty->assign("DB_Drop", $db['drop_perm']);
$smarty->assign("DB_Info", $db['info_perm']);
$smarty->assign("DB_BlockQ", $db['block_q_perm']);

#load list of proxies
$proxies = get_proxies();
$ids = array();
$names = array();
$db_proxies = array();

foreach ($proxies as $proxy)
{
    $db_proxies[] = array('id'=> $proxy['proxyid'], 'name' => $proxy['proxyname']);
}
$smarty->assign("proxies", $db_proxies);

$modes = get_db_modes();
$ids = array_keys($modes);
$db_modes = array();
foreach ($ids as $id)
{
  if ($id < 10 || ($id >= 10 && $db['sysdbtype'] == 'user_db'))
    $db_modes[] = array('id'=> $id, 'name' => $modes[$id]['mode']);
}

$smarty->assign("block_modes", $db_modes);
if ($db['status'])
  $smarty->assign("block_mode", $db['status']);
else
  $smarty->assign("block_mode", 0);


$help_msg = get_section_help("db_edit");
if ($help_msg)
{
  $smarty->assign("HelpPage","help.tpl");
  $smarty->assign("HelpMsg",$help_msg);
}

$smarty->display('index.tpl');
?>
