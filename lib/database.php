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

function DB_KVList($datasets, $table, $vfield)
{
  $result = array();
  $keys = DB_GetKeys($table);
  foreach ($datasets as $item)
  {
    $result[$item[$keys[0]]] = $item[$vfield];
  }
  return($result);
}

function checkTableName(&$table)
{
	$prefix = cfg('db.prefix');
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
  $preFix = substr($tableName, 0, strlen(cfg('db.prefix')));
  if ( $preFix == cfg('db.prefix') ) 
    $tableName = substr($tableName, strlen(cfg('db.prefix')));
  return($tableName); 
}

function getSQLDateTime($unixTimeStamp)
{
  return(date('Y-m-d H:i:s', $unixTimeStamp));
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
        $result = $result.',"'.mysql_real_escape_string($v, $GLOBALS['db_link']).'"';
    }
  return substr($result,1);
}

function DB_ListItems($tablename, $where = '1', $params = array())
{
  if (!is_array($params)) 
    $params = stringParamsToArray($params);
    
  if (is_array($where))
  {
    $twhere = array();
    foreach ($where as $k => $v)
      $twhere[] = $k.'="'.DB_Safe($v).'"';
    $where = implode(' AND ', $twhere);
    if (trim($where)=='') $where = '1';
  }

  if ($params['limit'] != 0)
    $limitStatement = 'LIMIT '.getDefault($params['offset'], 0).','.$params['limit'];
  else
    $limitStatement = '';

  $result = DB_GetList('SELECT * FROM '.getTableName($tablename).
                       ' WHERE '.$where.
                       ' '.getDefault($params['order']).
                       ' '.$limitStatement);
  
	profile_point('DB_ListItems('.$tablename.')');
	
  return($result);
}

function DB_UpdateField($tableName, $rowId, $fieldName, $value)
{
	if(is_array($value)) $value = $value[$fieldName];
	$keys = DB_GetKeys($tableName);
	DB_Update('UPDATE '.getTableName($tableName).' SET `'.$fieldName.'` = "'.mysql_real_escape_string($value).'" WHERE `'.$keys[0].'` = '.($rowId+0));
}

// retrieves a list of fields and their metadata for the give table
function DB_ListFields($tablename)
{
  //DB_ProfileCall('LIST FIELDS '.$tablename);
  checkTableName($tablename);
  $fieldDesc = array();
  
  $metaFields = array();#DB_GetFieldMetaInfo($tablename);
     
  $result = mysql_query("SELECT * FROM ".$tablename." LIMIT 1");
  $fields = @mysql_num_fields($result);
  $table = @mysql_field_table($result, 0);
  for ($i=0; $i < $fields; $i++)
  {
    $fieldName = mysql_field_name($result, $i);
    if (!$clearXdata || ($fieldName!='_xdata' && substr($fieldName, 0, 1)!='_'))
    {
      @$fullInfo = array(
            'type' => mysql_field_type($result, $i),
            'name' => $fieldName,
            'length' => mysql_field_len($result, $i),
            'flags' => explode(' ', mysql_field_flags($result, $i)),
            'caption' => getDefault($metaFields[$fieldName.'.caption'], $fieldName),
            'subtype' => $metaFields[$fieldName.'.subtype'],
            'ref' => getDefault($metaFields[$fieldName.'.ref']),
            );
      foreach($fullInfo['flags'] as $flag) $fullInfo['flags'][$flag] = true;
      if ($withKeys)
        $fieldDesc[$fieldName] = $fullInfo; 
      else if ($simple)
        $fieldDesc[$fieldName] = $fieldName;
      else
        $fieldDesc[$fieldName] = $fullInfo;
    }
  }
  @mysql_free_result($result);
	
	profile_point('DB_ListFields('.$tablename.')');
  return($fieldDesc);
}

// gets a list of keys for the table
function DB_GetKeys($tablename)
{
  checkTableName($tablename);
  if (cfg('db.keys.'.$tablename)) return(cfg('db.keys.'.$tablename));
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

function DB_PackDataset($table, &$rawds)
{
  $fields = DB_ListFields($table);
  $ext = array();
  foreach($rawds as $k => $v)
  {
    if (!isset($extFieldname)) 
    {
      $f = $k; 
      $extFieldname = CutSegment('_', $f).'_extended';
      if (!isset($fields[$extFieldname])) return;
    }
    if (!isset($fields[$k]))
    {
      $ext[$k] = $v;
      unset($rawds[$k]);
    }
  }
  $rawds[$extFieldname] = serialize($ext);
}

function DB_UnpackDataset($table, &$rawds)
{
  #$fields = DB_ListFields($table);
  foreach($rawds as $k => $v)
  {
    if (!isset($extFieldname)) 
    {
      $extFieldname = CutSegment('_', $k).'_extended';
      if (!isset($rawds[$extFieldname])) return;
    }
  }
  $ext = unserialize($rawds[$extFieldname]);
  if (is_array($ext)) foreach($ext as $k => $v)
  {
    $rawds[$k] = getDefault($rawds[$k], $v); 
  }
}

function DB_SplitDataset(&$ds, $fieldPrefix, $result = array())
{
  foreach($ds as $k => $v)
  {
    if (substr($k, 0, strlen($fieldPrefix)) == $fieldPrefix)
      $result[$k] = $v;
  }
  return($result);
}

/*$list = DB_Select('mytable', where(), order(), limit());

#function where()

function DB_Select($table, $params)
{
	
}*/


// updates/creates the $dataset in the $tablename
function DB_UpdateDataset($tablename, &$dataset, $keyvalue = null, $keyname = null, $options = array())
{
  checkTableName($tablename);
  $keynames = DB_GetKeys($tablename);
  if ($keyname == null)
    $keyname = $keynames[0]; 
		
	if($options['prefix'] != '')
	{
		$nds = array();
		$pfx = $options['prefix'];
		$pfxl = strlen($options['prefix']);
		foreach($dataset as $k => $v)
		  if(substr($k, 0, $pfxl) == $pfx) $nds[$k] = $v;
	}
	else  
	  $nds = $dataset;
  
  if (cfg('db.packextended'))
    DB_PackDataset($tablename, $nds);

  $pureData = $nds;
  if ($keyvalue != null)
    $pureData[$keyname] = $keyvalue;

  $query='REPLACE INTO '.$tablename.' ('.MakeNamesList($pureData).
      ') VALUES('.MakeValuesList($pureData).');';
  
  mysql_query($query, $GLOBALS['db_link']) or $DBERR = (mysql_error().'{ '.$query.' }');
  if (trim($DBERR)!='') logError('error_sql', $DBERR);
  $dataset[$keyname] = getDefault($dataset[$keyname], mysql_insert_id($GLOBALS['db_link']));
  $pureData[$keyname] = $dataset[$keyname];
  
  if(cfg('db.searchtable'))
    DB_UpdateSearchIndex($tablename, $pureData[$keyname], $pureData);
    
  profile_point('DB_UpdateDataset('.$tablename.', '.DB_UpdateDataset.')');
  return $pureData[$keyname];
}

// get all the tables in the current database
function DB_GetTables()
{
  $result = mysql_list_tables(cfg('db.database'), $GLOBALS['db_link']);
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
    $where[] = '('.$k.'="'.mysql_real_escape_string($v).'")';
  foreach($noMatchOptions as $k => $v)
    $where[] = '('.$k.'!="'.mysql_real_escape_string($v).'")';
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
  if (@$options['nocache'] == true && cfg('db.usecache')) 
  {
    unset($GLOBALS['dbtmp'][$tablename][$keyname.'.'.$keyvalue]); 
  }
  if (!isset($GLOBALS['dbtmp'][$tablename][$keyname.'.'.$keyvalue]))
  {
    $result = array();
    if ($keyvalue != '' && $keyvalue != '0')
    {
      //DB_ProfileCall('GET DATASET '.$tablename.' '.$keyvalue);
      checkTableName($tablename);
      if ($keyname == null)
      {
        $keynames = DB_GetKeys($tablename);
        $keyname = $keynames[0];
      }

      $query = 'SELECT '.$fields.' FROM '.$tablename.' '.$options['join'].' WHERE '.$keyname.'="'.
        mysql_escape_string($keyvalue).'";';
      $rs = mysql_query($query, $GLOBALS['db_link'])
        or $DBERR = mysql_error($GLOBALS['db_link']).' { Query: "'.$query.'" }';
      
      if ($DBERR != '') logError('error_sql', $DBERR);

      if ($line = @mysql_fetch_array($rs, MYSQL_ASSOC))
      {
        $result = $line;
        mysql_free_result($rs);
        if (cfg('db.packextended'))
          DB_UnpackDataset($tablename, $result);
      }
      else
        $result = array();
    }
    if (cfg('cfg.usecache'))
      $GLOBALS['dbtmp'][$tablename][$keyname.'.'.$keyvalue] = $result;
  }
  else
  {
    $result = $GLOBALS['dbtmp'][$tablename][$keyname.'.'.$keyvalue];
  }
	profile_point('DB_GetDataSet('.$tablename.', '.$keyvalue.')');
  return $result;
}

function DB_RemoveDataset($tablename, $keyvalue, $keyname = null)
{
  //DB_ProfileCall('REMOVE DATASET '.$tablename.' '.$keyvalue);
  checkTableName($tablename);
  if ($keyname == null)
  {
    $keynames = DB_GetKeys($tablename);
    $keyname = $keynames[0];
  }

  $rs = mysql_query('DELETE FROM '.$tablename.' WHERE '.$keyname.'="'.
  mysql_real_escape_string($keyvalue, $GLOBALS['db_link']).'";', $GLOBALS['db_link'])
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
        $result .= '"'.mysql_real_escape_string($parameters[$pctr]).'"';
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

profile_point('DB_Init(parse)');
ob_start();
$GLOBALS['db_link'] = @mysql_pconnect(cfg('db.host'), cfg('db.user'), cfg('db.password')) or
  $DBERR = 'The database connection to server '.cfg('db.user').'@'.cfg('db.host').' could not be established (code: '.@mysql_error($GLOBALS['db_link']).')';

@mysql_select_db(cfg('db.database'), $GLOBALS['db_link']) or
  $DBERR = 'The database connection to database '.cfg('db.database').' on '.cfg('db.user').'@'.cfg('db.host').' could not be established. (code: '.@mysql_error($GLOBALS['db_link']).')';

profile_point('DB_Init(mysql_connect)');
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

?>
