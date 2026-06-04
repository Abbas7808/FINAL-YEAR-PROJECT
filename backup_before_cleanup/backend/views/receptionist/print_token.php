<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Token #<?= $token['token_number'] ?></title>
    <style>
        body { font-family: 'Courier New', monospace; text-align: center; width: 300px; margin: 0 auto; }
        .header { font-weight: bold; font-size: 18px; margin-bottom: 10px; }
        .token-num { font-size: 48px; font-weight: bold; margin: 10px 0; border: 2px solid #000; padding: 10px; }
        .details { text-align: left; margin-top: 20px; font-size: 14px; }
        .footer { margin-top: 20px; font-size: 12px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header"><?= $token['hospital_name'] ?></div>
    <div>Outpatient Department</div>
    
    <div class="token-num"><?= $token['token_number'] ?></div>
    
    <div class="details">
        <p><strong>Patient:</strong> <?= $token['patient_name'] ?> (<?= $token['age'] ?>/<?= $token['gender'] ?>)</p>
        <p><strong>Doctor:</strong> <?= $token['doctor_name'] ?></p>
        <p><strong>Date:</strong> <?= date('d-M-Y', strtotime($token['date'])) ?></p>
    </div>

    <div class="footer">
        Please wait for your turn.<br>
        <button class="no-print" onclick="window.print()">Print Again</button> 
        <button class="no-print" onclick="window.close()">Close</button>
    </div>
</body>
</html>
