<?php

namespace CarloNicora\Minimalism\Services\Auth\Data\Databases\OAuth\Tables\Enums;

enum AppReliability: int
{
    case DISTRUSTED = 0;
    case TRUSTED = 1;
}