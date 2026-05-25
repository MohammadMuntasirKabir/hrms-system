<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('contract_type'); // full_time, part_time, contract, internship, freelance
            $table->string('position');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->string('currency', 3)->default('BDT');
            $table->string('status')->default('active'); // active, expired, terminated, draft
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
