<?php

return [
    // A request that is not resolved/closed after this many days is "overdue".
    // The schema has no due-date column, so this is a configurable policy (see ADR/PED 13).
    'overdue_days' => (int) env('OVERDUE_DAYS', 3),

    'statuses' => ['open', 'assigned', 'in_progress', 'resolved', 'closed'],
    'roles' => ['requestor', 'staff', 'management'],
    'per_page' => 15,
];
