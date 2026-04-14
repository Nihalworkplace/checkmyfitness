<?php

// config/cmf.php
return [
    /*
    |----------------------------------------------------------------------
    | Doctor Session Expiry (hours)
    |----------------------------------------------------------------------
    | How many hours a doctor session remains valid after creation.
    | Doctor is automatically logged out when this expires.
    */
    'doctor_session_expiry_hours' => env('DOCTOR_SESSION_EXPIRY_HOURS', 12),

    /*
    |----------------------------------------------------------------------
    | Max Login Attempts Before Lockout
    |----------------------------------------------------------------------
    */
    'doctor_session_max_attempts' => env('DOCTOR_SESSION_MAX_ATTEMPTS', 5),

    /*
    |----------------------------------------------------------------------
    | One Session Per Doctor
    |----------------------------------------------------------------------
    | If true, creating a new session for a doctor automatically
    | revokes any previously active sessions for that doctor.
    */
    'one_session_per_doctor' => true,

    /*
    |----------------------------------------------------------------------
    | Available Classes
    |----------------------------------------------------------------------
    */
    'classes' => [
        '1A','1B','2A','2B','3A','3B','4A','4B',
        '5A','5B','6A','6B','7A','7B','8A','8B',
        '9A','9B','10A','10B','11A','11B','12A','12B',
    ],
];
