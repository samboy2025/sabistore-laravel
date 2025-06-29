<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Completion</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .certificate {
            background: white;
            width: 800px;
            height: 600px;
            padding: 60px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 8px solid #f8f9fa;
            position: relative;
            overflow: hidden;
        }
        
        .certificate::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23f8f9fa" opacity="0.3"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.1;
            pointer-events: none;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
        }
        
        .title {
            font-size: 36px;
            font-weight: bold;
            color: #2d3748;
            margin: 0;
            letter-spacing: 2px;
        }
        
        .subtitle {
            font-size: 18px;
            color: #718096;
            margin: 10px 0 0 0;
            font-style: italic;
        }
        
        .content {
            text-align: center;
            margin: 40px 0;
            position: relative;
            z-index: 1;
        }
        
        .awarded-to {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .recipient-name {
            font-size: 42px;
            font-weight: bold;
            color: #2d3748;
            margin: 20px 0;
            border-bottom: 3px solid #667eea;
            display: inline-block;
            padding-bottom: 10px;
        }
        
        .course-info {
            margin: 30px 0;
        }
        
        .completion-text {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 10px;
        }
        
        .course-title {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
            margin: 10px 0;
        }
        
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 60px;
            position: relative;
            z-index: 1;
        }
        
        .date-section, .signature-section {
            text-align: center;
            flex: 1;
        }
        
        .date, .signature-line {
            border-top: 2px solid #2d3748;
            padding-top: 10px;
            margin-top: 20px;
            font-size: 14px;
            color: #4a5568;
        }
        
        .certificate-number {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            color: #a0aec0;
            font-family: 'Courier New', monospace;
        }
        
        .decorative-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            pointer-events: none;
        }
        
        .decorative-corner {
            position: absolute;
            width: 40px;
            height: 40px;
            border: 3px solid #667eea;
        }
        
        .decorative-corner.top-left {
            top: 30px;
            left: 30px;
            border-right: none;
            border-bottom: none;
        }
        
        .decorative-corner.top-right {
            top: 30px;
            right: 30px;
            border-left: none;
            border-bottom: none;
        }
        
        .decorative-corner.bottom-left {
            bottom: 30px;
            left: 30px;
            border-right: none;
            border-top: none;
        }
        
        .decorative-corner.bottom-right {
            bottom: 30px;
            right: 30px;
            border-left: none;
            border-top: none;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="decorative-border"></div>
        <div class="decorative-corner top-left"></div>
        <div class="decorative-corner top-right"></div>
        <div class="decorative-corner bottom-left"></div>
        <div class="decorative-corner bottom-right"></div>
        
        <div class="header">
            <div class="logo">S</div>
            <h1 class="title">CERTIFICATE</h1>
            <p class="subtitle">of Completion</p>
        </div>
        
        <div class="content">
            <p class="awarded-to">This is to certify that</p>
            <h2 class="recipient-name">{{ $certificate->recipient_name }}</h2>
            
            <div class="course-info">
                <p class="completion-text">has successfully completed the course</p>
                <h3 class="course-title">{{ $certificate->course_title }}</h3>
            </div>
        </div>
        
        <div class="footer">
            <div class="date-section">
                <div class="date">
                    Date: {{ $certificate->completion_date->format('F d, Y') }}
                </div>
            </div>
            
            <div class="signature-section">
                <div class="signature-line">
                    SabiStore Learning Center
                </div>
            </div>
        </div>
        
        <div class="certificate-number">
            Certificate No: {{ $certificate->certificate_number }}
        </div>
    </div>
</body>
</html>
