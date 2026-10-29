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
        Schema::dropIfExists('permintaan_belanjas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('permintaan_belanjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('item_name');
            $table->integer('qty');
            $table->string('satuan')->nullable();
            $table->decimal('estimasi_harga', 12, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }
};
