<?php

// Copyright (C) 2009 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

class Brand extends AppModel
{
  public $hasMany = array(
    "Product" => array("foreignKey" => "brand_id", "order" => "name ASC"),
    );
}
