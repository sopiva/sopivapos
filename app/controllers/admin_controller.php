<?php

// Copyright (C) 2010-2011 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

class AdminController extends AppController
{
  public $uses = array("User", "Option");

  function beforeFilter()
  {
    parent::beforeFilter();
    $this->checkAdmin();
    $this->add_menu(array(
      "/admin" => "Asetukset",
      "/users/users" => "K\xc3\xa4ytt\xc3\xa4j\xc3\xa4hallinta",
      ));
  }

  function index()
  {
    $opts = $this->Option->find("all");
    $this->set("opts", $opts);
  }

  function save_options()
  {
    foreach ($this->data["Option"] as $key => $val)
      {
        $q = $this->Option->findByKey($key);
        if (empty($q))
          {
            $this->Option->create();
            $this->Option->save(compact("key", "val"));
          }
        else
          {
            $id = $q["Option"]["id"];
            $this->Option->save(compact("id", "val"));
          }
      }
    $this->redirect("/admin");
  }
}
