<?php

namespace Projeto\Model;

use \Projeto\DB\Sql;
use \Projeto\Model;

class User extends Model{

    const SESSION = "User";
    public static function login($login, $password)
    {
      $sql = new Sql();

      $res = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
          ":LOGIN"=>$login
      ));
      //Checking if something was found
      
      if(count($res) === 0){
          throw new \Exception("Invalid login or password. Check your data. ");
      }
      $data = $res[0];
      
      //Checking user password
      //this function returns true or false
      if(password_verify($password, $data["despassword"]) === true ){
        $user = new User;
        $user->setData($data);
        return $user;
        $_SESSION[User::SESSION] = $user->getValues();
        exit;
        
      }else{
          throw new \Exception("Invalid login or password. Check your data. ");
      }     
          
    }
    public static function verifyLogin($inadmin = true)
    {
        if
        (
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        )
        {
            header("Location: /admin/login");
            exit;
        }
    }
}