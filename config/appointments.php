<?php
/**
 * Appointment Scheduling Configuration
 * This file defines the operational hours and capacity for the Records Office.
 */

return [
    'office_hours' => [
        'open' => '09:00',
        'close' => '16:00',
        'slot_duration' => 30, // in minutes
    ],
    'capacity' => [
        'max_per_slot' => 5, // Maximum number of appointments per time slot
    ],
    'scheduling_rules' => [
        'allowed_days' => [1, 2, 3, 4, 5], // 1=Mon, 5=Fri. Sat/Sun (6,7) are excluded.
        'min_advance_days' => 1, // Must book at least 1 day in advance
    ],
];
