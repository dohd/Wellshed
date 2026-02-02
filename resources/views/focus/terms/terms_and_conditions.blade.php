{{-- resources/views/terms/water-subscription.blade.php --}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Terms and Conditions - Water Subscription Service' }}</title>

    <style>
        :root{
            --text:#111827;
            --muted:#6b7280;
            --border:#e5e7eb;
            --bg:#ffffff;
            --soft:#f9fafb;
        }
        body{
            margin:0;
            background:var(--soft);
            color:var(--text);
            font-family: Arial, Helvetica, sans-serif;
            line-height:1.55;
        }
        .wrap{
            max-width: 920px;
            margin: 32px auto;
            padding: 0 16px;
        }
        .card{
            background:var(--bg);
            border:1px solid var(--border);
            border-radius:12px;
            padding: 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,.03);
        }
        .header{
            display:flex;
            gap:18px;
            justify-content:space-between;
            align-items:flex-start;
            border-bottom:1px solid var(--border);
            padding-bottom:16px;
            margin-bottom:18px;
        }
        .brand h1{
            margin:0 0 6px;
            font-size:20px;
            letter-spacing:.3px;
        }
        .brand .sub{
            margin:0;
            color:var(--muted);
            font-size:13px;
        }
        .contacts{
            text-align:right;
            font-size:13px;
            color:var(--muted);
        }
        .contacts a{ color:inherit; text-decoration:none; }
        .badge{
            display:inline-block;
            padding:4px 10px;
            border-radius:999px;
            background:#eef2ff;
            color:#3730a3;
            font-size:12px;
            font-weight:600;
            margin-top:8px;
        }
        h2{
            font-size:16px;
            margin:22px 0 10px;
        }
        h3{
            font-size:14px;
            margin:18px 0 8px;
        }
        .muted{ color:var(--muted); }
        ol{
            padding-left: 18px;
        }
        li{ margin: 8px 0; }
        .clause{
            border:1px solid var(--border);
            border-radius:10px;
            padding: 14px 14px 10px;
            margin: 12px 0;
            background: #fff;
        }
        .clause .no{
            font-weight:700;
            margin-bottom:6px;
        }
        ul{
            padding-left:18px;
        }
        .footer{
            border-top:1px solid var(--border);
            margin-top:22px;
            padding-top:14px;
            color:var(--muted);
            font-size:13px;
        }
        .print{
            margin-top: 14px;
        }
        @media print {
            body{ background:#fff; }
            .wrap{ margin:0; max-width:none; }
            .card{ border:0; box-shadow:none; border-radius:0; padding:0; }
            .print{ display:none; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">

        {{-- Header / Company Block --}}
        <div class="header">
            <div class="brand">
                <h1>{{ $companyName ?? 'Wellshed Group Limited' }}</h1>
                <p class="sub">
                    {{ $addressLine1 ?? 'P.o. Box – 16972' }},
                    {{ $addressLine2 ?? '00100 NAIROBI' }}<br>
                    {{ $locationLine ?? 'Karen, Nairobi Kenya' }}
                </p>
                <div class="badge">
                    TERMS AND CONDITIONS — WATER SUBSCRIPTION SERVICE
                </div>
                <p class="sub muted" style="margin-top:8px;">
                    Last Updated: {{ $lastUpdated ?? 'February 2nd 2026' }}
                </p>
            </div>

            <div class="contacts">
                <div>
                    <strong class="muted">Phone:</strong>
                    {{ $phone1 ?? '+254 708 660 147' }}<br>
                    {{ $phone2 ?? '+254 755 993 524' }}
                </div>
                <div style="margin-top:10px;">
                    <strong class="muted">Email:</strong>
                    <a href="mailto:{{ $email ?? 'info@wellshedgroup.com' }}">{{ $email ?? 'info@wellshedgroup.com' }}</a>
                </div>
                <div style="margin-top:6px;">
                    <strong class="muted">Website:</strong>
                    <a href="{{ $websiteUrl ?? 'https://www.wellshedgroup.com' }}" target="_blank" rel="noopener">
                        {{ $websiteText ?? 'www.wellshedgroup.com' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <section>
            <div class="clause" id="clause-1">
                <div class="no">1. INTRODUCTION</div>
                <div>
                    These Terms and Conditions (“Terms”) govern the provision of water subscription services by
                    {{ $companyName ?? 'Wellshed Group Limited' }} (“the Company”) to customers (“Customer”, “You”).
                    They apply to all subscriptions, deliveries, refills, dispensers, bottles, digital platforms, and customer communications.
                </div>
            </div>

            <div class="clause" id="clause-2">
                <div class="no">2. ACCEPTANCE OF TERMS</div>
                <div>
                    By registering on our website, submitting personal details, making payment, or receiving services,
                    you confirm that you have read, understood, and agreed to be bound by these Terms.
                </div>
            </div>

            <div class="clause" id="clause-3">
                <div class="no">3. SERVICE DESCRIPTION</div>
                <div>
                    The Company supplies potable drinking water, refill water, dispensers, bottles, and related services under
                    subscription or one-off arrangements. Services are provided subject to availability, delivery zones, and operational constraints.
                </div>
            </div>

            <div class="clause" id="clause-4">
                <div class="no">4. SUBSCRIPTIONS AND PAYMENTS</div>
                <ol>
                    <li>Subscriptions are billed in advance or arrears as selected.</li>
                    <li>Payments are accepted via M-Pesa, bank transfer, or approved digital platforms.</li>
                    <li>Non-payment may result in suspension or termination of service.</li>
                    <li>Prices may change with reasonable notice.</li>
                </ol>
            </div>

            <div class="clause" id="clause-5">
                <div class="no">5. DELIVERY CONDITIONS</div>
                <ol>
                    <li>Delivery timelines are indicative and not guaranteed.</li>
                    <li>Customers must ensure safe access and availability at delivery locations.</li>
                    <li>Failed deliveries caused by customer unavailability may attract additional charges.</li>
                </ol>
            </div>

            <div class="clause" id="clause-6">
                <div class="no">6. BOTTLES, DISPENSERS, AND DEPOSITS</div>
                <ol>
                    <li>All returnable bottles and dispensers remain the property of the Company.</li>
                    <li>Customers are responsible for loss, theft, or damage beyond normal wear.</li>
                    <li>Replacement costs may be charged or deducted from deposits.</li>
                </ol>
            </div>

            <div class="clause" id="clause-7">
                <div class="no">7. QUALITY AND SAFETY</div>
                <ol>
                    <li>Water supplied complies with KEBS and public health standards.</li>
                    <li>Quality concerns must be reported within 24 hours of delivery.</li>
                    <li>Liability is limited to replacement of affected products.</li>
                </ol>
            </div>

            <div class="clause" id="clause-8">
                <div class="no">8. DATA PROTECTION AND PRIVACY (KENYA DATA PROTECTION ACT, 2019)</div>

                <h3>8.1 Data Controller</h3>
                <p>{{ $companyName ?? 'Wellshed Group Limited' }} is the Data Controller under the Kenya Data Protection Act, 2019.</p>

                <h3>8.2 Categories of Personal Data Collected</h3>
                <ul>
                    <li>Full name</li>
                    <li>Phone number</li>
                    <li>Email address</li>
                    <li>Physical delivery address and GPS location</li>
                    <li>Identification details (where required for corporate clients)</li>
                    <li>Payment and transaction records</li>
                    <li>Subscription preferences and delivery history</li>
                    <li>WhatsApp, call recordings, emails, and customer support communications</li>
                    <li>Device, IP address, and website usage data (cookies and analytics)</li>
                </ul>

                <h3>8.3 Lawful Basis for Processing</h3>
                <p>The Company processes personal data based on:</p>
                <ul>
                    <li>Performance of a contract</li>
                    <li>Consent of the data subject</li>
                    <li>Compliance with legal obligations</li>
                    <li>Legitimate business interests</li>
                </ul>

                <h3>8.4 Purpose of Processing</h3>
                <p>Personal data is processed for:</p>
                <ul>
                    <li>Account registration and management</li>
                    <li>Order processing and delivery</li>
                    <li>Payment reconciliation and invoicing</li>
                    <li>Customer support and complaint handling</li>
                    <li>Service improvement and analytics</li>
                    <li>Regulatory compliance and audit</li>
                </ul>

                <h3>8.5 Consent</h3>
                <p>
                    By registering and using the Service, you expressly consent to the collection, use, storage, and processing
                    of your personal data as outlined herein. Consent may be withdrawn subject to legal and contractual limitations.
                </p>

                <h3>8.6 Data Sharing and Third Parties</h3>
                <p>The Company may share personal data with:</p>
                <ul>
                    <li>Delivery and logistics partners</li>
                    <li>Payment service providers</li>
                    <li>CRM, ERP, and cloud service providers</li>
                    <li>Regulators and law enforcement where required by law</li>
                </ul>
                <p>All third parties are required to implement appropriate data protection safeguards.</p>

                <h3>8.7 Cross-Border Data Transfers</h3>
                <p>
                    Where personal data is transferred outside Kenya, the Company ensures compliance with ODPC requirements,
                    including adequate safeguards and contractual protections.
                </p>

                <h3>8.8 Data Security Measures</h3>
                <p>The Company implements:</p>
                <ul>
                    <li>Access controls</li>
                    <li>Secure servers and encrypted systems</li>
                    <li>Staff confidentiality obligations</li>
                    <li>Regular system monitoring</li>
                </ul>

                <h3>8.9 Data Retention</h3>
                <p>Personal data is retained only for as long as necessary for:</p>
                <ul>
                    <li>Service delivery</li>
                    <li>Legal and regulatory compliance</li>
                    <li>Dispute resolution</li>
                    <li>Legitimate business purposes</li>
                </ul>

                <h3>8.10 Data Subject Rights</h3>
                <p>Customers have the right to:</p>
                <ul>
                    <li>Access personal data</li>
                    <li>Request correction or update</li>
                    <li>Object to processing</li>
                    <li>Request deletion where legally permissible</li>
                    <li>Lodge a complaint with the Office of the Data Protection Commissioner (ODPC)</li>
                </ul>
                <p>Requests may be submitted to: <a href="mailto:{{ $email ?? 'info@wellshedgroup.com' }}">{{ $email ?? 'info@wellshedgroup.com' }}</a></p>
            </div>

            <div class="clause" id="clause-9">
                <div class="no">9. DIGITAL AND WHATSAPP COMMUNICATIONS</div>
                <div>
                    Customers consent to service-related communications via WhatsApp, SMS, email, and phone calls.
                    Marketing communications may be opted out of at any time.
                </div>
            </div>

            <div class="clause" id="clause-10">
                <div class="no">10. LIMITATION OF LIABILITY</div>
                <div>
                    The Company shall not be liable for indirect or consequential losses.
                    Total liability shall not exceed the value of the most recent paid subscription period.
                </div>
            </div>

            <div class="clause" id="clause-11">
                <div class="no">11. TERMINATION</div>
                <div>
                    The Company may suspend or terminate services for breach, misuse, or non-payment.
                    Outstanding balances remain payable.
                </div>
            </div>

            <div class="clause" id="clause-12">
                <div class="no">12. FORCE MAJEURE</div>
                <div>
                    The Company is not liable for delays or failures caused by events beyond reasonable control.
                </div>
            </div>

            <div class="clause" id="clause-13">
                <div class="no">13. GOVERNING LAW AND JURISDICTION</div>
                <div>
                    These Terms are governed by the laws of the Republic of Kenya.
                    Kenyan courts shall have exclusive jurisdiction.
                </div>
            </div>

            <div class="clause" id="clause-14">
                <div class="no">14. AMENDMENTS</div>
                <div>
                    The Company may amend these Terms at any time.
                    Continued use of the Service constitutes acceptance of revised Terms.
                </div>
            </div>

            <div class="clause" id="clause-15">
                <div class="no">15. CONTACT DETAILS</div>
                <div>
                    <strong>{{ $companyName ?? 'Wellshed Group Limited' }}</strong><br>
                    Email: <a href="mailto:{{ $email ?? 'info@wellshedgroup.com' }}">{{ $email ?? 'info@wellshedgroup.com' }}</a><br>
                    Phone: {{ $phoneLocal1 ?? '0708660147' }}, {{ $phoneLocal2 ?? '0755993524' }}<br>
                    Website: <a href="{{ $contactWebsiteUrl ?? 'https://www.grange-park.com' }}" target="_blank" rel="noopener">
                        {{ $contactWebsiteText ?? 'www.grange-park.com' }}
                    </a>
                </div>
            </div>

            {{-- <div class="print">
                <button type="button" onclick="window.print()" style="cursor:pointer;padding:10px 14px;border-radius:10px;border:1px solid var(--border);background:#fff;">
                    Print / Save as PDF
                </button>
            </div>

            <div class="footer">
                <div>
                    <strong>Note:</strong> This page is a digital rendering of the published Terms and Conditions for the Water Subscription Service.
                </div>
            </div> --}}
        </section>
    </div>
</div>
</body>
</html>
