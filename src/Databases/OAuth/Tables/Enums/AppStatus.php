<?php

namespace CarloNicora\Minimalism\Services\Auth\Databases\OAuth\Tables\Enums;

enum AppStatus: int
{
    case Inactive = 0;
    case Active = 1;
}