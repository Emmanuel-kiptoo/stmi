<?php
require_once '../../config/database.php';
requireAdmin();

$donation_id = $_GET['id'] ?? 0;

// Fetch donation details
$stmt = $pdo->prepare("
    SELECT d.*, c.title as campaign_title 
    FROM donations d 
    LEFT JOIN donation_campaigns c ON d.campaign_id = c.id 
    WHERE d.id = ?
");
$stmt->execute([$donation_id]);
$donation = $stmt->fetch();

if (!$donation) {
    die('Donation not found');
}

// Generate receipt number
$receipt_no = 'STMI-' . date('Y') . '-' . str_pad($donation_id, 6, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Receipt - STMI Trust</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0e0c5e;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            margin: 0;
        }
        .receipt-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .amount-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            text-align: center;
        }
        .amount {
            font-size: 36px;
            font-weight: bold;
            color: #2ecc71;
        }
        .currency {
            font-size: 24px;
            color: #666;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .signature {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin: 20px 0;
            padding-top: 10px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0,0,0,0.1);
            pointer-events: none;
            white-space: nowrap;
            font-weight: bold;
        }
        @media print {
            body {
                padding: 0;
            }
            .receipt {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="watermark">PAID</div>
        
        <div class="header">
            <h1>SOKA TOTO MUDA INITIATIVE TRUST</h1>
            <p>Christ-centered, non-profit making organization</p>
            <p>P.O. Box 12345, Nairobi, Kenya | Email: stmitrust@gmail.com</p>
            <p>Phone: +254 728 274304 | Website: www.stmitrust.org</p>
        </div>
        
        <div class="receipt-details">
            <div>
                <div class="detail-item">
                    <div class="detail-label">Receipt Number:</div>
                    <div class="detail-value"><?php echo $receipt_no; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value"><?php echo date('F j, Y', strtotime($donation['created_at'])); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Transaction ID:</div>
                    <div class="detail-value"><?php echo $donation['transaction_id'] ?: 'N/A'; ?></div>
                </div>
            </div>
            <div>
                <div class="detail-item">
                    <div class="detail-label">Donor Name:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($donation['donor_name']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value"><?php echo $donation['donor_email'] ?: 'Not provided'; ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Phone:</div>
                    <div class="detail-value"><?php echo $donation['donor_phone'] ?: 'Not provided'; ?></div>
                </div>
            </div>
        </div>
        
        <div class="amount-box">
            <div class="amount">
                <?php echo $donation['currency']; ?> <?php echo number_format($donation['amount'], 2); ?>
            </div>
            <div class="description">Donation Amount Received</div>
        </div>
        
        <div class="receipt-details">
            <div>
                <div class="detail-item">
                    <div class="detail-label">Payment Method:</div>
                    <div class="detail-value"><?php echo ucfirst($donation['payment_method']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Campaign:</div>
                    <div class="detail-value"><?php echo $donation['campaign_title'] ?: 'General Donation'; ?></div>
                </div>
            </div>
            <div>
                <div class="detail-item">
                    <div class="detail-label">Purpose:</div>
                    <div class="detail-value"><?php echo $donation['purpose'] ?: 'Supporting children and teen mothers'; ?></div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>Notes:</strong> This receipt acknowledges your donation to Soka Toto Muda Initiative Trust. Your contribution is tax-deductible as permitted by law.</p>
            <p>Thank you for your generous support in empowering children and young mothers through sports, creative arts, and psychosocial support.</p>
            
            <div class="signature">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Donor's Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div>Authorized Signature</div>
                    <div>Soka Toto Muda Initiative Trust</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>