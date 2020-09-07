<?php

namespace Projeto\Model;

use Projeto\Mailer;
use \Projeto\DB\Sql;
use \Projeto\Model;

class Category extends Model{

    public static function listAll()
    {
        $sql = new Sql;
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    public function save()
    {
      $sql = new Sql();
      $results = $sql->select("CALL db_ecommerce.sp_categories_save(:idcategory, :descategory)", array(
                                                                                    ":idcategory"=>$this->getidcategory(),
                                                                                    ":descategory"=>$this->getdescategory()
                                                                                  ));
      $this->setData($results[0]);
      Category::updateFile();
    }
    public function get($idcategory)
    {
      $sql = new Sql();
      $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
        ":idcategory"=>$idcategory
      ));
      $this->setData($results[0]);
    }
    public function delete()
    {
      $sql = new Sql();
      $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory",
      [':idcategory'=>$this->getidcategory()]);
      Category::updateFile();
    }
    public static function updateFile()
    {
      $categories = Category::listAll();
      $html = [];
      foreach($categories as $data){ 
        array_push($html,'<li><a href="/categories/'.$data['idcategory'].'">'.$data['descategory'].'</a></li>');
      }
      file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views". DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
    }
}
