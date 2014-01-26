<?php

// Copyright (C) 2009-2010 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

class Receipt extends AppModel
{
  public $start_year = 2009;
  public $hasMany = array(
    "ReceiptPayment" => array("foreignKey" => "receipt_id"),
    "ReceiptItem" => array("foreignKey" => "receipt_id"));

  function receipt_number($day_id, $time_t)
  {
    $day_num = date("z", $time_t);
    $year_num = date("Y", $time_t) - $this->start_year + 1;

    return $year_num * 100000 + $day_num * 100 + $day_id;
  }
}
