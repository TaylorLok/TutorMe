<?php

namespace App\Enums;

enum UserTypes: string
{
    case ADMIN = 'admin';
    case TUTOR = 'tutor';
    case STUDENT = 'student';
}
