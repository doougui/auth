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

        $checkUserEmail = (new User())
            ->find("email = :email", "email={$this->email}")
            ->count();

        if ($checkUserEmail) {
            $this->fail = new Exception(
                "Já existe um usuário cadastrado com este email"
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

        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        return true;
    }
}