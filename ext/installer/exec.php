<h3>Install Hubbub - Finishing</h3>
<div>
  <b>Finishing Install</b>
</div>
<br/>
<?
$GLOBALS['errorhandler_ignore'] = true;
?>
<br/>
<div id="db_check" class="result_box" style="margin-left: 150px;">
  
</div>
<script>
  
  function checkFields(parm)
  {
    $('#db_check').html('<img src="themes/default/ajax-loader.gif"/>');
    $.post('?p=exec_check', {'cmd' : parm }, function(data)
      {
        $('#db_check').html(data);        
      }
    );
  }
  
  checkFields();
  
</script>
