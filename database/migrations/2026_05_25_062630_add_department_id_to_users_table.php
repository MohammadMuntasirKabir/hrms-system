<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // department_id already added in add_company_fields_to_users_table migration
        // This migration is kept for rollback reference
    }

    public function down(): void
    {
        // No-op: column was originally added in add_company_fields_to_users_table
    }
};
