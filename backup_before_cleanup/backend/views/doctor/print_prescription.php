<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Print</title>
    <style>
        body { font-family: 'Times New Roman', serif; background: #e0e0e0; }
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 20px auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        @media print {
            body { background: white; }
            .a4-page { box-shadow: none; margin: 0; }
            .no-print { display: none; }
        }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; }
        .doc-info h1 { margin: 0; font-size: 24px; color: #333; }
        .doc-info p { margin: 5px 0 0; color: #666; }
        .logo { font-size: 30px; font-weight: bold; color: #6C5DD3; }
        
        .patient-info { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 16px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; }
        .patient-info strong { font-weight: bold; }

        .content { display: flex; gap: 30px; min-height: 500px; }
        .left-col { width: 30%; border-right: 1px solid #eee; padding-right: 20px; }
        .right-col { width: 70%; padding-left: 20px; }

        .section-title { font-weight: bold; text-decoration: underline; margin-bottom: 10px; font-size: 14px; text-transform: uppercase; color: #666; }
        
        .rx-symbol { font-size: 40px; font-style: italic; font-weight: bold; margin-bottom: 10px; }
        .medicine-list { margin-top: 20px; white-space: pre-wrap; font-size: 16px; line-height: 1.6; }

        .footer { position: absolute; bottom: 20mm; left: 20mm; right: 20mm; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; padding: 20px;">
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #6C5DD3; color: white; border: none; border-radius: 5px;">Print Prescription</button>
</div>

<div class="a4-page">
    <div class="header">
        <div class="doc-info">
            <h1>Dr. <?= $prescription['doctor_name'] ?></h1>
            <p><?= $prescription['specialization'] ?></p>
            <p>Reg No: 12345678</p>
        </div>
        <div class="logo">MEDIQ</div>
    </div>

    <div class="patient-info">
        <div>
            <p><strong>Patient Name:</strong> <?= $prescription['patient_name'] ?> (<?= $prescription['gender'] ?>/<?= $prescription['age'] ?>)</p>
            <p><strong>Address:</strong> Hyderabad, Sindh</p>
        </div>
        <div style="text-align: right;">
            <p><strong>Date:</strong> <?= date('d-M-Y', strtotime($prescription['date'])) ?></p>
            <p><strong>Token:</strong> #<?= $prescription['token_number'] ?></p>
        </div>
    </div>

    <div class="content">
        <div class="left-col">
            <div class="section-title">Clinical Findings</div>
            <p><?= nl2br($prescription['diagnosis']) ?></p>
            
            <br><br>
            <div class="section-title">Advice</div>
            <p><?= nl2br($prescription['advice']) ?></p>
        </div>
        <div class="right-col">
            <div class="rx-symbol">Rx</div>
            <div class="medicine-list">
<?= $prescription['medicines'] ?>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Medical Center, Main Road, City | Phone: +123 456 7890</p>
        <p>This is a computer generated prescription.</p>
    </div>
</div>

</body>
</html>
