<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="<?=t_href('/static/style.css')?>" />
  <title>POS</title>
</head>
<body>
  <img src="/static/logo-600px.png" />
  <hr size="1" />
  <?php
    echo $this->element("menu");
    $session->flash();
    echo $content_for_layout;
  ?>
  </body>
</html>
