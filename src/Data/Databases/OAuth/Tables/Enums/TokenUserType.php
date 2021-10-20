<?php

namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\Enums;

enum TokenUserType: int
{
    case VISITOR = 0;
    case REGISTERED = 1;
}