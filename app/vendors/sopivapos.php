<?php

// Copyright (C) 2009-2012 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

require_once dirname(__FILE__) . "/sopivapos/i18n.php";
require_once dirname(__FILE__) . "/sopivapos/taisiafpdf.php";

define("TAISIAFPDF_FONT", "dejavulgcsanscondensed");

class SopivaReceipt
{
  public $address, $url, $businessid, $logo, $person, $id;
  public $items;      // array(array("name" => "x", "sum" => cnt, "vat" => p), ...)
  public $items_vat0; // $items prices are vat0
  public $payment;    // like items
  public $utf8;       // strings are utf8
  public $time;       // creation time

  private $i18n, $pdf, $lineh;

  function __construct($lang=null)
  {
    $this->pdf = null;
    $this->format = "72mm";
    $this->i18n = new TaisiaInvoiceI18N($lang);

    $this->time = time();

    $this->items = array();
    $this->items_vat0 = true;
    $this->payment = array();
    $this->utf8 = true;
  }

  public static function format_sum($cents)
  {
    $eur = (int) ($cents / 100);
    $cnt = abs($cents % 100);
    return sprintf("%d,%02d \xa4", $eur, $cnt);
  }

  function get_string($str)
  {
    return $this->i18n->get_string($str);
  }

  function hline($width)
  {
    $y = $this->pdf->GetY();
    $this->pdf->SetLineWidth($width);
    $this->pdf->Line(0, $y, $this->width, $y);
  }

  function write($width, $text, $align="", $newline=0, $type="", $sizep=0)
  {
    $this->pdf->SetFont(TAISIAFPDF_FONT, $type, round(9.25+$sizep/2));
    if ($width < 0)
      {
	$this->pdf->x = $this->pdf->lMargin;
	$width = 0;
      }
    $this->pdf->Cell($width, $this->lineh, $text, 0, $newline, $align);
  }

  function Output($name="", $dest="")
  {
    return $this->pdf->Output($name, $dest);
  }

  function Header($pdf)
  {
    $w = 35;
    $x = (int) (($this->width - $w) / 2);
    $pdf->Image($this->logo, $x, 2, $w, null);
    $pdf->SetY(22);
    $this->lineh = 3;
    foreach (explode("\n", $this->address) as $line)
      $this->write(-1, $line, "C", 1, "", -2);
    $pdf->SetY(5);
    $this->write(-1, $this->id, "R", 0, "", -2);
  }

  function Footer($pdf)
  {
    $pdf->SetY(-16);
    $this->lineh = 3;
    $this->write(-1, $this->get_string("You were served by").": ".$this->person, "C", 1, "", -2);
    $this->write(-1, date("d.m.Y H:i", $this->time), "C", 1, "", -2);
    $this->write(-1, $this->url, "C", 1, "", -2);
    $this->write(-1, "Y-".$this->businessid, "C", 0, "", -2);
  }

  function utf8_to_latin9()
  {
    $this->person = iconv("UTF-8", "ISO-8859-15//TRANSLIT", $this->person);
    $this->address = iconv("UTF-8", "ISO-8859-15//TRANSLIT", $this->address);
    foreach ($this->items as &$item)
      $item["name"] = iconv("UTF-8", "ISO-8859-15//TRANSLIT", $item["name"]);
    foreach ($this->payment as &$item)
      $item["name"] = iconv("UTF-8", "ISO-8859-15//TRANSLIT", $item["name"]);
  }

  function draw_invoice($type=null)
  {
    if ($this->format == "72mm")
      {
        $format = array(72, 0);
        $this->width = 72;
        $this->lineh = 3.5;
      }
    else if ($this->format == "A6")
      {
        $format = "A6";
        $this->width = 105;
      }

    if ($this->utf8)
      $this->utf8_to_latin9();

    // multiple vats?
    $multivat = false;
    $multicnt = false;
    $vat = null;
    foreach ($this->items as $item)
      {
        if ($vat === null)
          $vat = $item["vat"];
        else if ($item["vat"] != $vat)
          $multivat = true;
        if (isset($item["cnt"]) && $item["cnt"] > 1)
          $multicnt = true;
      }

    // col width
    $cwt = 24;
    if ($multivat || $multicnt)
      $cw = $cwt / ($multivat + $multicnt * 2);
    else
      $cwt = 0;
    $linelen = (int) round($this->width/1.3 - $cwt);

    // wrap descriptions and calculate taxes
    $ilines = array();
    $total_sum = 0;
    $total_tax = 0;
    foreach ($this->items as $item)
      {
        $item_n   = (isset($item["cnt"]) && $item["cnt"] > 1) ? $item["cnt"] : 1;
        $item_name = $item["name"];
        $item_sum = $item_n * $item["sum"]; // cnt
        $item_vat = $item["vat"]; // %

        $item_sum_str = self::format_sum($item_sum);
        $item_vat_str = sprintf("%.2f", $item_vat);
        $item_vat_str = $item_vat . " %";

        $lines = split("\n", trim(wordwrap($item_name, round($linelen/2))));
        $tline = array(array($linelen, array_shift($lines), "L", 1));
        if ($item_sum !== 0)
          {
            $tline[0][3] = 0;
            if ($multivat) // write vat % on first line of each item
              {
                $tline[] = array($cw, $item_vat_str, "R");
              }
            if ($multicnt)
              {
                $a_sum_str = self::format_sum($item["sum"]);
                $tline[] = array($cw-2, $item_n, "R");
                $tline[] = array($cw+2, $a_sum_str, "R");
              }
            $tline[] = array(0, $item_sum_str, "R", 1);
          }
        $ilines[] = $tline;
        foreach ($lines as $item_line)
          {
            $ilines[] = array(array($linelen, $item_line, "L", 1));
          }
        $total_sum += $item_sum;
        if ($this->items_vat0)
          $total_tax += $item_sum * $item_vat / 100;
        else
          $total_tax += $item_sum - ($item_sum*100/(100+$item_vat));
      }

    // create pdf & start writing it
    if (is_array($format))
      $format[1] = 65 + $this->lineh * (sizeof($ilines) + sizeof($this->payment));
    $this->pdf = new TaisiaFPDF($this, "mm", $format);
    $this->pdf->SetMargins(0, 0);
    if (is_array($format))
      $this->pdf->SetAutoPageBreak(false);

    $this->pdf->AddPage();
    $this->pdf->SetTitle(date("r", $this->time));
    $this->pdf->SetAuthor($this->url);
    $this->pdf->SetY(30);

    $this->write($linelen, $this->get_string("Item"), "", 0, "B", 0);
    if ($multivat)
      {
        $this->write($cw, $this->get_string("VAT")." %", "R", 0, "B");
      }
    if ($multicnt)
      {
        $this->write($cw-2, $this->get_string("Count"), "R", 0, "B");
        $this->write($cw+2, "\xe0  ", "R", 0, "B");
      }
    $this->write(0, $this->get_string("Cost")." \xa4", "R", 1, "B");
    $this->hline(0.3);
    $this->pdf->Ln(1);

    // items
    foreach ($ilines as $tline)
      {
        $last = array_pop($tline);
        foreach ($tline as $item)
          call_user_func_array(array($this, "write"), $item);
        call_user_func_array(array($this, "write"), $last);
      }

    // tax, rounded to cent
    $total_tax = round($total_tax);
    if ($this->items_vat0)
      {
        $total_vat0 = $total_sum;
        $total_sum += $total_tax;
      }
    else
      {
        $total_vat0 = $total_sum - $total_tax;
      }

    $this->pdf->Ln();
    if ($total_tax > 0)
      {
        $this->write(150, $this->get_string("Total excluding VAT"));
        $total_vat0_str = self::format_sum($total_vat0);
        $this->write(0, $total_vat0_str, "R", 1);
      }
    if (!$multivat)
      $this->write(150, $this->get_string("Value added tax")." ".$vat."%");
    else
      $this->write(150, $this->get_string("Total VAT"));
    $total_tax_str = self::format_sum($total_tax);
    $this->write(0, $total_tax_str, "R", 1);

    $this->pdf->Ln();

    $this->write(150, $this->get_string("Total"), "", 0, "");
    $total_sum_str = self::format_sum($total_sum);
    $this->write(0, $total_sum_str, "R", 1, "");

    foreach ($this->payment as $item)
      {
        $item_name = $item["name"];
        $item_sum = $item["sum"]; // cnt
        $item_sum_str = self::format_sum($item_sum);
        $this->write($linelen, $item_name);
        $this->write(0, $item_sum_str, "R", 1);
      }
  }
}
