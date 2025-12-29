<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a5f 0%, #0d1b2a 100%);
        }
        .auth-card {
            max-width: 420px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        .auth-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            border-radius: 16px 16px 0 0;
            padding: 30px;
            text-align: center;
        }
        .auth-header h2 {
            color: white;
            margin-bottom: 5px;
        }
        .auth-header p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0;
        }
        .form-control:focus {
            border-color: #1e3a5f;
            box-shadow: 0 0 0 0.2rem rgba(30, 58, 95, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2d5a87 0%, #1e3a5f 100%);
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 20px 0;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }
        .divider span {
            padding: 0 15px;
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
    <div class="auth-card bg-white w-100 mx-3">
        <?= $this->renderSection('content') ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
