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
        Schema::create('expeditions', function (Blueprint $table) {
            $table->id();
            $table->date("date");
            $table->time("hdr");
            $table->foreignId("trajet_id")->constrained("trajets")->cascadeOnDelete();
            $table->foreignId("car_id")->nullable()->constrained("cars")->cascadeOnDelete();
            $table->unique(["date","hdr","car_id"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expeditions');
    }
};
