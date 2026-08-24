<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7f9;
            padding-bottom: 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            color: #4a5568;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-top: 40px;
        }
        .header {
            background-color: #ffffff;
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid #005a9c;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 40px;
            line-height: 1.6;
        }
        h1 {
            color: #1a202c;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            background-color: #005a9c;
            color: #ffffff !important;
            padding: 14px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(0, 90, 156, 0.2);
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 11px;
            color: #718096;
            background-color: #f8fafc;
        }
        .divider {
            border-top: 1px solid #e2e8f0;
            margin: 30px 0;
        }
        .small-text {
            font-size: 12px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    <img src="https://tse1.mm.bing.net/th/id/OIP.yOU8eycHego0ZrV-Jyn51wHaGn?r=0&rs=1&pid=ImgDetMain&o=7&rm=3" alt="SRM Beni Mellal">
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h1>Bonjour {{ $name }},</h1>
                    <p>Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte sur la plateforme de gestion <strong>NetDevice Pro</strong> de la SRM.</p>
                    
                    <div class="button-container">
                        <a href="{{ $url }}" class="button">Réinitialiser mon mot de passe</a>
                    </div>

                    <p>Ce lien de réinitialisation est sécurisé et expirera dans 60 minutes.</p>
                    <p>Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email en toute sécurité.</p>
                    
                    <div class="divider"></div>
                    
                    <p class="small-text">
                        En cas de problème, copiez ce lien dans votre navigateur :<br>
                        <span style="color: #005a9c;">{{ $url }}</span>
                    </p>
                </td>
            </tr>
        </table>
        <div class="footer">
            &copy; {{ date('Y') }} SRM Béni Mellal - Khénifra S.A. Tous droits réservés.<br>
            Société Régionale de Multi-services - Division Système d'Information
        </div>
    </div>
</body>
</html>
