@php
    // Prefer passed company details if available
    $companyName = $company->cname ?? config('app.name', 'ERP Project');
    $companyWebsite = $company->website_url ?? 'https://erpproject.co.ke';
@endphp

<p class="small center" style="line-height: 24px; margin-bottom: 20px; font-family: Arial, sans-serif; font-size: 13px; color: #7f8c8d;">
    © {{ date('Y') }}
    <a href="{{ $companyWebsite }}" target="_blank" style="color: #2c3e50; text-decoration: none; font-weight: 600;">
        {{ $companyName }}
    </a>.
    All Rights Reserved.
</p>

@if(!empty($company->address) || !empty($company->phone) || !empty($company->email))
    <p class="small center" style="line-height: 20px; font-size: 12px; color: #95a5a6;">
        @if(!empty($company->address))
            {{ $company->address }}<br>
        @endif
        @if(!empty($company->phone))
            📞 {{ $company->phone }}
        @endif
        @if(!empty($company->email))
            &nbsp;&nbsp;✉️ <a href="mailto:{{ $company->email }}" style="color: #95a5a6; text-decoration: none;">{{ $company->email }}</a>
        @endif
    </p>
@endif
