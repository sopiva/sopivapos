<?php

// Copyright (C) 2009 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

class User extends AppModel
{
  public $primaryKey = "user_id";
  private $mySecret = "cda281144a3c";

  private function hash_password($user_id, $plaintext)
  {
    return sha1($user_id . "/" . sha1($plaintext) . "/" . $this->mySecret);
  }

  function set_password($id, $password)
  {
    $this->id = $id;
    return $this->saveField("password", $this->hash_password($id, $password));
  }

  function check_password($uid, $dbpw, $inputpw)
  {
    $hash = $this->hash_password($uid, $inputpw);
    return $hash == $dbpw;
  }
}
