<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('excel_jsons', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->json('data'); // Store full JSON here
            $table->string('file_type')->nullable(); // 'file_1' or 'file_2'
            $table->string('comparison_name')->nullable(); // Name for grouping files together
            $table->timestamps();
            
            // Add index for faster queries
            $table->index(['comparison_name', 'file_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_jsons');
    }
};

