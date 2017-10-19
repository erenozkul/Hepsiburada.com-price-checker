<?php

include "class/class.hbprice.php";
$list = array(
    "apple-iphone-7-32-gb-apple-turkiye-garantili-p-HBV0000012S8F"
);
$hb = new HBPrice($list);

$hb->appendProduct("padisah-meri-full-ortopedik-luks-yatak-90-x-190-cm-p-HBV00000808O6");
//echo $hb->syncData("json",true);
echo $hb->readData("productList","json");
//echo $hb->removeData("productList","json");