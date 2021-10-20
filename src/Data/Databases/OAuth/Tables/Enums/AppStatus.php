<?php

namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\Enums;

enum AppStatus: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;
}