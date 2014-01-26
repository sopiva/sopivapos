<?php

$optnames = array(
  "default_vat" => "Arvonlis\xc3\xa4vero",
  );

echo $form->create("Option", array("url" => "/admin/save_options"));
foreach ($opts as $opt)
  {
    extract($opt["Option"]);
    echo isset($optnames[$key]) ? $optnames[$key] : $key, "\n";
    echo $form->text($key, array("value" => $val, "size" => 10));
    echo "<br />\n";
  }
echo $form->submit("Tallenna");
echo $form->end();
