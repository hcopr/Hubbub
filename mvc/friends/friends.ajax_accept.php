<?

if($this->result['data']['result'] != 'OK')
{
  ?><div class="fail"><?= $this->l10n('friend.req.error').' :: '.$this->result['data']['reason'] ?></div><?
}
else
{
  ?><div class="win"><?= $this->l10n('friend.req.accepted') ?></div><?
}

?>