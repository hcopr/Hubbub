<?

  $results = array();

  $url = HubbubEndpoint::urlUnify($_REQUEST['q']);
  $url_res = $this->model->loadUrl($url);

  $GLOBALS['group.options'] = array('<option value="0">Select...</option>');
  foreach($this->model->getMyGroups() as $grp)
  $GLOBALS['group.options'][] = '<option value="'.$grp['lg_key'].'">'.$grp['lg_name'].'</option>';
  
  if($url_res['result'] == 'OK')
  {
    if($url_res['url'] == '') $url_res['url'] = $url; else $url_res['url'] = HubbubEndpoint::urlUnify($url_res['url']);
    if(trim($url_res['server']) != '' && trim($url_res['user']) != '') 
    {
      $server = new HubbubServer($url_res['server']);
      if(!$server->isTrusted()) $server->msg_trust_sendkey1();
      $url_res['server_trusted'] = $server->isTrusted();
      $results[] = $url_res;
    }
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
          <b><?= $item['name'] ?></b>
          <div id="srvstat_<?= md5($item['server']) ?>">
          <?
          if(!$item['server_trusted'])
          {
            ?>contacting server...
              <script>             
              function reload_<?= md5($item['server']) ?>()
              {
                $.post('<?= actionUrl('ajax_pingserver', 'friends', $item) ?>', function(data) {
                  $('#srvstat_<?= md5($item['server']) ?>').html(data);
                  });                
              }
              reload_<?= md5($item['server']) ?>();
              </script>
            <?  
          }
          else
          {
            ?><span class="green"><?= $item['url'] ?></span><?
          }
          ?>
          </div>
        </td>
        <td>&nbsp;</td>
        <td valign="top" width="*"><div id="frq_<?= $ent->key() ?>">
            <input type="button" value="<?= l10n('friend.addnow') ?>" 
              onclick="do_friend_request(<?= $ent->key() ?>);"/>          
            <?= l10n('addto') ?>
            <select id="group_select_<?= $ent->key() ?>">
              <?= implode('', $GLOBALS['group.options']) ?>
            </select>
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
    $.post('<?= actionUrl('ajax_friend_request', 'friends') ?>', { 'id': id, 'group' : $('#group_select_'+id+' option:selected').val() }, function(data) {
      $('#frq_'+id).html(data);
      });
  }  
  
  $("button, input:submit, input:button, a.btn").button(); 
</script>