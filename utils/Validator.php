<?php
class Validator
{
    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function validatePassword($password)
    {
        return strlen($password) >= 6 && strlen(trim($password)) > 0;
    }
}
