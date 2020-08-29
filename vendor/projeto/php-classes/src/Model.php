<?php

namespace Projeto;

class Model{
    private $values = [];
    
    //name = chave, args = valor
    public function __call($name, $args)
    {
        //if the firsts three letters are g-e-t or s-e-t, the command will be
        //executed according to the  method
        //A partir da posicao 0, traga 0,1,2
        $method = substr($name, 0, 3);
        $fieldName = substr($name, 3,strlen($name));
      
        switch ($method)
        {
            case "get":
                return $this->values[$fieldName];
            break;

            case "set":
                $this->values[$fieldName] = $args[0];
            break;
        }
        
    }
    public function setData($data = array()){
        foreach($data as $key => $values){
            $this->{"set".$key}($values);
        }
    }
    public function getValues(){
        return $this->values;
    }
}