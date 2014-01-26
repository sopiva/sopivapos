<?php

// Copyright (C) 2005-2010 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

class TaisiaInvoiceI18N
{
  public $en = array();
  public $fi = array(
    "Bank account" => "Pankkiyhteys",
    "Breakdown of costs" => "Erittely",
    "Business ID" => "Y-tunnus",
    "Cost" => "Hinta",
    "Count" => "Kpl",
    "Date" => "P�iv�m��r�",
    "Due date" => "Er�p�iv�",
    "IBAN" => "IBAN",
    "Invoice" => "Lasku",
    "INVOICE" => "LASKU",
    "Invoice date" => "Laskun p�iv�m��r�",
    "Invoice ID" => "Viitenumero",
    "Item" => "Tuote",
    "Mailing list" => "L�hetyslista",
    "MAILING LIST" => "L�HETYSLISTA",
    "PAYMENT" => "TILITYS",
    "Payment" => "Tilitys",
    "Price" => "Hinta",
    "Receipt" => "Kuitti",
    "RECEIPT" => "KUITTI",
    "Subtotal" => "V�lisumma",
    "Summary" => "Yhteenveto",
    "Total excluding VAT" => "Veroton hinta yhteens�",
    "Total VAT" => "Arvonlis�vero yhteens�",
    "Total" => "Yhteens�",
    "VAT" => "ALV",
    "VAT number" => "ALV-tunnus",
    "Value added tax" => "Arvonlis�vero",
    "You were served by" => "Teit� palveli",
    );
  private $strings;

  function __construct($lang="fi")
  {
    if ($lang !== "fi")
      $lang = "en";
    $this->strings = $this->$lang;
  }

  function get_string($str)
  {
    if (isset($this->strings[$str]))
      return $this->strings[$str];
    return $str;
  }
}
