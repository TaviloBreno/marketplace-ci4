<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresso - <?= esc($ticket->code) ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: A4;
            margin: 20mm;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .ticket {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .event-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .event-date {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .ticket-body {
            padding: 30px;
        }
        
        .seat-section {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .seat-box {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 12px;
        }
        
        .seat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }
        
        .seat-code {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .qr-section {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        #qrcode {
            display: inline-block;
        }
        
        .ticket-code {
            font-family: monospace;
            font-size: 1.3rem;
            letter-spacing: 3px;
            text-align: center;
            padding: 15px;
            background: #f1f5f9;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-item {
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-weight: 600;
            color: #1e293b;
        }
        
        .ticket-footer {
            padding: 20px 30px;
            background: #f8fafc;
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
            border-top: 2px dashed #e2e8f0;
        }
        
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #6366f1;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        
        .print-btn:hover {
            background: #4f46e5;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .ticket {
                box-shadow: none;
                max-width: 100%;
            }
            
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="ticket-header">
            <div class="event-title"><?= esc($event->title ?? 'Evento') ?></div>
            <div class="event-date">
                <?= date('d/m/Y', strtotime($eventDay->event_date ?? 'now')) ?>
                <?php if ($eventDay && $eventDay->start_time): ?>
                    às <?= date('H:i', strtotime($eventDay->start_time)) ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="ticket-body">
            <div class="seat-section">
                <div class="seat-box">
                    <div class="seat-label">Assento</div>
                    <div class="seat-code"><?= esc($seat->code ?? 'N/A') ?></div>
                </div>
            </div>
            
            <div class="qr-section">
                <div id="qrcode"></div>
            </div>
            
            <div class="ticket-code">
                <?= esc($ticket->code) ?>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Local</div>
                    <div class="info-value"><?= esc($event->venue ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Setor</div>
                    <div class="info-value"><?= esc($sector->name ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fila</div>
                    <div class="info-value"><?= esc($queue->name ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Cidade</div>
                    <div class="info-value"><?= esc($event->city ?? 'N/A') ?></div>
                </div>
            </div>
        </div>
        
        <div class="ticket-footer">
            Apresente este ingresso na entrada do evento.<br>
            Tenha um documento de identidade em mãos.<br>
            <strong>EventHub</strong> - www.eventhub.com
        </div>
    </div>
    
    <button class="print-btn" onclick="window.print()">
        🖨️ Imprimir Ingresso
    </button>
    
    <script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
    <script>
        const qrData = JSON.stringify({
            code: '<?= esc($ticket->code) ?>',
            event: <?= $event ? $event->id : 0 ?>,
            day: <?= $eventDay ? $eventDay->id : 0 ?>,
            seat: <?= $seat ? $seat->id : 0 ?>
        });
        
        QRCode.toCanvas(document.createElement('canvas'), qrData, { 
            width: 150,
            margin: 0
        }, function (error, canvas) {
            if (!error) {
                document.getElementById('qrcode').appendChild(canvas);
            }
        });
    </script>
</body>
</html>
