<?php


namespace App\Roles;


use Symfony\Component\Security\Core\Security;

class CheckRoles
{
    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }


    public function checkroles()
    {
        $user = $this->security->getUser();
        if ($user->getRoles() == ["ROLE_ADMIN"]) {
            $route = 'app_admin_index';
        } else if ($user->getRoles() == ["ROLE_USER"]) {
            $route = 'app_user_index';
        }
        return $route;
    }
}
