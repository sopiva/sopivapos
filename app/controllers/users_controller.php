<?php

// Copyright (C) 2009-2011 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

class UsersController extends AppController
{
  public $uses = array("User");

  function index()
  {
    $this->redirect("/users/password");
  }

  function denied()
  {
    $this->checkSession(false);
  }

  function login()
  {
    $this->set("error", false);

    if (!empty($this->data) && !empty($this->data["User"]))
      {
        $input = $this->data["User"];
        $row = $this->User->find("first", array(
          "conditions" => "inactive=0 AND lower(username)=lower('".addslashes($input["username"])."')"));
        $row = $row ? $row["User"] : null;

        if (!empty($row) && $this->User->check_password($row["user_id"], $row["password"], $input["password"]))
          {
            $this->Session->write("User", $row);
            $this->redirect("/");
          }
        else
          {
            $this->set("error", true);
          }
        unset($this->data["User"]["password"]);
      }
  }

  function logout()
  {
    $this->Session->destroy();
    $this->redirect("/");
  }

  function change_password()
  {
    $this->checkSession();
    $id = $this->get_user_id();
    $msg = "Password change failed.";

    if (!empty($this->data))
      {
        if ((int) $this->auth_user["admin"] && isset($this->data["User"]["user_id"]))
          {
            $id = (int) $this->data["User"]["user_id"];
            $admin = true;
          }
        $newpassword = $this->data["User"]["newpassword"];
        $ok = true;
        if (! $admin)
          {
            $row = $this->User->findByUserId($id);
            $dbpassword = $row["User"]["password"];
            $oldpassword = $this->data["User"]["oldpassword"];
            $checkpassword = $this->data["User"]["checkpassword"];
            $ok = $this->User->check_password($id, $dbpassword, $oldpassword);
            if (! $ok)
              {
                $msg = "Old password was incorrect.";
              }
            else
              {
                $ok = ($checkpassword == $newpassword) && !empty($newpassword);
                if (!$ok)
                  $msg = "The (new) passwords didn't match.";
              }
          }
        if ($ok)
          $ok = $this->User->set_password($id, $newpassword);
        if ($ok)
          $msg = "Password changed.";
      }

    $this->Session->setFlash($msg);
    $this->redirect($admin ? "/users/manage/".$id : "/users/password");
  }

  function password()
  {
    $this->checkSession();
  }

  // USER ADMINISTRATION
  private function usermanager()
  {
    $this->checkAdmin();
  }

  function users()
  {
    $this->usermanager();
    $userlist = $this->User->find("all", array(
      "conditions" => "inactive=0",
      "order" => "username ASC"));
    $this->set("users", $userlist);
  }

  function manage($uid)
  {
    $this->usermanager();
    $uid = (int) $uid;
    $row = $this->User->findByUserId($uid);
    if (empty($row))
      $this->redirect("/users/users");
    $this->set("row", $row);
  }

  function add_user()
  {
    $this->usermanager();

    if (empty($this->data))
      $this->redirect("/users/users");

    $data = $this->data["User"];
    $username = $data["username"];
    $plainpw = $data["password"];

    $exist = $this->User->find("first", array(
      "conditions" => "lower(username) = lower('".addslashes($username)."')"));
    if (!empty($exist))
      {
        $this->Session->setFlash("The specified username is already in use.");
        $this->redirect("/users/users");
        return;
      }

    $data = array("username" => $username, "password" => "(null)");
    if ($this->User->save($data))
      {
        $uid = (int) $this->User->getLastInsertID();
        $this->User->set_password($uid, $plainpw);
        $this->Session->setFlash("User created.");
        $this->redirect("/users/manage/".$uid);
      }
    else
      {
        $this->Session->setFlash("User creation failed.");
        $this->redirect("/users/users");
      }
  }

  function delete_user($uid)
  {
    $this->usermanager();
    $uid = (int) $uid;
    $this->User->query("UPDATE users SET inactive=1 WHERE user_id=".$uid);
    $this->Session->setFlash("User deleted.");
    $this->redirect("/users/users");
  }

  function set_level($uid, $level=0)
  {
    $this->usermanager();
    $this->User->query("UPDATE users set admin=".((int) $level)." WHERE user_id=".((int) $uid));
    $this->Session->setFlash("User status changed.");
    $this->redirect("/users/manage/".$uid);
  }
}
