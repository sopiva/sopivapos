<?php

// Copyright (C) 2009-2011 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

App::Import("Vendor", "taisiatools");

class AppController extends Controller
{
  public $cacheAction = array();

  function beforeFilter()
  {
    $this->set("user_name", $this->get_user_name());
    $this->set("user_id", $this->get_user_id());
    if (isset($this->auth_user) && !empty($this->auth_user["admin"]))
      $this->set("admin_menu", true);
    $this->view_menus = array();
  }

  protected function add_menu($items)
  {
    $this->view_menus[] = $items;
    $this->set("menus", $this->view_menus);
  }

  function get_user_id()
  {
    $this->checkSession(false);
    return isset($this->auth_user) ? $this->auth_user["user_id"] : null;
  }

  function get_user_name()
  {
    $this->checkSession(false);
    return isset($this->auth_user) ? $this->auth_user["username"] : null;
  }

  protected function checkAdmin()
  {
    $this->checkSession();
    if ($this->auth_user["admin"])
      return true;
    // denied
    $this->redirect("/users/denied");
    exit;
  }

  protected function checkSession($exit_on_fail=true)
  {
    if (isset($this->__have_session))
      return true;

    // If the session info hasn't been set...
    if (! $this->Session->check("User"))
      {
        if ($exit_on_fail == false)
          return false;
        // Force the user to login
        $this->redirect("/users/login");
        exit;
      }
    $user = $this->Session->read("User");
    if (is_array($user))
      $this->auth_user = $user;
    $this->__have_session = true;
  }

  protected function return_render($action=null, $layout="ajax")
  {
    $tmp_auto = $this->autoRender;
    $tmp_output = $this->output;
    $this->output = "";
    $data = $this->render($action, $layout);
    $this->output = $tmp_output;
    $this->autoRender = $tmp_auto;
    return $data;
  }
}
