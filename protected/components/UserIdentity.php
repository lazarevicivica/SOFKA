<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
    private $_id;
    /**
     * @return boolean da li je autentifikacija uspela.
     * Ako je force true onda ne proverava lozinku i loguje korisnika.
     * Koristim pri aktivaciji naloga.
     */
    public function authenticate($force=false)
    {                           
            $record = Clan::model()->find('korisnicko_ime=:ime', array(':ime'=>$this->username));            
            if($record===null)
                $this->errorCode=self::ERROR_USERNAME_INVALID;
            elseif(!$record->aktivan)
                    $this->errorCode=self::ERROR_UNKNOWN_IDENTITY;
            elseif($record->lozinka !== hash('sha256',$this->password) && !$force)
                $this->errorCode=self::ERROR_PASSWORD_INVALID;
            else
            {
                $this->_id = $record->id;
                $this->errorCode=self::ERROR_NONE;
            }
            return !$this->errorCode;            
    }

    public function getId()
    {
            return $this->_id;
    }
}