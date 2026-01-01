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
        Schema::create('donnees_economiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->integer('annee');
            $table->enum('secteur', ['agriculture', 'peche', 'tourisme', 'energie', 'elevage', 'foret']);
            $table->string('indicateur', 100);
            $table->decimal('valeur', 15, 2)->nullable();
            $table->string('unite', 50)->default('FCFA');
            $table->enum('impact', ['positif', 'negatif', 'neutre'])->nullable();
            $table->text('description')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            $table->foreignId('utilisateur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donnees_economiques');
    }
};
