<?php

// Copyright (C) 2009 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

class Product extends AppModel
{
  public $belongsTo = array("Brand" => array("foreignKey" => "brand_id"));

  // override normal create()
  function create($brand_id, $name, $price)
  {
    $brand_id = (int) $brand_id;
    $price = (int) $price;
    $name = addslashes($name);
    $q = $this->query("SELECT create_product($brand_id, '$name', $price) AS id");
    return $q[0][0]["id"];
  }
}
