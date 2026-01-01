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
        Schema::create('donnees_climatiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->integer('annee');
            $table->enum('type_indicateur', ['secheresse', 'inondation', 'desertification', 'temperature', 'pluviometrie']);
            $table->decimal('valeur', 10, 2)->nullable();
            $table->string('unite', 50)->nullable();
            $table->string('source')->nullable();
            $table->text('commentaire')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->index(['region_id', 'annee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donnees_climatiques');
    }
};
