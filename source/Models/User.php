<?php

namespace Source\Models;

use CoffeeCode\DataLayer\DataLayer;
use Exception;

/**
 * Class User
 * @package Source\Models
 */
class User extends DataLayer
{
    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct(
            "user",
            ["first_name", "last_name", "email", "password"]
        );
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        if (
            !$this->validateEmail()
            || !$this->validatePassword()
            || !parent::save()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function validateEmail(): bool
    {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->fail = new Exception(
                "Por favor, informe um email válido para continuar"
            );
            return false;
        }

        $userByEmail = null;
        if (!$this->id) {
            $userByEmail = $this
                ->find("email = :email", "email={$this->email}")
                ->count();
        } else {
            $userByEmail = $this
                ->find("email = :email AND id != :id", "email={$this->email}&id={$this->id}")
                ->count();
        }

        if ($userByEmail) {
            $this->fail = new Exception(
                "O email informado já está em uso"
            );
            return false;
        }

        return true;
    }

    protected function validatePassword(): bool
    {
        if (strlen($this->password) < 5) {
            $this->fail = new Exception(
                "Informe uma senha com pelo menos 5 caracteres"
            );
            return false;
        }

        if (password_get_info($this->password)["algo"]) {
            return true;
        }

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        return true;
    }
}