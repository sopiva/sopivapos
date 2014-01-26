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
    "Date" => "Päivämäärä",
    "Due date" => "Eräpäivä",
    "IBAN" => "IBAN",
    "Invoice" => "Lasku",
    "INVOICE" => "LASKU",
    "Invoice date" => "Laskun päivämäärä",
    "Invoice ID" => "Viitenumero",
    "Item" => "Tuote",
    "Mailing list" => "Lähetyslista",
    "MAILING LIST" => "LÄHETYSLISTA",
    "PAYMENT" => "TILITYS",
    "Payment" => "Tilitys",
    "Price" => "Hinta",
    "Receipt" => "Kuitti",
    "RECEIPT" => "KUITTI",
    "Subtotal" => "Välisumma",
    "Summary" => "Yhteenveto",
    "Total excluding VAT" => "Veroton hinta yhteensä",
    "Total VAT" => "Arvonlisävero yhteensä",
    "Total" => "Yhteensä",
    "VAT" => "ALV",
    "VAT number" => "ALV-tunnus",
    "Value added tax" => "Arvonlisävero",
    "You were served by" => "Teitä palveli",
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
