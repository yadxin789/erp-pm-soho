<?php

namespace App\Soho\Models\Concerns;

trait HasSohoTable
{
    public function getTable(): string
    {
        $t = parent::getTable();
        return str_starts_with($t, 'soho_') ? $t : 'soho_' . $t;
    }
}