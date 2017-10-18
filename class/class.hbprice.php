<?php
/**
 * @author Eren Özkul <info@erenozkul.com>
 * @version 1.0
 * @Date: 18.10.2017 14:12
 */


include "simple_html_dom.php";
error_reporting(0);

class HBPrice
{
    private $base = "";
    private $logdir = "";
    private $return = array();
    public $productList = array();

    public function __construct($productList = null, $recordDir = "records", $baseUrl = "http://www.hepsiburada.com/", $timezone = "Europe/Istanbul")
    {
        date_default_timezone_set($timezone);
        $this->logdir = $recordDir;
        $this->base = $baseUrl;
        if ($productList != null && is_array($productList)) {
            $this->productList = $productList;
        }
    }

    public function appendProduct($url)
    {
        if (is_array($url)) {
            foreach ($url as $u) {
                array_push($this->productList, $u);
            }
        } else {
            array_push($this->productList, $url);
        }
    }

    public function syncData($type = "json" | "array" | "xml", $proxy = false)
    {
        $this->return = array();
        if (is_array($this->productList) && !empty($this->productList)) {
            foreach ($this->productList as $product) {
                $product = trim($product);
                if (!empty($product)) {

                    $url = $this->base . $product;
                    if ($proxy == true) {
                        $html = $this->Curl($url, true);
                    } else {
                        $html = $this->Curl($url);
                    }
                    if (!isset($html["error"])) {
                        if (!str_get_html($html)->find("img[src=http://images.hepsiburada.net/assets/sfstatic/Content/images/404.jpg]")) {
                            $data = array();
                            $data["url"] = $url;
                            $data["date"] = date("d.m.Y H:i:s");
                            $data["name"] = trim(str_get_html($html)->find("h1[id=product-name]", 0)->innertext);
                            $data["oldPrice"] = trim(trim(str_get_html($html)->find("del[class=product-old-price]", 0)->plaintext, "TL"));
                            $data["price"] = explode(" ", trim(str_get_html($html)->find("span[id=offering-price]", 0)->plaintext));
                            $data["price"] = $data["price"][0] . $data["price"][1];
                            $data["discount"] = trim(str_get_html($html)->find("span[class=discount-amount]", 0)->children(0)->plaintext);
                            $this->elog(json_encode($data), date("dmYHis"), $this->seo($product));
                            $this->return[$product] = $data;
                        } else {
                            $this->return[$product] = array("error" => "Hatalı ürün");
                        }
                    } else {
                        $this->return[$product] = array("error" => "Sayfa bulunamadı");
                    }
                }

            }
        } else {
            $this->return = array("error" => "Ürün listesi boş");
        }
        return $this->ereturn($this->return, $type);
    }

    public function readData($dir = "all" | "productList", $type = "json" | "array" | "xml")
    {
        $this->return = array();
        if (is_array($this->productList) && !empty($this->productList)) {
            if ($dir == "all") {
                $products = array_filter(glob($this->logdir . "/*"), 'is_dir');

            } else {
                $products = $this->productList;
            }
            foreach ($products as $product) {
                $product = trim($product);
                if ($dir == "all") {
                    $product = basename($product);
                }

                $path = $this->logdir . "/" . $this->seo($product);
                if (is_dir($path)) {
                    $files = glob($path . '/*.json');
                    if (is_array($files) && !empty($files)) {
                        foreach ($files as $file) {
                            $pathinfo = pathinfo($file);
                            $this->return[$product][$pathinfo["filename"]] = json_decode(file_get_contents($file), true);
                        }
                    } else {
                        $this->return[$product] = array("error" => "Kayıt bulunamadı.");
                    }
                } else {
                    $this->return[$product] = array("error" => "Daha önce hiç senkronize edilmedi.");
                }
            }

        } else {
            $this->return = array("error" => "Ürün listesi boş");
        }
        return $this->ereturn($this->return, $type);
    }

    public function removeData($data = "all" | "productList" | "specificProducts", $type = "json" | "array" | "xml", $specificProducts = null)
    {
        $products = "";
        $this->return = array();
        if ($data == "all") {
            $products = array_filter(glob($this->logdir . "/*"), 'is_dir');
        } else if ($data == "productList") {
            $products = $this->productList;
        } else if ($data == "specificProducts" && $specificProducts != null) {
            $products = $specificProducts;
        } else {
            return array("error" => "Hatalı parametre");
        }
        foreach ($products as $product) {
            $product = $this->seo($product);
            if ($data != "all") {
                $product = $this->logdir . "/" . $product;
            }
            if (is_dir($product)) {
                array_map('unlink', glob($product . "/*.*"));
                if (rmdir($product)) {
                    $this->return[basename($product)] = array("error" => "Veri silindi.");
                } else {
                    $this->return[basename($product)] = array("error" => "Veri silinemedi..");
                }

            } else {
                $this->return[basename($product)] = array("error" => "Veri bulunamadı.");
            }
        }
        return $this->ereturn($this->return, $type);


    }


    private function Curl($url, $proxy = false)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false,
        );
        if ($proxy == true) {
            $proxyList = json_decode(file_get_contents(__DIR__ . "/proxy.json"), true);
            $p = rand(0, count($proxyList) - 1);
            array_push($options, array(CURLOPT_PROXY => $proxyList[$p]));
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['redirect'] = $redirectURL;
        $header['content'] = $content;
        if (empty($errmsg)) {
            return str_replace(array("\n", "\r", "\t"), NULL, $header['content']);
        } else {
            return array("error" => "code: " . $err . " message:" . $errmsg);
        }
    }

    private function seo($string)
    {   $s = $string;
        $tr = array('ş', 'Ş', 'ı', 'I', 'İ', 'ğ', 'Ğ', 'ü', 'Ü', 'ö', 'Ö', 'Ç', 'ç', '(', ')', '/', ':', ',');
        $eng = array('s', 's', 'i', 'i', 'i', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c', '', '', '-', '-', '');
        $s = str_replace($tr, $eng, $s);
        $s = strtolower($s);
        $s = preg_replace('/&amp;amp;amp;amp;amp;amp;amp;amp;amp;.+?;/', '', $s);
        $s = preg_replace('/\s+/', '-', $s);
        $s = preg_replace('|-+|', '-', $s);
        $s = preg_replace('/#/', '', $s);
        $s = trim($s, '-');
        return $s;
    }

    private function elog($record, $filename, $path = "")
    {
        if (!is_dir($this->logdir . "/" . $path)) {
            mkdir($this->logdir . "/" . $path, 0777);
        }
        $fpath = $this->logdir . "/" . $path . "/" . $filename . ".json";
        $file = file_exists($path) ? fopen($fpath, "a") : fopen($fpath, "x");
        fwrite($file, $record);
        fclose($file);
    }

    private function array_to_xml($array, &$xml_user_info)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml_user_info->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml_user_info->addChild("item");
                    $this->array_to_xml($value, $subnode);
                }
            } else {
                $xml_user_info->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    private function ereturn($data, $type = "json" | "array" | "xml")
    {
        if ($type == "json" or empty($type)) {
            return json_encode($data);
        } else if ($type == "array") {
            return $data;
        } else if ($type == "xml") {
            $xml = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
            $this->array_to_xml($data, $xml);
            return $xml->asXML();
        }
    }
}