<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Comprehensive Operational Summary Report </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            width: 80%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .d-flex {
            display: flex;
            align-items: baseline;
        }
        h1 {
            color: #333333;
            margin: 0;
        }
        h2 {
            color: #333333;
            margin: 0;
            margin-right: 5px;
        }
        p {
            color: #555555;
            line-height: 1.6;
        }
        .button-container {
            text-align: center;
            margin: 20px 0;
            margin-bottom: 50px;
        }
        .button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #aaaaaa;
        }
        .message-box {
            margin-bottom: 20px;
        }
        .reminder-title {
            text-align: center;
            margin: 20px 0;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex">
        <h3 style="margin-right: 5px">Hello, </h3>
        <h2>{{" " . $body->name }}</h2>
    </div>

    <h3 class="reminder-title">
        The 8pm Daily Operational Summary Report
    </h3>

    <p class="message-box">

        This is an automated message to notify you that your latest report is now available. Please click the link below to access it:

        <br><br>

        <span class="button-container">
            <a href="{{ route('daily-business-metrics', $body->dbm_uuid) }}" class="button">Go To Report</a>
        </span>

        <br><br>

        Thank you for using our PME ERP system. Should you have any questions, please contact our support team.

        <br><br>
        Best regards,<br>
        PME.
    </p>




    <div class="footer">
        <p>&copy; 2024 PME. All rights reserved.</p>
    </div>
</div>
</body>
</html>
