<?
$GLOBALS['errorhandler_ignore'] = true;

$icon = 'ktip';

$sbase = $_REQUEST['serverurl'];

?>
<table width="100%"><tr>
  <td valign="top" width="64"><img src="img/<?= $icon ?>.png"/></td>
  <td>&nbsp;</td>
  <td><?= $msg ?>
    <div id="inst_log">
      
    </div>
  </td>
</tr></table>
<script>
  $("button, input:submit, input:button, a.btn").button();
  
  $.post('?p=exec_do', {'part' : 0 }, function(data)
    {
      $('#inst_log').append(data);        
    }
  );
</script>
<?
print(ob_get_clean());
die();
?>