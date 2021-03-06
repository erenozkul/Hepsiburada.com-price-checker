Hakkında
====================

Merhaba, bu proje hepsiburada.com'da ürünlerin fiyat takibini yapabilmek amacıyla oluşturulmuştur. diğediğiniz bir dizine(records) JSON formatta veri kaydı yapar.

Not: Kayıt yapılacak dizine(records) okuma/yazma izni vermeniz gerekmetedir. Aksi takdirde veri yazma-okuma problemi yaşayabilirsiniz.

3 formatta veri döndürebilirsiniz.
* JSON
* Array
* XML

Kullanımına aşağıdan bakabilirsiniz.

* Varsayılan olarak "records" dizinine kayıt yapar.
* Proxy kullanımı istek sürenizi uzatabilir.


Kullanım
=

<h4>Kurulum</h4>

 __construct fonksiyonu 4 parametre alabilir;
*   $productList : varsayılan olarak null değerini alır, istenirse önyüklemeli olarak örnekteki gibi ürün listesi eklenebilinir.
*   $recordDir : varsayılan olarak "records" değerini alır. veri kaydının yapılacağı dizini belirtir.
*   $baseUrl : varsayılan olarak "http://www.hepsiburada.com/" değerini alır. 
*   $timezone : varsayılan olarak "Europe/Istanbul" değerini alır. Veri kaydında kullanılacak zaman dilimini belirtir.

```php

include "class/class.hbprice.php";
$list = array(
    "apple-iphone-7-32-gb-apple-turkiye-garantili-p-HBV0000012S8F",
    "vestel-49ub8300-49-124-ekran-4k-smart-led-tv-p-HBV000002HL1J"
);
$hb = new HBPrice($list);



```


<h4>Veri Senkronizasyonu</h4>
@return JSON,Array,XML

Ürün listesindeki verilerin senkronizasyonunu gerçekleştirir ve istenilen veri tipinde geri döndürür

Parametreler;
* $type : "json","array" veya "xml" değerlerini alabilir. belirtilen tipte veri geri döndürür.
* $proxy : varsayılan olarak true olmak üzere true ve false değerlerini alabilir. true olması durumunda proxy.json içerisindeki rastgele bir ip adresi üzerinden veri iletişimi sağlar.
 
```php
$hb->syncData("json",true);
```


<h4>Veri Okuma</h4>
@return JSON,Array,XML

Ürün listesindeki ve ya tüm kayıt edilen verilerin okunmasını sağlar ve istenilen veri tipinde geri döndürür

Parametreler;

* $dir : "all" veya "productList" değerlerini alabilir.
* $type : "json","array" veya "xml" değerlerini alabilir. belirtilen tipte veri geri döndürür.

```php
$hb->readData("all","json");
```

<h4>Veri Silme</h4>
@return JSON,Array,XML

Ürün listesindeki, özel bir listedeki ve ya tüm kayıt edilen verilerin silinmesini sağlar ve istenilen veri tipinde geri döndürür

Parametreler;

* $data : "all","productList" veya "specificProducts" değerlerini alabilir.
* $type : "json","array" veya "xml" değerlerini alabilir. belirtilen tipte veri geri döndürür.
* $specificProducts : 1. parametre "specificProducts" olması durumunda bu parametre ile silinecek verilerin listesi verilmelidir.

```php
$hb->removeData("all","json");
$specificProducts = array(
    "apple-iphone-7-32-gb-apple-turkiye-garantili-p-HBV0000012S8F",
    "vestel-49ub8300-49-124-ekran-4k-smart-led-tv-p-HBV000002HL1J"
);
$hb->removeData("specificProducts","json",$specificProducts);
```


<h4>Veri Kayıt Örneği</h4>
* "special-discount" ve "special-price" alanları ürün için "Sepette %?? indirim" kampanyası olması durumunda hesaplanı ve yazdırılır.

```php
{
	"padisah-meri-full-ortopedik-luks-yatak-90-x-190-cm-p-HBV00000808O6": {
		"url": "http://www.hepsiburada.com/padisah-meri-full-ortopedik-luks-yatak-90-x-190-cm-p-HBV00000808O6",
		"date": "20.10.2017 00:20:47",
		"name": "Padişah Meri Full Ortopedik Lüks Yatak 90 x 190 cm",
		"oldPrice": 642.9,
		"price": 385.57,
		"discount": "40",
		"campaigns": [
			"Seçili Padişah yataklarda sepette %30 indirim + kargo bedava!",
			"Seçili yataklarda kargo bedava!",
			"Özdemirler Halı mağazasının seçili ürünlerinde kargo bedava!"
		],
		"special-discount": "30",
		"special-price": 269.9
	}
}
```