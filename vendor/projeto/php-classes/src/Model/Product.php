<?php

namespace Projeto\Model;

use Projeto\Mailer;
use \Projeto\DB\Sql;
use \Projeto\Model;

class Product extends Model{

    public static function listAll()
    {
        $sql = new Sql;
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");                   
    }
    public static function checkList($list)
    {
        foreach($list as &$row)
        {
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
        }
        return $list;
    }

    public function save()
    {
        $sql = new Sql();
        $results = $sql->select("CALL db_ecommerce.sp_products_save(
                                                                :idproduct, 
                                                                :desproduct,
                                                                :vlprice,
                                                                :vlwidth,
                                                                :vlheight,
                                                                :vllength,
                                                                :vlweight,
                                                                :desurl

                                                                )",array(
                                                                ':idproduct'=>$this->getidproduct(),
                                                                ':desproduct'=>$this->getdesproduct(),
                                                                ':vlprice'=>$this->getvlprice(),
                                                                ':vlwidth'=>$this->getvlwidth(),
                                                                ':vlheight'=>$this->getvlheight(),
                                                                ':vllength'=>$this->getvllength(),
                                                                ':vlweight'=>$this->getvlweight(),
                                                                ':desurl'=>$this->getdesurl()
                                                                
                                                                ));
        $this->setData($results[0]);
    }
    public function get($idproduct)
    {
      $sql = new Sql();
      $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", array(
        ":idproduct"=>$idproduct
      ));
      $this->setData($results[0]);
    }
    public static function getPage($page = 1, $itemsPerPage = 10)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products 
            ORDER BY desproduct
            LIMIT $start, $itemsPerPage;
        ");

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data'=>$results,
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];

    }
    public function delete()
    {
      $sql = new Sql();
      $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct",
      [':idproduct'=>$this->getidproduct()]);
    }
    public function checkPhoto()
    {
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
        "resources" . DIRECTORY_SEPARATOR . 
        "site" . DIRECTORY_SEPARATOR . 
        "img" . DIRECTORY_SEPARATOR . 
        "products" . DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg"))
        {
            $url = "/resources/site/img/products/" . $this->getidproduct() . ".jpg"; 
        }
        else
        {
            $url = "/resources/site/img/product.jpg";
        }
        return $this->setdesphoto($url);
    }
    public function getValues()
    {
        $this->checkPhoto();
        $values = parent::getValues();
        return $values;

    }
    public function setPhoto($file)
    {
        $extension = explode(".", $file['name']);
        $extension = end($extension);

        switch($extension)
        {
            case "jpg";

            case "jpeg";
                $img = imagecreatefromjpeg($file["tmp_name"]);
            break;

            case "gif";
                $img = imagecreatefromgif($file["tmp_name"]);
            break;

            case "png";
                $img = imagecreatefrompng($file["tmp_name"]);
            break;
        }
        $dest = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
                "resources" . DIRECTORY_SEPARATOR . 
                "site" . DIRECTORY_SEPARATOR . 
                "img" . DIRECTORY_SEPARATOR . 
                "products" . DIRECTORY_SEPARATOR . $this->getidproduct() . ".jpg";

        imagejpeg($img, $dest);
        imagedestroy($img);
        $this->checkPhoto();
    }

}
