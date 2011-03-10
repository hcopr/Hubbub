<div class="login_pane">

<table width="900" align="center">
  <tr>
    <td>
    
    <h2><?= l10n('email.recovery') ?> <a href="./"><?= $this->srvName ?></a></h2>
    
    <br/>
    <div class="balloon">
      <b>Welcome, <?= getDefault($this->usr['u_name'], $this->uds['ia_url']) ?></b>
    </div>
    <br/>
    
    <?
    
    $form = new CQForm('pwrecovery');
    $form
      ->add('password', 'new_pwd', array('onvalidate' => function($value, $e, $form) { 
          $form->pwd = trim($value);
          if(strlen($form->pwd) < 5) return(l10n('email.password.tooshort')); else return(true);
        }))
      ->add('param', 'i', $_REQUEST['i'])
      ->add('submit', 'reset_pwd')
      ->ds($_REQUEST)
      ->receive(function($data, $form) {
          $uds = DB_GetDataset('idaccounts', $_REQUEST['i'], 'ia_recovery');
          $uds['ia_comments'] = md5($uds['ia_url'].$form->pwd);
          $uds['ia_recovery'] = '';
          DB_UpdateDataset('idaccounts', $uds);
          object('user')->loginWithId($uds['ia_user']);
          print(l10n('email.password.reset').'<br/><br/><a href="'.actionUrl('index', 'home').'" class="btn">&gt; OK</a>');
          $form->hidden = true;
          })
      ->display();
        
    ?>
    
    </td>
  </tr>
</table>

</div>