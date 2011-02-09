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
  $text = file_get_contents('plugins/friendlyui/msg.'.$data['notice_type'].'.txt');
  ?><div class="notice_me">
    <?= $text ?>
  </div><?
}

?>