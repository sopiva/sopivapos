<form method="post" action="/shop/receipt_save" id="receiptform" onsubmit="return check_for_submit()">

<div class="mleft">

<table class="itemtable">
  <thead>
    <tr>
      <td>KPL</td>
      <td>Merkki</td>
      <td>Tuote</td>
      <td>Hinta &euro;</td>
      <td>Vero %</td>
    </tr>
  </thead>
  <tbody id="itemtbody"></tbody>
  <tbody class="itemtotal">
    <tr>
      <td colspan="2"><input type="button" value="Lis&auml;&auml; rivi" onclick="item_add_row()" /></td>
      <td>Yhteens&auml; (<a title='Aseta alennus %' href='javascript:void(set_discount())'>ale <span id='disco_text'>0</span>%</a>)</td>
      <td id="itemtotaltd">0</td>
      <td></td>
    </tr>
  </tbody>
</table>

<br />

<div class='paybtn'>
<input type="button" value="Maksu: K&auml;teinen" onclick="cash_payment()" />
<input type="button" value="Maksu: Pankkikortti" onclick='card_payment("Pankkikortti")' />
<br />
<input type="button" value="Maksu: VISA Electron" onclick='card_payment("VISA Electron")' />
<input type="button" value="Maksu: Maestro" onclick='card_payment("Maestro")' />
<br />
<input type="button" value="Maksu: VISA Debit" onclick='card_payment("VISA Debit")' />
<input type="button" value="Maksu: MasterCard Debit" onclick='card_payment("MasterCard Debit")' />
<br />
<input type="button" value="Maksu: VISA Credit" onclick='card_payment("VISA Credit")' />
<input type="button" value="Maksu: MasterCard Credit" onclick='card_payment("MasterCard Credit")' />
</div>

</div> <!-- mleft -->

<div class="mright">

<table class="producttable">
  <thead>
    <tr>
      <td colspan="2">
        <?php
          $c = 0;
          foreach ($brands as $id => $b)
            {
              $c++;
              echo "<span class='brand' id='brand-$id'><a href='javascript:void(update_products($id))'>".t_escape($b["name"])."</a></span>\n";
              if ($c%3 == 0)
                echo "<br />\n";
            }
        ?>
      </td>
    </tr>
  </thead>
  <tbody id="producttbody"></tbody>
</table>

</div> <!-- mright -->

<div class="clear"></div>

<br />


<input type="hidden" name="data[receipt][discount]" value="0" id="disco" />

<input type="hidden" id="receipt_submitted" value="0" />

</form>

<?php

echo "<script language='javascript' type='text/javascript'>\n";
echo "var tbrands = {";
$first = true;
foreach ($brands as $id => $r)
  {
    if (!$first) echo ","; else $first = false;
    echo "\n  $id : [\"".addslashes($r["abbr"])."\", \"".addslashes($r["name"])."\", [".join(",",$r["products"])."]]";
  }
echo "\n};\n";
echo "var tproducts = {";
$first = true;
foreach ($products as $id => $r)
  {
    if (!$first) echo ","; else $first = false;
    echo "\n  $id : [".$r["brand_id"].", \"".addslashes($r["name"])."\", ".$r["price"]."]";
  }
echo "\n};\n";
echo "</script>\n";
?>

<script language="javascript" type="text/javascript">
<!--
var last_iid=0;
var last_pid=0;
var sel_brand=0;

function parse_sum(isum)
{
  if (isum == "")
    return 0;
  var q = isum.indexOf(",");
  if (q == -1)
    q = isum.indexOf(".");
  if (q != -1)
    {
      var e = parseInt(isum.substring(0,q));
      var s = isum.substring(q+1,q+3);
      if (s.length == 1)
        s = parseInt(s) * 10;
      else
        s = parseInt(s);
      if (isNaN(e) || isNaN(s))
        return 0;
      return e * 100 + ((e < 0) ? - s : s);
    }
  else
    {
      q = parseInt(isum);
      if (isNaN(q))
        return 0;
      return 100 * q;
    }
}

function format_sum(n)
{
  if (n == 0)
    return "0";
  var m = "" + n;
  return m.substring(0,m.length-2) + "," + m.substring(m.length-2);
}

function calc_total()
{
  var total, isum, icnt, iname, sum, disco;
  total = 0;
  for (var i=0; i<=last_iid; i++)
    {
      iname = document.getElementById("INAME"+i);
      if (!iname || iname.value == "")
        continue;
      isum = parse_sum(document.getElementById("ISUM"+i).value);
      icnt = document.getElementById("ICNT"+i).value;
      sum = isum * icnt;
      total += sum;
    }
  disco = parseInt(document.getElementById("disco").value);
  if (disco)
    {
      disco = Math.round(total * disco / 100);
      disco -= disco%5;
      total -= disco;
    }
  return total;
}

function check_for_submit()
{
  if (check_receipt())
    return false;
  if (document.getElementById("payment_1") == null)
    {
      alert("Maksutapaa ei ole valittu!");
      return false;
    }
  return true;
}

function check_receipt()
{
  var iname, isum;
  for (var i=0; i<=last_iid; i++)
    {
      iname = document.getElementById("INAME"+i);
      if (!iname || iname.value == "")
        continue;
      isum = parse_sum(document.getElementById("ISUM"+i).value);
      if (isum == 0)
        {
          alert("Tuotteelle "+(iname.value)+" ei ole annettu hintaa!");
          return true;
        }
      if (document.getElementById("IBRAND"+i).selectedIndex == 0)
        {
          alert("Tuotteelle "+(iname.value)+" ei ole valittu merkkii!");
          return true;
        }
    }
  return false;
}

function cash_payment()
{
  if (check_receipt())
    return;
  var total = calc_total();
  var payment = prompt("Yht. "+format_sum(total)+" EUR, maksu:");
  payment = parse_sum(payment);
  if (payment != 0)
    {
      var takas = payment-total;
      alert("Takaisin: "+format_sum(takas));
      add_payment(format_sum(payment), "<?=utf8_encode('Käteinen')?>");
      if (takas != 0)
        add_payment(format_sum(takas), "Takaisin");
    }
  else
    {
      add_payment(0, "<?=utf8_encode('Käteinen')?>");
    }
  submit_receipt();
}

function card_payment(pname)
{
  if (check_receipt())
    return;
  add_payment(0,pname);
  submit_receipt();
}

function submit_receipt()
{
  if (document.getElementById("receipt_submitted").value == 1)
    return;
  document.getElementById("receipt_submitted").value = 1;
  document.getElementById("receiptform").submit();
}

function set_discount()
{
  var disco_old = parseInt(document.getElementById("disco").value);
  var disco_new = prompt("Alennus %", disco_old);
  if (disco_new == "" || disco_new == null)
    return;
  disco_new = parseInt(disco_new);
  if (isNaN(disco_new))
    disco_new = 0;
  document.getElementById("disco").value = disco_new;
  document.getElementById("disco_text").innerHTML = disco_new;
  update_total_td();
}

function update_total_td()
{
  var total = calc_total();
  var td = document.getElementById("itemtotaltd");
  td.innerHTML = format_sum(total);
}

function update_name(ob,id)
{
  if (ob.value.indexOf("#") == 0)
    {
      code = parseInt(ob.value.substring(1));
      if (!isNaN(code))
        if (add_product(code, id))
          return;
    }
  update_total_td();
}

function update_cnt(ob,id)
{
  if (parseInt(ob.value) >= 1)
    update_total_td();
  else
    reset_row(id);
}

function create_item_field(parent, type, dom_id, item_id, name)
{
  var ob = document.createElement(type);
  ob.name = "data[items]["+item_id+"]["+name+"]";
  ob.id = dom_id + item_id;
  parent.appendChild(ob);
  return ob;
}

function item_add_row()
{
  var id, tb, tr, td, ob, opt, b;

  id = ++last_iid;
  tb = document.getElementById("itemtbody");
  tr = document.createElement("tr");

  td = document.createElement("td");
  ob = document.createElement("span");
  //ob.innerHTML = "<img src='/static/plus.png' alt='+' /><img src='/static/minus.png' alt='-' />";
  //td.appendChild(ob);
  ob = create_item_field(td, "input", "ICNT", id, "cnt");
  ob.size = "3"
  ob.value = "1";
  ob.onchange = function() { update_cnt(this, id); }
  tr.appendChild(td);

  td = document.createElement("td");
  ob = create_item_field(td, "select", "IBRAND", id, "brand_id");
  opt = document.createElement("option");
  opt.value = "";
  opt.innerHTML = "-- valitse --";
  ob.appendChild(opt);
  for (b in tbrands)
    {
      opt = document.createElement("option");
      opt.value = b;
      opt.innerHTML = tbrands[b][0];
      ob.appendChild(opt);
    }
  tr.appendChild(td);

  td = document.createElement("td");
  ob = create_item_field(td, "input", "INAME", id, "name");
  ob.setAttribute("class", "itemname");
  ob.onchange = function() { update_name(this, id); }
  tr.appendChild(td);

  td = document.createElement("td");
  ob = create_item_field(td, "input", "ISUM", id, "sum");
  ob.size = "5";
  ob.onchange = function() { update_total_td(); }
  tr.appendChild(td);

  td = document.createElement("td");
  ob = create_item_field(td, "input", "IVAT", id, "vat");
  ob.size = "3";
  ob.value = "<?= $default_vat ?>";
  ob = document.createElement("span");
  ob.innerHTML = "<a class='prod-act' href='javascript:void(reset_row("+id+"))'><img src='/static/delete.png' alt='delete' /></a>";
  td.appendChild(ob);
  tr.appendChild(td);

  tb.appendChild(tr);
}

function add_payment(sum,name)
{
  var id = ++last_pid;
  var f = document.getElementById("receiptform");
  var s = document.createElement("input");
  s.id = "payment_"+id;
  s.type = "hidden";
  s.name = "data[payment]["+id+"][sum]";
  s.value = sum;
  f.appendChild(s);
  s = document.createElement("input");
  s.type = "hidden";
  s.name = "data[payment]["+id+"][name]";
  s.value = name;
  f.appendChild(s);
}

function add_product(code, i)
{
  var iname, ibrand, isum, icnt, cnt, pp;
  pp = tproducts[code];
  if (pp == null)
    return 0;
  if (i == 0)
    {
      var found = 0;
      for (i=1; i<=last_iid; i++)
        {
          iname = document.getElementById("INAME"+i);
          if (!iname)
            break;
          if (iname.value == pp[1])
            {
              // matching name -- see if brand and price match
              isum = document.getElementById("ISUM"+i);
              ibrand = document.getElementById("IBRAND"+i);
              if (parse_sum(isum.value) == pp[2] && ibrand.options[ibrand.selectedIndex].value == pp[0])
                {
                  icnt = document.getElementById("ICNT"+i);
                  cnt = parseInt(icnt.value);
                  if (!isNaN(cnt))
                    {
                      icnt.value = cnt+1;
                      update_total_td();
                      return 1;
                    }
                }
            }
          if (iname.value != "")
            continue;
          found = 1;
          break;
        }
      if (!found)
        {
          item_add_row();
          i = last_iid;
        }
    }
  iname = document.getElementById("INAME"+i);
  isum = document.getElementById("ISUM"+i);
  ibrand = document.getElementById("IBRAND"+i);
  iname.value = pp[1];
  if (pp[2] == 0)
    isum.value = format_sum(parse_sum(prompt("Tuotteen hinta")));
  else
    isum.value = format_sum(pp[2]);
  for (i=0; i<ibrand.options.length; i++)
    if (ibrand.options[i].value == pp[0])
      {
        ibrand.selectedIndex = i;
        break;
      }
  update_total_td();
  return 1;
}

function update_products(br)
{
  var p, tb, tr, td, str, le, ld;
  tb = document.getElementById("producttbody");
  while (tb.firstChild)
    tb.removeChild(tb.firstChild);
  for (p in tbrands[br][2])
    {
      p = tbrands[br][2][p];
      tr = document.createElement("tr");
      td = document.createElement("td");
      str = "#" + p + " " + tproducts[p][1];
      td.innerHTML = "<a href='javascript:void(add_product("+p+",0))'>"+str+"</a>";
      tr.appendChild(td);
      td = document.createElement("td");
      td.setAttribute("style", "text-align: right; white-space: nowrap");
      le = "<a class='prod-act' title='Muokkaa' href='javascript:void(edit_product("+br+", "+p+"))'><img src='/static/edit.png' alt='edit' /></a>";
      ld = "<a class='prod-act' title='Poista'  href='javascript:void(delete_product("+p+"))'><img src='/static/delete.png' alt='delete' /></a>";
      td.innerHTML = format_sum(tproducts[p][2]) + " " + le + ld;
      tr.appendChild(td);
      tb.appendChild(tr);
    }
  tr = document.createElement("tr");
  td = document.createElement("td");
  td.setAttribute("colspan", 2);
  span = document.createElement("span");
  str = "Uusi tuote";
  span.innerHTML = "<a href='javascript:void(edit_product("+br+",0))'>"+str+"</a>";
  td.appendChild(span);
  if ("<?= $admin ?>" == "1")
    {
      span = document.createElement("span");
      str = "Uusi tuotemerkki";
      span.innerHTML = " | <a href='javascript:void(create_brand())'>"+str+"</a>";
      td.appendChild(span);
      span = document.createElement("span");
      str = "Poista tuotemerkki";
      span.innerHTML = " | <a href='javascript:void(delete_brand("+br+"))'>"+str+"</a>";
      td.appendChild(span);
    }
  tr.appendChild(td);
  td = document.createElement("td");
  tr.appendChild(td);
  tb.appendChild(tr);

  // selected
  p = document.getElementById("brand-"+sel_brand);
  if (p)
    p.setAttribute("class", "brand");
  sel_brand = br;
  p = document.getElementById("brand-"+sel_brand);
  p.setAttribute("class", "brand brand-selected");
}

function product_save_return(req, code, br, nn, pp)
{
  if (! (req.readyState == 4 && req.status == 200))
    return;

  var t = req.responseText;
  if (t.indexOf("OK:") != 0)
    {
      alert("Tuotteen luonti ei onnistunut :-(");
      return;
    }
  var newcode = t.substring(3);
  if (newcode != code)
    {
      code = newcode;
      tbrands[br][2].push(code);
    }
  tproducts[code] = [br,nn,pp];
  update_products(br);
}

function edit_product(br, code)
{
  var on = "", op = "";
  if (code)
    {
      on = tproducts[code][1];
      op = format_sum(tproducts[code][2]);
    }
  var nn = prompt("Tuotteen nimi", on);
  if (nn == "" || nn == null)
    return;
  var pp = prompt("Tuotteen hinta", op);
  if (pp == "" || pp == null)
    return;
  pp = parse_sum(pp);

  // alert("Uusi tuote ["+br+"] ["+nn+"] ["+parse_sum(pp)+"]");
  var url = "<?=$html->url('/shop/product_save')?>/"+code+"?brand_id="+br+"&price="+pp+"&nameenc=latin1&name="+escape(nn);
  try { req = new XMLHttpRequest(); } catch (e) { }
  if (!req)
    {
      alert("XMLHttpRequest support missing :-(");
      return null;
    }
  if (req.overrideMimeType)
    {
      req.overrideMimeType("text/text");
    }

  req.onreadystatechange = function() { product_save_return(req, code, br, nn, pp); };
  req.open("GET", url, true);
  req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  req.send(null);
}

function delete_product(id)
{
  if (! confirm("Haluatko varmasti poistaa tuotteen?"))
    return;
  document.location.href = "<?=$html->url('/shop/product_delete')?>/"+id;
}

function create_brand()
{
  var nn = prompt("Tuotemerkin nimi");
  if (nn == "" || nn == null)
    return;

  var na = prompt("Tuotemerkin lyhenne");
  if (na == "" || na == null)
    return;

  var url = "<?=$html->url('/shop/brand_save')?>?nameenc=latin1&name="+escape(nn)+"&abbrenc=latin1&abbr="+escape(na);
  document.location.href = url;
}

function delete_brand(id)
{
  if (! confirm("Haluatko varmasti poistaa tuotemerkin?"))
    return;
  document.location.href = "<?=$html->url('/shop/brand_delete')?>/"+id;
}

function reset_row(id)
{
  document.getElementById("ICNT"+id).value = "1";
  document.getElementById("IBRAND"+id).selectedIndex = 0;
  document.getElementById("IVAT"+id).value = "<?= $default_vat ?>";
  document.getElementById("INAME"+id).value = "";
  document.getElementById("ISUM"+id).value = "";
  update_total_td();
}

for (i=0; i<5; i++)
  {
    item_add_row();
  }

<?php
if (!empty($sel_brand))
  echo "update_products($sel_brand);\n";
?>
//-->
</script>
