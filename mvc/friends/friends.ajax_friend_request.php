<?

$friendEntity = new HubbubEntity($_REQUEST['id']);
$req = $this->model->friend_request($friendEntity);

if($req['result'] == 'OK')
{
  ?><div class="win banner">
    <?= l10n('friend_request.sent') ?>
  </div><? 
}
else
{
  ?><div class="win banner">
    <?= l10n('friend_request.error').': '.getDefault($req['reason'], 'could not connect') ?>
  </div><? 
}

?>