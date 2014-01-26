<?php

// Copyright (C) 2009-2011 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

class ShopController extends AppController
{
  public $uses = array(
    "Receipt", "ReceiptPayment", "ReceiptItem",
    "Brand", "Product",
    "Option");

  function beforeFilter()
  {
    parent::beforeFilter();
    $this->checkSession();
    $this->set("admin", $this->auth_user["admin"]);
  }

  function index($sel_brand=0)
  {
    $this->default_vat = $this->Option->get("default_vat");

    $q = $this->Brand->find("all", array(
      "conditions" => "inactive=0",
      "order" => "name ASC"));
    $brands = array();
    $products = array();
    foreach ($q as $r)
      {
        $bid = $r["Brand"]["id"];
        $brands[$bid] = $r["Brand"];
        $brands[$bid]["products"] = array();
        foreach ($r["Product"] as $sr)
          {
            $pcode = $sr["code"];
            $brands[$bid]["products"][] = $pcode;
            $products[$pcode] = $sr;
          }
      }
    $this->set("brands", $brands);
    $this->set("products", $products);
    $this->set("sel_brand", (int) $sel_brand);
    $this->set("default_vat", $this->default_vat);
  }

  function receipt_save()
  {
    $this->default_vat = $this->Option->get("default_vat");
    $receipt = $this->data["receipt"];
    $items_tmp = $this->data["items"];
    $pay_tmp = !empty($this->data["payment"]) ? $this->data["payment"] : null;

    $items = array();
    $brands = array(); // brand_id -> sum (for discounts)
    $total = 0;
    foreach ($items_tmp as $item)
      {
        if (empty($item["name"]))
          continue;
        $sum = (int) t_price_in($item["sum"]);
        $cnt = (int) $item["cnt"];
        if (empty($sum) || $cnt < 1)
          continue;
        $brand_id = (int) $item["brand_id"];
        $vat = (int) $item["vat"];
        $name = $item["name"];
        $items[] = compact("brand_id", "cnt", "sum", "vat", "name");
        $tsum = ( ($cnt > 1) ? $cnt : 1 ) * $sum;
        $total += $tsum;
        if (!isset($brands[$brand_id]))
          $brands[$brand_id] = 0;
        $brands[$brand_id] += $tsum;
      }

    // no items or payment?  return.
    if (empty($items) || empty($pay_tmp))
      {
        $this->redirect("/shop");
        return;
      }

    if ($receipt["discount"] > 0)
      {
        $q = $this->Brand->find("all", array(
          "conditions" => "id IN (".join(",",array_keys($brands)).")",
          "order" => "name ASC"));
        $brand_names = array();
        foreach ($q as $r)
          $brand_names[$r["Brand"]["id"]] = $r["Brand"]["name"];
        foreach ($brands as $brand_id => $brand_total)
          {
            $discount = round($brand_total * $receipt["discount"] / 100);
            $discount -= $discount % 5; // round to 5 cents
            $items[] = array(
              "brand_id" => $brand_id,
              "cnt" => 1,
              "name" => "Alennus ".$receipt["discount"]." % (".$brand_names[$brand_id].")",
              "vat" => $this->default_vat,
              "sum" => -$discount);
            $total -= $discount;
          }
      }

    $payment = array();
    foreach ($pay_tmp as $e)
      if (!empty($e["name"]))
        {
          $sum = (int) t_price_in($e["sum"]);
          if (empty($sum))
            $sum = $total;
          $payment[] = array("sum" => $sum, "name" => $e["name"]);
        }

    $q = $this->Receipt->query("SELECT MAX(day_id) FROM receipts WHERE time::date=now()::date");
    $receipt["day_id"] = 1 + (int) $q["0"]["0"]["max"];
    $receipt["number"] = $this->Receipt->receipt_number($receipt["day_id"], time());
    $receipt["person"] = $this->get_user_name();
    $this->Receipt->create();
    $this->Receipt->save($receipt);
    $id = $this->Receipt->getLastInsertID();

    foreach ($items as $row)
      {
        $row["receipt_id"] = $id;
        if (empty($row["brand_id"]))
          unset($row["brand_id"]);
        $this->ReceiptItem->create();
        $this->ReceiptItem->save($row);
      }

    foreach ($payment as $row)
      {
        $row["receipt_id"] = $id;
        $this->ReceiptPayment->create();
        $this->ReceiptPayment->save($row);
      }

    $this->redirect("/report/receipt_render/".((int)$id)."/print?next=/shop");
  }

  function product_save($code=0)
  {
    $brand_id = (int) $_GET["brand_id"];
    $price = (int) $_GET["price"];
    $name = $_GET["name"];
    if (isset($_GET["nameenc"]) && $_GET["nameenc"] == "latin1")
      $name = utf8_encode($name);
    if (!empty($code))
      {
        $code = (int) $code;
        $pr = $this->Product->findByCode($code);
        if (!empty($pr))
          {
            $id = $pr["Product"]["id"];
            $pr = compact("id", "code", "name", "price", "brand_id");
            $this->Product->save($pr);
          }
      }
    else
      {
        $id = $this->Product->create($brand_id, $name, $price);
      }

    if (!empty($id))
      {
        $pr = $this->Product->findById($id);
        if (!empty($pr))
          {
            $this->set("code", $pr["Product"]["code"]);
          }
      }
    $this->render(null, "ajax");
  }

  function product_delete($code=0)
  {
    $pr = $this->Product->findByCode((int) $code);
    if (!empty($pr))
      $this->Product->delete($pr["Product"]["id"]);
    $this->redirect("/shop");
  }

  function brand_save($id=0)
  {
    $this->checkAdmin();
    $name = $_GET["name"];
    $abbr = $_GET["abbr"];
    if (isset($_GET["nameenc"]) && $_GET["nameenc"] == "latin1")
      $name = utf8_encode($name);
    if (isset($_GET["abbrenc"]) && $_GET["abbrenc"] == "latin1")
      $abbr = utf8_encode($abbr);
    $this->Brand->save(compact("abbr", "name"));
    $id = $this->Brand->getLastInsertID();
    $this->redirect("/shop/index/".$id);
  }

  function brand_delete($id=0)
  {
    $this->checkAdmin();
    $pr = $this->Brand->query("UPDATE brands SET inactive=1 WHERE id=".((int) $id));
    $this->redirect("/shop");
  }
}
