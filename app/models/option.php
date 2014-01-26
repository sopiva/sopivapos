<?php

// Copyright (C) 2010 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

class Option extends AppModel
{
  function get($key)
  {
    $q = $this->query("SELECT val FROM options WHERE key='".addslashes($key)."'");
    if (!empty($q))
      return $q[0][0]["val"];
    return null;
  }

  function set($key, $val)
  {
    $q = $this->query("SELECT id FROM options WHERE key='".addslashes($key)."'");
    if (!empty($q))
      $this->query("UPDATE options SET val='".addslashes($val)."' WHERE id=".((int) $q[0][0]["id"]));
    else
      $this->query("INSERT INTO options (key, val) VALUES ('".addslashes($key)."', '".addslashes($val)."')");
  }
}
