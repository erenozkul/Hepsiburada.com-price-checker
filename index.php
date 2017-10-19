<?php

include "class/class.hbprice.php";
$list = array(
    "apple-iphone-7-32-gb-apple-turkiye-garantili-p-HBV0000012S8F"
);
$hb = new HBPrice($list);


$hb->appendProduct("profilo-cmg140dtr-premium-9-a-9-kg-1400-devir-camasir-makinesi-p-MTPROFCGM1400DTR");
//echo $hb->syncData("json",true);
//echo $hb->readData("productList","json");
//echo $hb->removeData("productList","json");