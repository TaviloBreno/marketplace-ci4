<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link de Acesso - Marketplace</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f6f9; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 40px 30px; border-radius: 12px 12px 0 0; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px;">🏪 Marketplace</h1>
                            <p style="color: rgba(255,255,255,0.8); margin: 10px 0 0 0; font-size: 16px;">Seu link de acesso</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #1e3a5f; margin: 0 0 20px 0; font-size: 22px;">Olá!</h2>
                            
                            <p style="color: #555555; font-size: 16px; line-height: 1.6; margin: 0 0 25px 0;">
                                Recebemos uma solicitação de acesso à sua conta no Marketplace. 
                                Clique no botão abaixo para entrar automaticamente:
                            </p>
                            
                            <!-- Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="<?= url_to('verify-magic-link') ?>?token=<?= $token ?>" 
                                           style="display: inline-block; background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">
                                            🔐 Acessar Minha Conta
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Warning -->
                            <div style="background-color: #fff8e1; border-left: 4px solid #ffc107; padding: 15px 20px; margin: 25px 0; border-radius: 0 8px 8px 0;">
                                <p style="color: #856404; margin: 0; font-size: 14px;">
                                    <strong>⚠️ Importante:</strong><br>
                                    Este link expira em <strong>1 hora</strong> e só pode ser usado uma vez.
                                </p>
                            </div>
                            
                            <p style="color: #888888; font-size: 14px; line-height: 1.6; margin: 25px 0 0 0;">
                                Se você não solicitou este acesso, pode ignorar este e-mail com segurança. 
                                Sua conta permanece protegida.
                            </p>
                            
                            <!-- Alternative Link -->
                            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eeeeee;">
                                <p style="color: #888888; font-size: 13px; margin: 0 0 10px 0;">
                                    Se o botão não funcionar, copie e cole este link no navegador:
                                </p>
                                <p style="color: #1e3a5f; font-size: 12px; word-break: break-all; background-color: #f5f5f5; padding: 10px; border-radius: 4px; margin: 0;">
                                    <?= url_to('verify-magic-link') ?>?token=<?= $token ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px 30px; border-radius: 0 0 12px 12px; text-align: center;">
                            <p style="color: #888888; font-size: 13px; margin: 0;">
                                © <?= date('Y') ?> Marketplace. Todos os direitos reservados.
                            </p>
                            <p style="color: #aaaaaa; font-size: 12px; margin: 10px 0 0 0;">
                                Este é um e-mail automático. Por favor, não responda.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
