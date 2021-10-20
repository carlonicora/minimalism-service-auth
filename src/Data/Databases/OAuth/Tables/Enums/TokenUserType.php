<?php

namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\Enums;

enum TokenUserType: int
{
    case Visitor = 0;
    case Registered = 1;
}