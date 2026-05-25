<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('employee_id')->nullable()->after('name');
            $table->string('job_title')->nullable()->after('employee_id');
            $table->foreignId('department_id')->nullable()->after('job_title');
            $table->boolean('is_active')->default(true)->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'employee_id', 'job_title', 'department_id', 'is_active']);
        });
    }
};
