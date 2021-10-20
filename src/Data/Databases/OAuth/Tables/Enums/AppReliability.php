<?php

namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\Enums;

enum AppReliability: int
{
    case Distrusted = 0;
    case Trusted = 1;
}