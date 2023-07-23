<?php

namespace App\Form\Security\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class OldPassword
{
    #[SecurityAssert\UserPassword(
        message: 'Wrong value for your current password',
        groups: ['ChangePassword']
    )]
    private string $oldPassword;

    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }
}
