<?php

namespace App\Enums;

enum RoleType: int
{
    case UNASSIGNED = 0;
    case SUPERADMIN = 1;
    case ADMIN = 2;
    case KONTRIBUTOR_UTAMA = 3;
    case KONTRIBUTOR_DAERAH = 4;
    case EDITOR_UTAMA = 5;

}