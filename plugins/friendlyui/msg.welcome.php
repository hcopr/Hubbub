<div style="padding: 6px">
  <h3><?= l10n('fui.wel.title') ?></h3>
  <?= l10n('fui.wel.getstarted') ?>
</div>
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top" width="33%">
      <div class="action_tile banner" style="min-height: 150px">
        <h4><?= l10n('fui.wel.getlisted') ?></h4>
        <div class="tile_text">
          <img src="img/kwrite.png" align="left" style="padding-right: 8px"/>
          <?= l10n('fui.wel.getlisted.text') ?><br/>
          <div align="center">
            <a href="<?= actionUrl('ab', 'friends') ?>" class="btn"><?= l10n('fui.wel.getlisted') ?></a>
          </div>
        </div>
      </div>
    </td>
    <td valign="top" width="33%">
      <div class="action_tile banner" style="min-height: 150px">
        <h4><?= l10n('fui.wel.addfriends') ?></h4>
        <div class="tile_text">
          <img src="img/ksmiletris.png" align="left" style="padding-right: 8px"/>
          <?= l10n('fui.wel.addfriends.text') ?><br/>
          <div align="center">
            <a href="<?= actionUrl('add', 'friends') ?>" class="btn"><?= l10n('fui.wel.addfriends') ?></a>
          </div>
        </div>
      </div>
    </td>
    <td valign="top" width="33%">
      <div class="action_tile banner" style="min-height: 150px">
        <h4><?= l10n('fui.wel.poststuff') ?></h4>
        <div class="tile_text">
          <img src="img/Community%20Help.png" align="left" style="padding-right: 8px"/>
          <?= l10n('fui.wel.poststuff.text') ?>
        </div>
      </div>
    </td>
  </tr>
</table>

