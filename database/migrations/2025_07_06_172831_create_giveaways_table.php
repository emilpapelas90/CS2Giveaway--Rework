<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('giveaways', function (Blueprint $table) {
            $table->id();
            $table->string('skin_name');
            $table->text('image');
            $table->decimal('value', 10, 2);
            $table->string('rarity');
            $table->unsignedInteger('entries')->default(0);
            $table->unsignedInteger('max_entries')->default(1);
            $table->json('requirements')->nullable();
            $table->string('end_time');
            $table->boolean('type')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giveaways');
    }
};
