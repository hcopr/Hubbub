<h3>Install Hubbub - Step 1</h3>
<div>
  <b>Database Installation</b>
</div>
<br/>
<?
include_once('lib/cq-forms.php');

$form = new CQForm('dbcred');

$onChange = 'checkDBFields();';
$form->add('string', 'host', 'Host Address', array('default' => 'localhost', 'placeholder' => 'enter DB server name', 'onchange' => $onChange));
$form->add('string', 'user', 'User Name', array('default' => '', 'placeholder' => 'enter DB user name', 'onchange' => $onChange));
$form->add('string', 'password', 'Password', array('default' => '', 'placeholder' => 'enter DB password', 'onchange' => $onChange));
$form->add('string', 'database', 'Database Name', array('default' => 'hubbub2', 'placeholder' => 'enter name of database', 'onchange' => $onChange));
$form->add('button', 'btn', 'Check', array('onclick' => $onChange));
#$form->add('string', 'prefix', 'Table Prefix', array('default' => 'h2', 'placeholder' => 'enter a table prefix', 'onchange' => $onChange));

$form->display();

?>
<br/>
<div id="db_check" class="result_box" style="margin-left: 150px;">
  
</div>
<script>
  
  function checkDBFields(parm)
  {
    $('#db_check').html('<img src="themes/default/ajax-loader.gif"/>');
    $.post('?p=step1_check', {'cmd' : parm, 'host' : $('#fld_host').val(), 'user' : $('#fld_user').val(), 'password' : $('#fld_password').val(), 'database' : $('#fld_database').val(), 'prefix' : $('#fld_prefix').val()}, function(data)
      {
        $('#db_check').html(data);        
      }
    );
  }
  
  checkDBFields();
  
</script>