<h2>Code Tests</h2>
<?

foreach(json_decode(
  '["if()","chr()","substr()","md5()","strtolower()","array()","foreach()","count()","implode()","return()","sender()","unset()","hubbubserver()","time()","gmdate()","json_decode()","gzinflate()",'.
  '"hubbubconnection()","switch()","case()","hubbubmessage()","rand()","microtime()","ob_start()","require()","trim()","ob_get_clean()","print()","header()","hubbubuser()","isset()","is_object()",'.
  '"explode()","strlen()","is_array()","debug_backtrace()","file_exists()","sizeof()","require_once()","is_callable()","setcookie()","unserialize()","session_destroy()","serialize()","file()","ob_clean()",'.
  '"ob_end_flush()","die()","action()","include()","include_once()","json_encode()","gzdeflate()","round()","hubbubentity()","htmlspecialchars()","http_build_query()","curl_init()","curl_setopt_array()",'.
  '"curl_exec()","curl_close()","str_repeat()","mkdir()","urlencode()","getelementbyid()","focus()","text()","val()","function()","accordion()","error_reporting()","set_error_handler()","strpos()",'.
  '"ini_set()","date_default_timezone_set()","session_name()","session_start()","get_magic_quotes_gpc()","list()","each()","stripslashes()","stristr()","eregi()","dfunction()","templateinitfunction()",'.
  '"str_replace()","mysql_real_escape_string()","date()","mysql_query()","mysql_num_fields()","mysql_field_table()","mysql_field_name()","mysql_field_type()","mysql_field_len()","mysql_field_flags()",'.
  '"mysql_free_result()","mysql_error()","mysql_fetch_assoc()","array_push()","order()","limit()","values()","mysql_insert_id()","mysql_list_tables()","mysql_fetch_row()","sort()","mysql_escape_string()",'.
  '"mysql_fetch_array()","for()","mysql_pconnect()","mysql_select_db()","number_format()","stripos()","array_search()","is_dir()","opendir()","while()","readdir()","closedir()","unlink()","fopen()","fwrite()",'.
  '"fclose()","chmod()","session_id()","parse_str()","debug_print_backtrace()","mail()","func_get_args()","print_r()","curl_setopt()","gmmktime()","floor()","sscanf()","mktime()","preg_replace()","strip_tags()",'.
  '"is_null()","preg_replace_callback()","create_function()","array_reverse()","addslashes()","html()","tabs()","authurl()","epitwitter()","getauthenticateurl()","scripturi()","settoken()","getaccesstoken()",'.
  '"get_accountverify_credentials()","getattributes()","ksort()","init()","subscribe()","reload()","file_get_contents()","validate()","attr()","css()","append()","button()","parse_url()","confirm()","new()",'.
  '"senttourl()","elseif()","catch()","getmessage()","typefunction()","loadthread()","deletecomment()","nl2br()","springcomment()","springvote()","postvote()","cancelvote()","closecommentifempty()","postcomment()",'.
  '"autoresize()","fadeto()","alert()","prepend()","rowcallback2()","chdir()","dirname()","fadeout()","callback()","handlerfunc()","mysql_connect()","mysql_errno()","phpversion()","base64_encode()",'.
  '"controllerclassname()","modelclassname()","memory_get_peak_usage()"]', true)
  as $b) $builtIn[$b] = true;
  
foreach(explode(',', 
  'cqrequest,datetimetostring,datetostring,db_gettables,db_stripprefix,do_fb_login,from,readstringlistfile,send_mail,stringlisttotext,get_user_timeoffset,stringtodatetime,strip_tags_attributes,'.
  'texttostringlist'
  ) as $b) $ignoreCallCheck[$b.'()'] = true;

foreach(file_list('./', null, true) as $k => $v) if((!strStartsWith($k, './ext') || strStartsWith($k, './ext/installer')) && 
  !strStartsWith($k, './lib/predef/') && (inStr($k, '.php') || inStr($k, '.js')))
{
  $file_id++;
  $parse2['files'][$file_id] = $k;
  foreach(explode(' ', str_replace(
    array(')', '$', ']', '"', ')', '=', "'", '[', ':', '!', '*', '+', '-', '@', '#', '/', '>', chr(10), ',', '.', '(', 'function '), 
    array(' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', '( ', 'function_'), file_get_contents($k))) as $word)
  {
    $word = trim(strtolower($word));
    if(inStr($word, '(') && strlen($word) > 2 && $word[0]!='$')
    {
      if(strStartsWith($word, 'function_')) 
        $parse['func'][substr($word, 9).')'] = $file_id;
      else 
        $parse['call'][$word.')'] = $file_id;
    }
  }
}

tsection('Function Declaration');

$notDeclared = array();
ksort($parse['call']);
foreach($parse['call'] as $function => $decl)
{
  if(!$builtIn[$function])
  {
    if(!$parse['func'][$function])    
      tlog(false, $function.' in '.$parse2['files'][$decl], 'OK', 'fail');
    else
      $okCount1++;
  }
}
tlog(true, 'Other declarations: '.$okCount1, 'OK', 'fail');

tsection('Unused Code');
ksort($parse['func']);
foreach($parse['func'] as $function => $decl)
{
  if(!inStr($parse2['files'][$decl], 'controller') && substr($function, 0, 1) != '_' && 
    substr($function, 0, 1) != '(' && !strStartsWith($parse2['files'][$decl], './msg') && !$ignoreCallCheck[$function] && !strEndsWith($function, 'callback()') &&
    !strStartsWith($function, 'js_') && !strStartsWith($function, 'dyn_'))
  {
    if(!$parse['call'][$function])
      tlog(false, $function.' in '.$parse2['files'][$decl], 'OK', 'fail');
    else
      $okCount2++;
  }
}
tlog(true, 'Other calls: '.$okCount2, 'OK', 'fail');

tsection_end();

?><!--<pre>
  <? 
  print_r($parse);
  ?>  
</pre>-->