<?php

return [
    'loan_period_days' => (int) env('LIBRARY_LOAN_PERIOD_DAYS', 14),
    'maximum_active_loans_per_member' => (int) env('LIBRARY_MAX_ACTIVE_LOANS', 3),
    'daily_fine_rupiah' => (int) env('LIBRARY_DAILY_FINE_RUPIAH', 1000),
];
