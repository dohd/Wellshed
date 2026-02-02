{{-- resources/views/policies/privacy.blade.php --}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Privacy Policy - Project Management ERP' }}</title>

    <style>
        :root{
            --text:#111827;
            --muted:#6b7280;
            --border:#e5e7eb;
            --bg:#ffffff;
            --soft:#f9fafb;
            --link:#1d4ed8;
        }
        body{
            margin:0;
            background:var(--soft);
            color:var(--text);
            font-family: Arial, Helvetica, sans-serif;
            line-height:1.6;
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
        .badge{
            display:inline-block;
            padding:4px 10px;
            border-radius:999px;
            background:#ecfeff;
            color:#155e75;
            font-size:12px;
            font-weight:700;
            margin-top:8px;
        }
        .contacts{
            text-align:right;
            font-size:13px;
            color:var(--muted);
        }
        .contacts a{
            color:inherit;
            text-decoration:none;
        }
        a{ color: var(--link); }
        h2{
            font-size:16px;
            margin:22px 0 10px;
        }
        h3{
            font-size:14px;
            margin:18px 0 8px;
        }
        .muted{ color:var(--muted); }
        .clause{
            border:1px solid var(--border);
            border-radius:10px;
            padding: 14px 14px 10px;
            margin: 12px 0;
            background:#fff;
        }
        .clause .no{
            font-weight:800;
            margin-bottom:6px;
        }
        ul, ol{
            padding-left:18px;
            margin-top: 8px;
        }
        li{ margin:8px 0; }
        .toc{
            border:1px dashed var(--border);
            background:#fff;
            border-radius:10px;
            padding: 12px 14px;
            margin: 14px 0 18px;
        }
        .toc a{
            text-decoration:none;
        }
        .toc ul{
            list-style: none;
            padding-left: 0;
            margin: 10px 0 0;
        }
        .toc li{
            margin: 6px 0;
        }
        .footer{
            border-top:1px solid var(--border);
            margin-top:22px;
            padding-top:14px;
            color:var(--muted);
            font-size:13px;
            display:flex;
            gap:12px;
            justify-content:space-between;
            flex-wrap:wrap;
        }
        .footer-links a{
            margin-right:10px;
            text-decoration:none;
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

        {{-- Header --}}
        <div class="header">
            <div class="brand">
                <h1>{{ $companyName ?? 'Project Management ERP (PME)' }}</h1>
                <p class="sub">
                    {{ $addressLine ?? 'Aztech Building, Mombasa Road, Opposite Nextgen Mall, Nairobi, Kenya' }}
                </p>

                <div class="badge">PRIVACY POLICY</div>

                <p class="sub muted" style="margin-top:8px;">
                    Effective Date: {{ $effectiveDate ?? '02/09/2025' }}
                </p>
            </div>

            <div class="contacts">
                <div>
                    <strong class="muted">Email:</strong>
                    <a href="mailto:{{ $email ?? 'info@erpproject.co.ke' }}">{{ $email ?? 'info@erpproject.co.ke' }}</a>
                </div>
                <div style="margin-top:8px;">
                    <strong class="muted">Website:</strong>
                    <a href="{{ $websiteUrl ?? 'https://erpproject.co.ke' }}" target="_blank" rel="noopener">
                        {{ $websiteText ?? 'erpproject.co.ke' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Intro --}}
        <p>
            {{ $companyName ?? 'Project Management ERP' }} is committed to protecting your privacy.
            This Privacy Policy explains how we collect, use, and safeguard your information when you use our
            software applications, websites, and services, including
            <strong>{{ $productsText ?? 'PME ERP, PME CRM, and Virtual Sales Force (VSF)' }}</strong>.
        </p>

        {{-- TOC --}}
        <div class="toc">
            <strong>Quick Navigation</strong>
            <ul>
                <li><a href="#section-1">1. Information We Collect</a></li>
                <li><a href="#section-2">2. How We Use Your Information</a></li>
                <li><a href="#section-3">3. Sharing of Information</a></li>
                <li><a href="#section-4">4. Data Security</a></li>
                <li><a href="#section-5">5. User Rights</a></li>
                <li><a href="#section-6">6. Cookies & Tracking</a></li>
                <li><a href="#section-7">7. Children’s Privacy</a></li>
                <li><a href="#section-8">8. Changes to this Privacy Policy</a></li>
                <li><a href="#section-9">9. Contact Us</a></li>
            </ul>
        </div>

        {{-- Sections --}}
        <div class="clause" id="section-1">
            <div class="no">1. INFORMATION WE COLLECT</div>
            <p class="muted" style="margin-top:0;">We may collect the following types of information:</p>
            <ul>
                <li>
                    <strong>Personal Information:</strong>
                    Name, email address, phone number, business details, and login credentials.
                </li>
                <li>
                    <strong>Business Data:</strong>
                    Project details, customer records, financial entries, and other information entered by users into PME ERP/CRM.
                </li>
                <li>
                    <strong>Usage Data:</strong>
                    Device information, browser type, IP address, and how you interact with our platforms.
                </li>
            </ul>
        </div>

        <div class="clause" id="section-2">
            <div class="no">2. HOW WE USE YOUR INFORMATION</div>
            <p class="muted" style="margin-top:0;">We use the information we collect to:</p>
            <ul>
                <li>Provide and improve our ERP, CRM, and VSF services.</li>
                <li>Enable communication between businesses and their customers.</li>
                <li>Personalize user experiences and support services.</li>
                <li>Send important updates, notifications, or promotional offers (with consent).</li>
                <li>Comply with legal obligations.</li>
            </ul>
        </div>

        <div class="clause" id="section-3">
            <div class="no">3. SHARING OF INFORMATION</div>
            <p style="margin-top:0;">
                We do not sell or rent your personal information. We may share data only in the following cases:
            </p>
            <ul>
                <li>
                    With trusted service providers (e.g., cloud hosting, payment processors) who support our services.
                </li>
                <li>
                    With business partners or resellers, strictly for purposes related to PME services.
                </li>
                <li>
                    If required by law, regulation, or legal process.
                </li>
            </ul>
        </div>

        <div class="clause" id="section-4">
            <div class="no">4. DATA SECURITY</div>
            <p style="margin-top:0;">
                We implement strict technical, administrative, and physical safeguards to protect your data against
                unauthorized access, loss, or misuse.
            </p>
        </div>

        <div class="clause" id="section-5">
            <div class="no">5. USER RIGHTS</div>
            <p class="muted" style="margin-top:0;">You have the right to:</p>
            <ul>
                <li>Access and update your personal information.</li>
                <li>Request deletion of your data (subject to legal or contractual obligations).</li>
                <li>Opt out of receiving promotional communications.</li>
            </ul>
            <p style="margin-top:10px;">
                To exercise these rights, contact us at
                <a href="mailto:{{ $email ?? 'info@erpproject.co.ke' }}">{{ $email ?? 'info@erpproject.co.ke' }}</a>.
            </p>
        </div>

        <div class="clause" id="section-6">
            <div class="no">6. COOKIES & TRACKING</div>
            <p style="margin-top:0;">
                Our platforms may use cookies and similar technologies to improve user experience, analyze usage, and
                deliver tailored services.
            </p>
        </div>

        <div class="clause" id="section-7">
            <div class="no">7. CHILDREN’S PRIVACY</div>
            <p style="margin-top:0;">
                PME services are intended for business use and are not directed at children under 18 years of age.
            </p>
        </div>

        <div class="clause" id="section-8">
            <div class="no">8. CHANGES TO THIS PRIVACY POLICY</div>
            <p style="margin-top:0;">
                We may update this Privacy Policy from time to time. Updates will be posted on our website with a revised effective date.
            </p>
        </div>

        <div class="clause" id="section-9">
            <div class="no">9. CONTACT US</div>
            <p style="margin-top:0;">
                If you have any questions about this Privacy Policy or our data practices, please contact us at:
            </p>
            <p style="margin:10px 0 0;">
                📧 Email:
                <a href="mailto:{{ $email ?? 'info@erpproject.co.ke' }}">{{ $email ?? 'info@erpproject.co.ke' }}</a><br>
                📍 Address: {{ $addressLine ?? 'Aztech Building, Mombasa Road, Opposite Nextgen Mall, Nairobi, Kenya' }}
            </p>
        </div>

        {{-- Print button --}}
        {{-- <div class="print">
            <button type="button" onclick="window.print()"
                    style="cursor:pointer;padding:10px 14px;border-radius:10px;border:1px solid var(--border);background:#fff;">
                Print / Save as PDF
            </button>
        </div> --}}

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-links">
                <a href="{{ $privacyUrl ?? route('privacy_policy') }}">📄 Privacy Policy</a>
                <a href="{{ $termsUrl ?? route('water_subscription') }}">⚖️ Terms of Service</a>
                <a href="{{ $deletionUrl ?? '#' }}">🗑️ Data Deletion Instructions</a>
            </div>
            <div>
                © {{ $copyrightYear ?? '2025' }}
                {{ $companyShort ?? 'PME (Project Management ERP)' }}. All rights reserved.
                <span class="muted">
                    | {{ $email ?? 'info@erpproject.co.ke' }}
                    | {{ $addressShort ?? 'Aztech Building, Mombasa Road, Nairobi, Kenya' }}
                </span>
            </div>
        </div>

    </div>
</div>
</body>
</html>
