<h3>Install Hubbub - Step 2</h3>
<div>
  <b>Configuration</b>
</div>
<br/>
<?
$GLOBALS['errorhandler_ignore'] = true;
$_SESSION['install']['ref'] = getDefault($_SERVER['HTTP_REFERER'], $_SESSION['install']['ref']);

include_once('lib/cq-forms.php');

$form = new CQForm('dbcred');
if($form->submitted)
{
  $form->getData(); 
}

$onChange = 'checkFields();';  

$surl = parse_url($_SERVER['HTTP_REFERER']);
$port = ($surl['port'] == '' || $surl['port'] == 80) ? '' : ':'.$surl['port'];
$sbase = $surl['host'].$port.$surl['path'];
$sbase = getDefault($sbase, $_SESSION['installer']['server_base']);
if(substr($sbase, -1) == '/') $sbase = substr($sbase, 0, -1);

$form->add('string', 'hosturl', 'Server URL', array('default' => $sbase, 'onchange' => $onChange, 
  'infomarker' => '^ This address must be publicly available via HTTP'));
$form->add('string', 'adminpw', 'Admin password', array('default' => substr(base64_encode(md5(time())), 0, 8), 'onchange' => $onChange, 
  'infomarker' => '^ Please make a note of your admin password'));

$form->add('button', 'btn.check', 'Check', array('onclick' => $onChange));

$form->display();

?>
<br/>
<div id="db_check" class="result_box" style="margin-left: 150px;">
  
</div>
<script>
  
  function checkFields(parm)
  {
    $('#db_check').html('<img src="themes/default/ajax-loader.gif"/>');
    $.post('?p=step2_check', {'cmd' : parm, 'apwd' : $('#fld_adminpw').val(), 'serverurl' : $('#fld_hosturl').val(), 'path' : '<?= $surl['path'] ?>'}, function(data)
      {
        $('#db_check').html(data);        
      }
    );
  }
  
  checkFields();
  
</script>