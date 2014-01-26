<?php

// Copyright (C) 2009-2012 Taisia Oy, Helsinki, Finland.
// All rights reserved.
//
// This file is under the GNU Lesser General Public License,
// version 2.1 or newer.  See the file `LICENSE` for details.

define("FPDF_FONTPATH", dirname(__FILE__) . "/fpdf-font/");
require_once dirname(__FILE__) . "/fpdf.php";

class TaisiaFPDF extends FPDF
{
  private $invoice;

  function __construct($iv, $unit="mm", $format="A4")
  {
    parent::__construct("P", $unit, $format);
    $this->AddFont(TAISIAFPDF_FONT,  "", TAISIAFPDF_FONT. ".php");
    $this->AddFont(TAISIAFPDF_FONT, "B", TAISIAFPDF_FONT."B.php");
    $this->AddFont(TAISIAFPDF_FONT, "I", TAISIAFPDF_FONT."I.php");
    $this->invoice = $iv;
  }

  function Header()
  {
    $this->invoice->Header($this);
  }

  function Footer()
  {
    $this->invoice->Footer($this);
  }

  // Filter through ghostscript to fix any errors and strip fonts
  function _enddoc()
  {
    parent::_enddoc();
    $cmdline = "/usr/bin/gs -q -dSAFER -dNOPAUSE -dBATCH -dCompatibilityLevel=1.4 -sDEVICE=pdfwrite -sOutputFile=- -c .setpdfwrite -f -";
    $rv = -1;
    $data = null;
    $descs = array(0 => array("pipe", "r"), 1 => array("pipe", "w"));
    $proc = proc_open($cmdline, $descs, $pipes, "/tmp");
    if (is_resource($proc))
      {
        fwrite($pipes[0], $this->buffer);
        fclose($pipes[0]);
        $data = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $rv = proc_close($proc);
      }
    if ($rv == 0 && !empty($data))
      {
        // add some metadata
        $x = strrpos($data, "/Producer");
        if ($x !== false)
          {
            // generate metadata, place it before /Producer
            $metadata = "";
            $metadata .= "/Title".$this->_textstring($this->title)."\n";
            $metadata .= "/Author".$this->_textstring($this->author)."\n";
            $metadata .= "/Creator".$this->_textstring("TaisiaInvoice 0.4")."\n";
            // Grab the part before /Producer, and the part after it
            $d1 = substr($data, 0, $x);
            $d2 = substr($data, $x);
            // Now check the length of data before xref table
            $x = strpos($d2, "xref") + strlen($d1) + strlen($metadata);
            // And replace the startxref pointer with the new count
            $y = strpos($d2, "startxref");
            $footer = "startxref\n$x\n%%EOF\n";
            $data = $d1 . $metadata . substr($d2, 0, $y) . $footer;
          }
        $this->buffer = $data;
      }
  }
}
