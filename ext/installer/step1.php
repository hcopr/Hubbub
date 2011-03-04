<h3>Install Hubbub - Step 1</h3>
<div>
  <b><?= l10n('db.install') ?></b>
</div>
<br/>
<?

$defaultDBServer = 'localhost';
if(isset($_SERVER['DATABASE_SERVER'])) $defaultDBServer = $_SERVER['DATABASE_SERVER'];

include_once('lib/cq-forms.php');
$form = new CQForm('dbcred');

$onChange = 'checkDBFields();';
$form
  ->add('string', 'host', array('default' => $defaultDBServer, 'placeholder' => 'enter DB server name', 'onchange' => $onChange))
  ->add('string', 'user', array('default' => '', 'placeholder' => 'enter DB user name', 'onchange' => $onChange))
  ->add('string', 'password', array('default' => '', 'placeholder' => 'enter DB password', 'onchange' => $onChange))
  ->add('string', 'database', array('default' => 'hubbub2', 'placeholder' => 'enter name of database', 'onchange' => $onChange))
  ->add('button', 'btn.check', array('onclick' => $onChange))
  ->display();

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