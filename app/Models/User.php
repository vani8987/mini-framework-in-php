<?php
namespace App\Models;

use Core\CRUD;
use Core\UserProviderInterface;

class User extends CRUD implements UserProviderInterface
{
    public function __construct()
    {
        parent::__construct('users');
    }

    public function findByEmail(string $email): ?array {
        if ($email === '') {
            $this->logger->warning('User lookup rejected: email is empty.');
            throw new \InvalidArgumentException('Email is empty.');
        }

        return $this->findOneBy(['id', 'password'], 'email', $email);
    }
}
