<?php

namespace Projeto\Model;

use Projeto\Mailer;
use \Projeto\DB\Sql;
use \Projeto\Model;

class User extends Model{

    const SESSION = "User";
    const SECRET = "senha_mt_segura";
    const SECRET_IV = "outra_senha_segura";

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
    public static function getForgot($email, $inadmin = true)
	{

		$sql = new Sql();

		$results = $sql->select("
                            SELECT *
                            FROM tb_persons a
                            INNER JOIN tb_users b USING(idperson)
                            WHERE a.desemail = :email;", array(":email"=>$email));

		if (count($results) === 0)
		{

			throw new \Exception("Não foi possível recuperar a senha.");

		}
		else
		{

			$data = $results[0];

			$results2 = $sql->select("CALL db_ecommerce.sp_userspasswordsrecoveries_create(
                                                                                    :iduser,
                                                                                    :desip)",
                                                                                    array(":iduser"=>$data['iduser'],
                                                                                        ":desip"=>$_SERVER['REMOTE_ADDR']));
			if (count($results2) === 0)
			{

				throw new \Exception("Não foi possível recuperar a senha.");

			}
			else
			{

				$dataRecovery = $results2[0];

				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

				if ($inadmin === true) {

					$link = "http://www.projetoecommerce.com.br/admin/forgot/reset?code=$code";

				} else {

					$link = "http://www.projetoecommerce.com.br/forgot/reset?code=$code";

				}

                $mailer = new Mailer($data['desemail'],
                                            $data['desperson'],
                                            "Redefinir senha",
                                            "forgot", array("name"=>$data['desperson'],
					                                        "link"=>$link));
				$mailer->send();
				return $link;
			}
		}
    }

	public static function validForgotDecrypt($code)
	{

		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
                            SELECT *
                            FROM tb_userspasswordsrecoveries a
                            INNER JOIN tb_users b USING(iduser)
                            INNER JOIN tb_persons c USING(idperson)
                            WHERE
                            a.idrecovery = :idrecovery
                            AND
                            a.dtrecovery IS NULL
                            AND
                            DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", array(":idrecovery"=>$idrecovery));
		if (count($results) === 0)
		{
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else
		{
			return $results[0];
		}
    }

    public static function setForgotUsed($idrecovery)
    {
        $sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));
    }
    public function setPassword($password)
    {
      $sql = new Sql();
  		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
  			":password"=>$password,
  			":iduser"=>$this->getiduser()
  	    ));
    }
}
