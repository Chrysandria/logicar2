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
        Schema::create('planning_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId("employe_id")->constrained("employes")->cascadeOnDelete();
            $table->foreignId("planning_id")->constrained("plannings")->cascadeOnDelete();
            $table->foreignId("expedition_id")->nullable()->constrained("expeditions")->cascadeOnDelete();
            $table->boolean("ok")->default(false);
            $table->string("remarque")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_items');
    }
};
