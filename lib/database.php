<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: functional MySQL database wrapper (provides functions that have the prefix DB_*)
 */

$DBERR = '';

function getTableName($table)
{
  checkTableName($table);
  return(mysql_real_escape_string($table));
}

function checkTableName(&$table)
{
	$prefix = cfg('db/prefix');
  $l = strlen($prefix);
  if (substr($table, 0, $l) != $prefix)
    $table = $prefix.$table;
  return($table);
}

function DB_Safe($raw)
{
  return(mysql_real_escape_string($raw));
}

function DB_StripPrefix($tableName)
{
  $preFix = substr($tableName, 0, strlen(cfg('db/prefix')));
  if ( $preFix == cfg('db/prefix') ) 
    $tableName = substr($tableName, strlen(cfg('db/prefix')));
  return($tableName); 
}

// create a comma-separated list of keys in $ds
function MakeNamesList(&$ds)
{
  $result = '';
  if (sizeof($ds) > 0)
    foreach ($ds as $k => $v)
    {
      if ($k!='')
        $result = $result.','.$k;
    }
  return substr($result, 1);
}

// make a name-value list for UPDATE-queries
function MakeValuesList(&$ds)
{
  $result = '';
  if (sizeof($ds) > 0)
    foreach ($ds as $k => $v)
    {
      if ($k!='')
        $result = $result.',"'.DB_Safe($v).'"';
    }
  return substr($result,1);
}

function DB_UpdateField($tableName, $rowId, $fieldName, $value)
{
	if(is_array($value)) $value = $value[$fieldName];
	$keys = DB_GetKeys($tableName);
	DB_Update('UPDATE '.getTableName($tableName).' SET `'.$fieldName.'` = "'.DB_Safe($value).'" WHERE `'.$keys[0].'` = '.($rowId+0));
}

// gets a list of keys for the table
function DB_GetKeys($tablename)
{
  checkTableName($tablename);
  if (cfg('db/keys/'.$tablename)) return(cfg('db/keys/'.$tablename));
  if ($GLOBALS['db_link'] != null)
  {
    if (!isset($GLOBALS['dbkeytmp'][$tablename]))
    {
      $pk = Array();
      $sql = 'SHOW KEYS FROM `'.$tablename.'`';
      $res = mysql_query($sql, $GLOBALS['db_link']) or $DBERR = (mysql_error().'{ '.$sql.' }');
      if (trim($DBERR)!='') logError('error_sql', $DBERR);
      
			while ($row = @mysql_fetch_assoc($res))
      {
        if ($row['Key_name']=='PRIMARY')
          array_push($pk, $row['Column_name']);
      }
      $GLOBALS['dbkeytmp'][$tablename] = $pk;
      profile_point('DB_GetKeys('.$tablename.')');
    }
    else
    {
      $pk = $GLOBALS['dbkeytmp'][$tablename];
    }
  }
  return $pk;
}

// updates/creates the $dataset in the $tablename
function DB_UpdateDataset($tablename, &$dataset, $options = array())
{
  checkTableName($tablename);
  $keynames = DB_GetKeys($tablename);
  $keyname = $keynames[0]; 
		 
  $query='REPLACE INTO '.$tablename.' ('.MakeNamesList($dataset).
      ') VALUES('.MakeValuesList($dataset).');';
  
  mysql_query($query, $GLOBALS['db_link']) or $DBERR = (mysql_error().'{ '.$query.' }');
  if (trim($DBERR)!='') logError('error_sql', $DBERR);
  $dataset[$keyname] = getDefault($dataset[$keyname], mysql_insert_id($GLOBALS['db_link']));
  
  profile_point('DB_UpdateDataset('.$tablename.', '.DB_UpdateDataset.')');
  return $dataset[$keyname];
}

// get all the tables in the current database
function DB_GetTables()
{
  $result = mysql_list_tables(cfg('db/database'), $GLOBALS['db_link']);
  $tableList = array();
  while ($row = mysql_fetch_row($result))
      $tableList[$row[0]] = $row[0];
  sort($tableList);
  return($tableList);
}

function DB_GetDatasetMatch($table, $matchOptions, $fillIfEmpty = true, $noMatchOptions = array())
{
  $where = array('1');
  if (!is_array($matchOptions))
    $matchOptions = stringParamsToArray($matchOptions);
  foreach($matchOptions as $k => $v)
    $where[] = '('.$k.'="'.DB_Safe($v).'")';
  foreach($noMatchOptions as $k => $v)
    $where[] = '('.$k.'!="'.DB_Safe($v).'")';
  $iwhere = implode(' AND ', $where);
	$query = 'SELECT * FROM '.getTableName($table).
    ' WHERE '.$iwhere;
  $resultDS = DB_GetDatasetWQuery($query);
  if ($fillIfEmpty && sizeof($resultDS) == 0)
    foreach($matchOptions as $k => $v)
      $resultDS[$k] = $v;
	profile_point('DB_GetDatasetMatch('.$table.', '.$iwhere.')');
  return($resultDS);
}

// from table $tablename, get dataset with key $keyvalue
function DB_GetDataSet($tablename, $keyvalue, $keyname = null, $options = array())
{
  $fields = @$options['fields'];
  $fields = getDefault($fields, '*'); 
  if (!$GLOBALS['db_link']) return(array());

  checkTableName($tablename);
  if ($keyname == null)
  {
    $keynames = DB_GetKeys($tablename);
    $keyname = $keynames[0];
  }

  $query = 'SELECT '.$fields.' FROM '.$tablename.' '.$options['join'].' WHERE '.$keyname.'="'.DB_Safe($keyvalue).'";';
  $rs = mysql_query($query, $GLOBALS['db_link']) or $DBERR = mysql_error($GLOBALS['db_link']).' { Query: "'.$query.'" }';
  if ($DBERR != '') logError('error_sql', $DBERR);

  if ($line = @mysql_fetch_array($rs, MYSQL_ASSOC))
  {
    mysql_free_result($rs);
    return($line);    
  }
  else
    $result = array();

	profile_point('DB_GetDataSet('.$tablename.', '.$keyvalue.')');
  return $result;
}

function DB_RemoveDataset($tablename, $keyvalue, $keyname = null)
{
  checkTableName($tablename);
  if ($keyname == null)
  {
    $keynames = DB_GetKeys($tablename);
    $keyname = $keynames[0];
  }

  $rs = mysql_query('DELETE FROM '.$tablename.' WHERE '.$keyname.'="'.
  DB_Safe($keyvalue).'";', $GLOBALS['db_link'])
    or $DBERR = mysql_error($GLOBALS['db_link']).'{ '.$query.' }';
  if (trim($DBERR)!='') logError('error_sql', $DBERR);
}

function DB_ParseQueryParams($query, $parameters = null)
{
  if ($parameters != null)
  {
    $pctr = 0;
    $result = '';
    for($a = 0; $a < strlen($query); $a++)
    {
      $c = substr($query, $a, 1);
      if ($c == '?')
      {
        $result .= '"'.DB_Safe($parameters[$pctr]).'"';
        $pctr++;
      }
      else
        $result .= $c;
    }
  }
  else
    $result = $query;
    
  return($result);
}

// retrieve dataset identified by SQL $query
function DB_GetDataSetWQuery($query, $parameters = null)
{
  $query = DB_ParseQueryParams($query, $parameters);

  $rs = mysql_query($query, $GLOBALS['db_link'])
    or $DBERR = mysql_error($GLOBALS['db_link']).'{ '.$query.' }';

  if (trim($DBERR)!='') logError('error_sql', $DBERR);
	
	if ($line = mysql_fetch_array($rs, MYSQL_ASSOC))
  {
    $result = $line;
    mysql_free_result($rs);
  }
  else
  $result = array();
	profile_point('DB_GetDataSetWQuery('.$query.')');
  return $result;
}

// execute a simple update $query
function DB_Update($query, $parameters = null)
{
  $query = trim($query);
  $query = DB_ParseQueryParams($query, $parameters);
  if (substr($query, -1, 1) == ';')
    $query = substr($query, 0, -1);
  $rs = mysql_query($query)
    or $DBERR = mysql_error().'{ '.$query.' }';
	profile_point('DB_Update('.$query.')');
  if (trim($DBERR)!='') logError('error_sql', $DBERR);
}

// get a list of datasets matching the $query
function DB_GetList($query, $parameters = null, $opt = array())
{
  $result = array();
  $error = '';

  $query = DB_ParseQueryParams($query, $parameters);

  $lines = mysql_query($query, $GLOBALS['db_link']) or $error = mysql_error($GLOBALS['db_link']).'{ '.$query.' }';

  if (trim($error) != '')
  {
    $DBERR = $error;
    logError('error_sql', $DBERR);
  }
  else
  {
    while ($line = mysql_fetch_array($lines, MYSQL_ASSOC))
    {
      if (isset($keyByField))
        $result[$line[$keyByField]] = $line;
      else
        $result[] = $line;
    }
    mysql_free_result($lines);
  }
	profile_point('DB_GetList('.substr($query, 0, 40).'...)');
  return $result;
}

// ***************************************************************************
// include settings from database
// ***************************************************************************

profile_point('DB_Init: code');
ob_start();
$GLOBALS['db_link'] = mysql_pconnect(cfg('db/host'), cfg('db/user'), cfg('db/password')) or
  $DBERR = 'The database connection to server '.cfg('db/user').'@'.cfg('db/host').' could not be established (code: '.@mysql_error($GLOBALS['db_link']).')';

mysql_select_db(cfg('db/database'), $GLOBALS['db_link']) or
  $DBERR = 'The database connection to database '.cfg('db/database').' on '.cfg('db/user').'@'.cfg('db/host').' could not be established. (code: '.@mysql_error($GLOBALS['db_link']).')';
ob_get_clean();

if ($DBERR != '')
{
  $startupErrors = 'Seems like something went wrong with the Hubbub database :-(<br/>'.$DBERR;
  h2_errorhandler(0, $startupErrors, __FILE__);	
	die();
}
else
{
  mysql_query("SET NAMES 'utf8'", $GLOBALS['db_link']);
}

profile_point('DB_Init: mysql_connect/select_db');

?>