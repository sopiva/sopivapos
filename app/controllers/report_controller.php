<?php

// Copyright (C) 2009-2011 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

App::import("Vendor", "sopivapos");

class ReportController extends AppController
{
  public $uses = array(
    "Receipt", "ReceiptPayment", "ReceiptItem",
    "Brand", "Product");

  function beforeFilter()
  {
    parent::beforeFilter();
    if (!$this->Session->check("User"))
      {
        $this->redirect("/users/login");
        exit;
      }
  }

  private function get_brands()
  {
    $q = $this->Brand->find("all", array("order" => "name ASC"));
    $brands = array();
    foreach ($q as $r)
      $brands[$r["Brand"]["id"]] = $r["Brand"]["name"];
    return $brands;
  }

  private function init_pdf()
  {
    $pdf = new SopivaReceipt("fi");
    $pdf->logo = WWW_ROOT . "/static/logo-600px.png";
    $pdf->address = $this->Option->get("address");
    $pdf->url = $this->Option->get("url");
    $pdf->businessid = $this->Option->get("businessid");
    return $pdf;
  }

  function index($month=null)
  {
    $t = strtotime($month . "-01");
    if (empty($t))
      $t = time();

    $y = (int) date("Y", $t);
    $m = (int) date("m", $t);
    $this->set("y", $y);
    $this->set("m", $m);

    $q = $this->Receipt->find("all", array(
      "conditions" => "date_part('year',time)=$y AND date_part('month',time)=$m",
      "order" => "time DESC"));
    $this->set("receipts", $q);

    $brands = $this->get_brands();
    $this->set("brands", $brands);

    $q = $this->Receipt->query(
      "SELECT i.brand_id,i.name,i.vat,i.sum AS unit_sum,sum(cnt) as unit_count ".
      "  FROM receipt_items i JOIN receipts r ON (i.receipt_id=r.id) ".
      " WHERE date_part('year',r.time)=$y AND date_part('month',r.time)=$m ".
      " GROUP BY i.brand_id, i.name, i.vat, unit_sum");
    $sales = array();
    foreach ($q as $row)
      {
        $b = $row[0]["brand_id"];
        if (!isset($sales[$b]))
          $sales[$b] = array();
        $n = ucfirst(strtolower(trim($row[0]["name"])))." ".$row[0]["vat"]."%";
        $c = $row[0]["unit_count"];
        $s = $c * $row[0]["unit_sum"];
        if (!isset($sales[$b][$n]))
          $sales[$b][$n] = array("count" => 0, "sum" => 0);
        $sales[$b][$n]["count"] += $c;
        $sales[$b][$n]["sum"] += $s;
      }
    $this->set("sales", $sales);
  }

  function receipt_view($id=0)
  {
    $this->set("img", sprintf("/report/receipt_render/%d/png", $id));
    $this->render("report_view");
  }

  function receipt_render($id=0, $action="show")
  {
    $id = (int) $id;
    $r = $this->Receipt->findById($id);
    if (empty($r))
      $this->redirect("/shop");

    $tt = strtotime(substr($r["Receipt"]["time"], 0, 19));

    $pdf = $this->init_pdf();

    $pdf->person = $r["Receipt"]["person"];
    $pdf->time = $tt;
    $pdf->id = $r["Receipt"]["number"];

    $pdf->items_vat0 = false;
    $pdf->items = $r["ReceiptItem"];
    $pdf->payment = $r["ReceiptPayment"];

    $filename = "receipt-".($pdf->id).".pdf";
    $pdf->draw_invoice();

    $next = !empty($_GET["next"]) ? $_GET["next"] : "/report";
    $this->pdf_render($pdf, $action, $filename, $next);
  }

  function report_view($day=0)
  {
    $day = preg_replace("#[^-0-9]#", "", $day);
    $this->set("img", "/report/report_render/$day/png");
  }

  function report_render($day=0, $action="show")
  {
    $day = preg_replace("#[^-0-9]#", "", $day);

    if (strlen($day) == 4+2+2+2) // 2009-03-15
      $dayrep = $cond = "time::date::text='$day'";
    else if (strlen($day) == 4+2+1) // 2009-03
      $monrep = $cond = "to_char(time, 'YYYY-MM')='$day'";
    else
      exit;

    $brands = $this->get_brands();

    // brand_sales = array(brand_id => array(vat => sum))
    // sales = array(person => array(positive=>brand_sales, negative=>brand_sales))
    $sales = array();
    $payments = array();
    $q = $this->Receipt->query("SELECT * FROM receipts WHERE $cond ORDER BY person ASC");
    foreach ($q as $r)
      {
        $p = isset($dayrep) ? $r[0]["person"] : "POS";
        if (!isset($sales[$p]))
          $sales[$p] = array(1 => array(), -1 => array());
        $sq = $this->Receipt->findById($r[0]["id"]);
        foreach ($sq["ReceiptItem"] as $sr)
          {
            $cnt = (int) $sr["cnt"];
            $sum = (int) $sr["sum"];
            $vat = (int) $sr["vat"];
            $bid = (int) $sr["brand_id"];
            $sum *= $cnt;
            $type = ($sum >= 0) ? 1 : -1;
            if (!isset($sales[$p][$type][$bid]))
              $sales[$p][$type][$bid] = array();
            if (!isset($sales[$p][$type][$bid][$vat]))
              $sales[$p][$type][$bid][$vat] = 0;
            $sales[$p][$type][$bid][$vat] += $sum;
          }
        foreach ($sq["ReceiptPayment"] as $sr)
          {
            $type = $sr["name"];
            $sum = (int) $sr["sum"];
            if (!isset($payments[$type]))
              $payments[$type] = 0;
            $payments[$type] += $sum;
          }
      }
    // hack
    if (isset($payments["Takaisin"]))
      {
        $sum_out = $payments["Takaisin"];
        $sum_in = $payments["K\xc3\xa4teinen"];
        if (true)
          {
            $payments["K\xc3\xa4teinen"] = $sum_in - $sum_out;
            unset($payments["Takaisin"]);
          }
        else
          {
            $payments["K\xc3\xa4teinen netto"] = $sum_in - $sum_out;
          }
      }
    ksort($payments);

    $pdf = $this->init_pdf();

    $pdf->person = $this->get_user_name();
    $pdf->time = time();
    $pdf->id = $day;

    $pdf->items_vat0 = false;
    $pdf->items = array();
    $pdf->payment = array();

    $cnt = 1;
    foreach ($sales as $person => $psales)
      foreach ($psales as $type => $tsales)
        foreach ($tsales as $brand_id => $bsales)
          foreach ($bsales as $vat => $sum)
            {
              $name = !isset($brands[$brand_id]) ? "(ei merkki\xc3\xa4)" : $brands[$brand_id];
              $name = $person.": ".$name;
              $item = compact("vat", "sum", "name", "cnt");
              $pdf->items[] = $item;
            }
    usort($pdf->items, array($this, "sort_pdf_items"));

    foreach ($payments as $name => $sum)
      {
        $pdf->payment[] = compact("name", "sum");
      }

    $filename = "report-".($pdf->id).".pdf";
    $pdf->draw_invoice();

    $next = !empty($_GET["next"]) ? $_GET["next"] : "/report";
    $this->pdf_render($pdf, $action, $filename, $next);
  }

  private function sort_pdf_items($i1, $i2)
  {
    if ($i1["sum"] >= 0 && $i2["sum"] < 0)
      return -1;
    else if ($i1["sum"] < 0 && $i2["sum"] >= 0)
      return 1;
    return strcmp($i1["name"], $i2["name"]);
  }

  private function pdf_render($pdf, $action, $filename, $redirect=null)
  {
    if ($action == "print")
      {
        $data = $pdf->Output($filename, "S");
        $fd = popen("/usr/bin/lpr", "wb");
        fwrite($fd, $data);
        pclose($fd);
        $this->redirect($redirect);
      }
    else if ($action == "download")
      {
        $pdf->Output($filename, "D");
      }
    else if ($action == "png")
      {
        $f1 = tempnam("/tmp", "pos-receipt-");
        $f2 = tempnam("/tmp", "pos-receipt-");
        $data = $pdf->Output($f1, "F");
        system("/usr/bin/gs -q -sDEVICE=pnggray -dBATCH -dNOPAUSE -r300 -sOutputFile=".$f2." ".$f1);
        header("content-type: image/png");
        system("/usr/bin/gm convert -scale 500x $f2 png:-");
        unlink($f1);
        unlink($f2);
      }
    else
      {
        $pdf->Output($filename, "I");
      }
    $this->render(null, "ajax");
  }
}
