<?php
namespace CarloNicora\Minimalism\Services\Auth\Models;

use CarloNicora\Minimalism\Services\Auth\Abstracts\AbstractAuthWebModel;

class Login extends AbstractAuthWebModel
{
    /** @var string  */
    protected string $viewName = 'login';
}