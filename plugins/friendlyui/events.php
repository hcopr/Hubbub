<?

function friendlyui_user_new(&$entityDS, &$userDS)
{
  // welcome message
  $welcome = new HubbubMessage('notice');
  $welcome->owner($entityDS);
  $welcome->data['notice_type'] = 'welcome';
  $welcome->save();
}

function friendlyui_show_notice(&$data, &$ds)
{
  l10n_load('plugins/friendlyui/l10n');
  
  ?><div class="notice_me">
    <? include('plugins/friendlyui/msg.'.$data['notice_type'].'.php'); ?>
  </div><?
}

?>