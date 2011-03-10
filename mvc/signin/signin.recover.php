<div class="login_pane">

<table width="900" align="center">
  <tr>
    <td>
    
    <h2><?= l10n('email.recovery') ?> <a href="./"><?= $this->srvName ?></a></h2>
    
    <br/>
    <div class="balloon">
      <?= l10n('email.recovery.balloon') ?>
    </div>
    <br/>
    
    <?
    
    $form = new CQForm('pwrecovery');
    $form
      ->add('string', 'email_address', array('validate' => 'email', 'onvalidate' => function($data, $e, $form) { 
          $form->ads = DB_GetDataset('idaccounts', trim($data), 'ia_url');
          if($form->ads['ia_user'] > 0) 
            return(true); else return(l10n('email.notindb')); 
        }))
      ->add('submit', 'email_recover_instructions')
      ->ds($_REQUEST)
      ->receive(function($data, $form) {
          $form->hidden = true;
          send_mail($form->ads['ia_url'], 'email.recovery.php', $form->ads);
          print('<div class="banner">'.l10n('email.recovery.sent').'</div>');
        })
      ->display();
        
    ?>
    
    </td>
  </tr>
</table>

</div>