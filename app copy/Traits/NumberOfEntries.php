<?php
    namespace App\Traits;

    trait NumberOfEntries
    {
        public function updateNumberOfEntries(): void
        {
            $this->numberOfEntries = $this->numberOfEntries;
        }
    }
