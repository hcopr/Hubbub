<?

  $results = array();

  $url = HubbubEndpoint::urlUnify($_REQUEST['q']);
  $url_res = $this->model->loadUrl($url);

  if($url_res['result'] == 'OK')
  {
    if($url_res['url'] == '') $url_res['url'] = $url; else $url_res['url'] = HubbubEndpoint::urlUnify($url_res['url']);
    $results[] = $url_res;
  }
  
  if(sizeof($results) == 0)
  {
    ?><div class="fail">
      <?= l10n('friend.notfound') ?>
    </div><? 
  }
  else
  {
    ?><div class="win" style="padding-bottom: 8px;">
      <?= l10n('friend.searchresults') ?>:
    </div><table><?
    
    foreach($results as $item)
    {
      $ent = new HubbubEntity($item);
      ?><tr>
        <td width="64" valign="top"><img src="img/anonymous.png" width="48"/></td>
        <td valign="top" width="250">
          <b><?= $item['name'] ?></b><br/>
          <?= $item['url'] ?>
        </td>
        <td>&nbsp;</td>
        <td valign="top" width="*"><div id="frq_<?= $ent->key() ?>">
          <input type="button" value="<?= l10n('friend.addnow') ?>" 
            onclick="do_friend_request(<?= $ent->key() ?>);"/>          
        </div>
        </td>
      </tr><?      
    }
    
    ?></table><?    
  }

?>
<script>
  
  function do_friend_request(id)
  {
    $('#frq_'+id).append(' <img src="themes/default/ajax-loader.gif" align="absmiddle">'); 
    $.post('<?= actionUrl('ajax_friend_request', 'friends') ?>', { 'id': id }, function(data) {
      $('#frq_'+id).html(data);
      });
  }  
  
</script>