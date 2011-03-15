<div class="login_pane"><?

if($_SESSION['msg'])
{
  ?><div class="banner fail">
    <?= htmlspecialchars($_SESSION['msg']) ?>
  </div><?
  unset($_SESSION['msg']);
}
$GLOBALS['page.h1'] = l10n('hubbub.server');
?>
<? if($_REQUEST['msg'] != '') print(h2_uibanner(htmlspecialchars($_REQUEST['msg']))); ?>
<table width="900" align="center">
  <tr>
    <td>
    
    <h2><?= $this->srvName ?> <?= l10n('hubbub.server') ?></h2>
    
    <div id="bubble_items">
    
      <div class="paragraph padded_extra" style="width: 500px">      
        <a href="http://hubbub.at">Hubbub</a> <?= l10n('hubbub.is') ?>
      </div>
      
      <div>
        Sign in with<br/>
        <? 
        include('mvc/signin/signin.widget.php');
        ?>
      </div>
    
    </div>
        
    </td>
  </tr>
</table>	
</div>
