<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexe les jetons d'authentification.
 *
 * `verification_token` et `reset_token` servent de critère de recherche à
 * chaque vérification d'adresse et à chaque réinitialisation de mot de passe
 * (AuthController::verifyEmail et ::resetPassword), sans index : la base
 * parcourait toute la table `users` à chaque appel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('verification_token');
            $table->index('reset_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['verification_token']);
            $table->dropIndex(['reset_token']);
        });
    }
};
