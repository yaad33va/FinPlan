<?php

namespace App\Enums;

/**
 * Roles stored in the database and in the JWT "role" claim.
 * The third role, guest, is a visitor without a token.
 */
enum UserRole: string
{
    case Member = 'member';
    case Admin = 'admin';
}
