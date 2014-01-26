<?php

if (!isset($menus))
  $menus = array();

if (!empty($user_id))
  {
    $menu = array(
      "/shop" => "Uusi tapahtuma",
      "/report" => "Vanhat tapahtumat",
      );
    if (isset($admin_menu))
      $menu["/admin"] = "Yll\xc3\xa4pito";
    else
      $menu["/users/password"] = "Vaihda salasana";
    $menu["/users/logout"] = "Kirjaudu ulos ($user_name)";
    array_unshift($menus, $menu);
  }

foreach ($menus as $menu)
  {
    echo "<div style='font-weight: bold; padding-bottom: 1em; margin-bottom: 1em; border-bottom: 2px dotted black'>\n";
    $first = true;
    foreach ($menu as $link => $name)
      {
        echo $first ? "[ " : "| ";
        $first = false;
        echo "<a href='".t_escape($link)."'>".t_escape($name)."</a>\n";
      }
    echo "]\n";
    echo "</div>\n";
  }
