<?php

// Copyright (C) 2009 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

function t_escape($str)
{
  return htmlentities($str, ENT_QUOTES, "UTF-8");
}

function t_href($name)
{
  if ($name[0] != "/")
    $name = "/".$name;
  $ref = $name . "?" . filemtime(WWW_ROOT.$name);
  return $ref;
}

// turn an input price into cents, works with for example:
// 3       --> 300
// 3,5     --> 350
// 3.20245 --> 320
// -,20    --> -20
function t_price_in($val)
{
  if (!preg_match('#^(-?[0-9]*)(?:[.,]([0-9]+))?$#', $val, $reg))
    return 0;
  $price = $reg[1];
  if (!isset($reg[2]))
    $price .= "00";
  else
    $price .= substr($reg[2]."00", 0, 2);
  return (int) $price;
}

// turn price in cents into something neat for output
function t_price_out($in, $curr="&euro;")
{
  return sprintf("%d,%02d", $in/100, abs($in%100)) . (empty($curr) ? "" : " ".$curr);
}

// turn price in cents into something suitable for editing in a form
// 0   --> ""
// 100 --> 1
// 120 --> 1,20
function t_price_form($val)
{
  if (empty($val))
    return "";
  if (($val%100) == 0)
    return (int) ($val/100);
  return sprintf("%d,%02d", $val/100, abs($val%100));
}
