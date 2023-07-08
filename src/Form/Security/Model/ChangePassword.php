<?php

namespace App\Form\Security\Model;

use Symfony\Component\Validator\Constraints\Length;

class ChangePassword extends OldPassword
{
    #[Length(min: 6)]
    private string $newPassword;

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }
}
