<?php

if ($error)
  {
    echo "<p>K&auml;ytt&auml;jatunnus tai salasana ei skulannu, koita uudestaan.</p>\n";
  }

?>

<br /><br />
<?= $form->create(null, array("url" => "/users/login")) ?>
<table>
  <tr>
    <td><label for="username">K&auml;ytt&auml;j&auml;tunnus:</label></td>
    <td><?= $form->input("username", array("label" => false, "size" => 20, "type" => "text")) ?></td>
  </tr>
  <tr>
    <td><label for="password">Salasana:</label></td>
    <td><?= $form->input("password", array("label" => false, "size" => 20)) ?></td>
  </tr>
</table>
<br /><br />

<?= $form->submit("Kirjaudu") ?>
<?= $form->end() ?>

<script language="javascript" type="text/javascript">
  document.getElementById("UserUsername").focus();
</script>
