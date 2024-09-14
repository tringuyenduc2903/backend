<?php

namespace App\Enums;

enum SettingTypeEnum: string
{
    case HOMEPAGE = 'homepage';

    case FOOTER = 'footer';

    case AUTH = 'auth';

    case STORE = 'store';
}
