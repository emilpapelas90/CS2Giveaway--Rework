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
            $table->dateTime('start_time')->nullable();
            $table->integer('duration_minutes')->default(60);             
            $table->integer('min_entries')->default(10);
            $table->boolean('type')->default(0);
            $table->string('server_seed');        
            $table->string('server_seed_hashed'); 
            $table->string('revealed_server_seed')->nullable();
            $table->boolean('is_active')->default(false);
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
