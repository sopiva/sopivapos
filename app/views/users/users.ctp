<?php

$statuses = array(1 => "Yll&auml;pit&auml;j&auml;", 0 => "K&auml;ytt&auml;j&auml;");

?>
<br clear="both" />

<table class="admin">
  <thead>
    <tr>
      <td>K&auml;ytt&auml;j&auml;</td>
      <td>Taso</td>
    </tr>
  </thead>
  <tbody>

  <?php
  $i=0;
  foreach ($users as $row)
    {
      $user = $row["User"];
      $uid = (int) $user["user_id"];
      $admin = ($user["admin"] >= 1) ? 1 : 0;
      ?>
      <tr class="<?= ($i++%2) ? 'odd' : '' ?>">
        <td style='vertical-align: text-top'><a href="/users/manage/<?= $uid ?>"><?= t_escape($user["username"]) ?></a></td>
        <td style='vertical-align: text-top'><?= $statuses[$admin] ?></td>
      </tr>
      <?php
    }
  ?>
  </tbody>
</table>

<br clear="both" />

<form method='post' action='/users/add_user'>
<table class="admin">
  <thead>
    <tr>
      <td colspan="2">Lis&auml;&auml; k&auml;ytt&auml;j&auml;</td>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Nimi:</td>
      <td><input type="text" value="" name="data[User][username]" size="40" /></td>
    </tr>
    <tr>
      <td>Salasana:</td>
      <td><input type="password" value="" name="data[User][password]" size="40" /></td>
    </tr>
    <tr>
      <td colspan='2'>
        <br />
        <input type="submit" value="Lis&auml;&auml;" />
      </td>
    </tr>
  </tbody>
</table>
</form>
