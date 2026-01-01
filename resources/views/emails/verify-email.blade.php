<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 12px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🌍 ONCC Sénégal</h1>
        <p>Observatoire National sur les Changements Climatiques</p>
    </div>
    
    <div class="content">
        <h2>Bonjour {{ $user->prenom }} {{ $user->nom }},</h2>
        
        <p>Merci de vous être inscrit sur la plateforme ONCC Sénégal !</p>
        
        <p>Pour activer votre compte et commencer à utiliser nos services, veuillez cliquer sur le bouton ci-dessous :</p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">
                ✓ Activer mon compte
            </a>
        </div>
        
        <p>Ou copiez ce lien dans votre navigateur :</p>
        <p style="word-break: break-all; color: #0d6efd;">{{ $verificationUrl }}</p>
        
        <div class="warning">
            <strong>⚠️ Important :</strong> Ce lien est valable pendant 24 heures. Si vous n'avez pas demandé cette inscription, vous pouvez ignorer cet email.
        </div>
        
        <p>Cordialement,<br>L'équipe ONCC Sénégal</p>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} ONCC Sénégal - Observatoire National sur les Changements Climatiques</p>
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>
</body>
</html>
