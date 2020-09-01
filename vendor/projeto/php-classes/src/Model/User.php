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
        $_SESSION[User::SESSION] = $user->getValues();
        return $user;
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
    public static function logout()
    {
        unset($_SESSION[User::SESSION]);
    }
    public static function listAll()
    {
        $sql = new Sql;
        return $sql->select("SELECT * "
                            . "FROM tb_users "
                            . "INNER JOIN tb_persons "
                            . "ON tb_persons.idperson = tb_users.idperson "
                            . "ORDER BY tb_persons.desperson");     
    }
    public function get($iduser)
    {
    
        $sql = new Sql();
        
        $results = $sql->select("SELECT * 
                                FROM tb_users a 
                                INNER JOIN tb_persons b 
                                USING(idperson) 
                                WHERE a.iduser = :iduser", array(
                                                            ":iduser"=>$iduser
                                                           ));
        $this->setData($results[0]);
    
    }
    public function saveData()
    {
        /*
        pdesperson VARCHAR(64),
        pdeslogin VARCHAR(64),
        pdespassword VARCHAR(256),
        pdesemail VARCHAR(128),
        pnrphone BIGINT,
        pinadmin TINYINT)
        */
        $sql = new Sql();
        //procedure call
        $results = $sql->select("CALL  db_ecommerce.sp_users_save(:desperson, 
                                                                :deslogin, 
                                                                :despassword, 
                                                                :desemail, 
                                                                :nrphone, 
                                                                :inadmin)",
                                                                array
                                                                (
                                                                    ":desperson"=>$this->getdesperson(),
                                                                    ":deslogin"=>$this->getdeslogin(),
                                                                    ":despassword"=>password_hash($this->getdespassword(), PASSWORD_DEFAULT,['cont'=>12]),
                                                                    ":desemail"=>$this->getdesemail(),
                                                                    ":nrphone"=>$this->getnrphone(),
                                                                    ":inadmin"=>$this->getinadmin()
                                                                ));
        $this->setData($results[0]);
    }
    public function update()
    {
        $sql = new Sql();
        $results = $sql->select("CALL  db_ecommerce.sp_usersupdate_save(:iduser, 
                                                                        :desperson, 
                                                                        :deslogin, 
                                                                        :despassword, 
                                                                        :desemail, 
                                                                        :nrphone, 
                                                                        :inadmin
                                                                        )",
                                                                        array
                                                                        (
                                                                            ":iduser"=>$this->getiduser(),
                                                                            ":desperson"=>$this->getdesperson(),
                                                                            ":deslogin"=>$this->getdeslogin(),
                                                                            ":despassword"=>password_hash($this->getdespassword(), PASSWORD_DEFAULT,['cont'=>12]),
                                                                            ":desemail"=>$this->getdesemail(),
                                                                            ":nrphone"=>$this->getnrphone(),
                                                                            ":inadmin"=>$this->getinadmin()
                                                                        ));
        $this->setData($results[0]);
    }
    public function delete()
    {
        $sql = new Sql();
        $sql->query("CALL  db_ecommerce.sp_users_delete(:iduser)", array
                                                                (
                                                                    ":iduser"=>$this->getiduser()
                                                                ));
    }
    
}