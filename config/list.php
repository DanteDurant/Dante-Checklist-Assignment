<?php

return [
    'default_per_page' => (int) env('LIST_DEFAULT_PER_PAGE', 15),
    'max_per_page' => (int) env('LIST_MAX_PER_PAGE', 100),
    'max_per_page_questions' => (int) env('LIST_MAX_PER_PAGE_QUESTIONS', 200),
];
