<?php

$user = $row["User"];
$statuses = array(1 => "Yll&auml;pit&auml;j&auml;", 0 => "K&auml;ytt&auml;j&auml;");

$admin = ($user["admin"] >= 1) ? 1 : 0;
$other = (int) !$admin;

?>
<br clear="both" />

<table class="admin">
  <thead>
    <tr>
      <td colspan="2"><?= t_escape($user["username"]) ?></td>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="caption">K&auml;j&auml;taso</td>
      <td>
        <?= $statuses[$admin] ?>
      </td>
    </tr>
    <tr>
      <td class="caption">Salasana</td>
      <td>
        <form method="post" action="/users/change_password">
          <input type="password" size="32" value="" name="data[User][newpassword]" />
          <input type="hidden" name="data[User][user_id]" value="<?= $user['user_id'] ?>" />
          <input type="submit" value="Vaihda" />
        </form>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <b>
        [ <a href="/users/set_level/<?= $user['user_id'].'/'.$other ?>">Vaihda taso: <?= $statuses[$other] ?></a>
        | <a href="/users/delete_user/<?= $user['user_id'] ?>">Poista k&auml;ytt&auml;j&auml;</a>
        | <a href="/users/users">Palaa</a>
        ]
        </b>
      </td>
    </tr>
  </tbody>
</table>
