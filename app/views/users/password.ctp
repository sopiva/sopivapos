<br /><br />
<form method="post" action="/users/change_password">
<table>
  <tr>
    <td><label for="username">Vanha salasana:</label></td>
    <td><input type="password" size="32" value="" name="data[User][oldpassword]" /></td>
  </tr>
  <tr>
    <td><label for="username">Uusi salasana:</label></td>
    <td><input type="password" size="32" value="" name="data[User][newpassword]" /></td>
  </tr>
  <tr>
    <td><label for="username">Uudestaan:</label></td>
    <td><input type="password" size="32" value="" name="data[User][checkpassword]" /></td>
  </tr>
</table>
<br /><br />
<input type="submit" value="Vaihda" />
</form>
