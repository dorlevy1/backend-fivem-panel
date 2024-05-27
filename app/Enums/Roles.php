<?php

namespace App\Enums;

enum Roles: string
{
    const DEV = 10;
    const GOD = 9;
    const MANAGEMENT = 8;
    const STAFF_MANAGER = 7;
    const HEAD_ADMIN = 6;
    const SENIOR_ADMIN = 5;
    const ADMIN = 4;
}
