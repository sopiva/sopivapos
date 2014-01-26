<?php

$months = array(
  "Tammikuu", "Helmikuu", "Maaliskuu", "Huhtikuu", "Toukokuu", "Kes\xc3\xa4kuu",
  "Hein\xc3\xa4kuu", "Elokuu", "Syyskuu", "Lokakuu", "Marraskuu", "Joulukuu");
$curr = "/report/index/$y-$m";
$next = ($m < 12) ? "$y-".($m+1) : ($y+1) . "-01";
$prev = ($m >  1) ? "$y-".($m-1) : ($y-1) . "-12";

// midori ei hanskaa pdfia
$midori = strpos($_SERVER["HTTP_USER_AGENT"], "midori") !== false;

?>
<style>
.rr li { font-family: monospace; }
#daylist { float: left; }
#prsales { float: left; padding-left: 2em; }
#prsales thead td { background: #aaa; }
#prsales tbody tr.row0 { background: #ccc; }
#prsales tbody tr.row1 { background: #eee; }
#prsales .amount { text-align: right; }
</style>
<?php

echo "<a href='/report/index/$prev'>&larr; edellinen</a>\n";
echo "<a href='/report/index/$next'>seuraava &rarr;</a>\n";
echo "<br />\n";
echo "<ul id='daylist'>\n";
$last_d = null;
$last_m = null;
foreach ($receipts as $r)
  {
    $tt = strtotime(substr($r["Receipt"]["time"], 0, 19));
    $date = date("d.m.Y", $tt);
    $dayI = date("Y-m-d", $tt);
    list($y,$m) = split("-", $dayI);
    $dayM = substr($dayI, 0, 4+1+2);
    $mony = $months[$m-1]." ".$y;
    if ($mony !== $last_m)
      {
        if (!empty($last_m))
          echo "</ul>\n</ul>\n";
        echo "<li> $mony ";
        echo "<a href='/report/report_view/$dayM'>[n&auml;yt&auml;]</a> ";
        if (!$midori)
          echo "<a href='/report/report_render/$dayM/pdf'>[pdf]</a> ";
        echo "<a href='/report/report_render/$dayM/print?next=$curr'>[tulosta]</a> ";
        echo "</li>\n";
        echo "<ul class='rr'>\n";
        $last_m = $mony;
        $last_d = null;
      }
    if ($date !== $last_d)
      {
        if (!empty($last_d))
          echo "</ul>\n";
        echo "<li> $date ";
        echo "<a href='/report/report_view/$dayI'>[n&auml;yt&auml;]</a> ";
        if (!$midori)
          echo "<a href='/report/report_render/$dayI/pdf'>[pdf]</a> ";
        echo "<a href='/report/report_render/$dayI/print?next=$curr'>[tulosta]</a> ";
        echo "</li>\n";
        echo "<ul class='rr'>\n";
        $last_d = $date;
      }
    $code = $r["Receipt"]["number"];
    echo "<li> $code ";
    echo "<a href='/report/receipt_view/".$r["Receipt"]["id"]."'>[n&auml;yt&auml;]</a> ";
    if (!$midori)
      echo "<a href='/report/receipt_render/".$r["Receipt"]["id"]."/pdf'>[pdf]</a> ";
    echo "<a href='/report/receipt_render/".$r["Receipt"]["id"]."/print?next=$curr'>[tulosta]</a> ";
    echo "<i>".$r["Receipt"]["person"]."</i>";
    echo "</li>\n";
  }
if (!empty($last_m))
  echo "</ul>\n";
if (!empty($last_d))
  echo "</ul>\n";
echo "</ul>\n";

if (!empty($sales))
  {
    echo "<table id='prsales'>\n";
    echo "<thead>\n";
    echo "<tr><td>Merkki</td><td>Tuote</td><td>M&auml;&auml;r&auml;</td><td>Summa</td></tr>";
    echo "</thead>\n";
    echo "<tbody>\n";
    $idx = 0;
    foreach ($brands as $bid => $brand)
      {
        if (empty($sales[$bid]))
          continue;
        ksort($sales[$bid]);
        foreach ($sales[$bid] as $name => $item)
          {
            echo "<tr class='row".($idx++%2)."'>\n";
            echo "<td>$brand</td><td>$name</td>\n";
            echo "<td class='amount'>".$item["count"]."</td>\n";
            echo "<td class='amount'>".t_price_out($item["sum"])."</td>\n";
            echo "</tr>\n";
          }
      }
    echo "</tbody>\n";
    echo "</table>\n";
  }

echo "<div style='clear: all'></div>\n";
