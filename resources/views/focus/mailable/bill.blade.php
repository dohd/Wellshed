@extends('emails.layouts.app')

@section('content')
    <div class="content" style="padding: 20px; font-family: Arial, sans-serif; color: #504f4f; font-size: 16px; line-height: 24px;">
        <table border="0" width="80%" align="center" cellpadding="0" cellspacing="0" class="container590">

            <!-- Main Email Content -->
            <tr>
                <td align="left" style="padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
                    <div style="line-height: 22px;">
                        {!! $body !!}
                    </div>
                </td>
            </tr>

            <!-- Letterhead (Dynamic for Tenant) -->
            @if(!empty($company->logo))
                <tr>
                    <td align="center" style="padding-top: 40px;">
                        <table border="0" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <hr style="border: none; border-top: 2px solid #2c3e50; margin: 20px 0;">
                                </td>
                            </tr>
                            <tr>
                                
                                <td align="center">

                                    @php $image = "img/company/{$company->logo}" @endphp
                                    <img src="{{ asset('storage/' . $image) }}" width="100%"
                                    style="display: block; max-width: 600px; margin: 0 auto;"
                                    >
                                </td>
                            </tr>
                            @if(!empty($company->cname))
                                <tr>
                                    <td align="center" style="font-size: 14px; color: #7f8c8d; padding-top: 10px;">
                                        {{ $company->cname }}
                                        @if(!empty($company->tagline))
                                            — {{ $company->tagline }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            @endif

            <!-- Footer -->
            <tr>
                <td align="center" style="padding-top: 20px;">
                    @include('emails.layouts.footer')
                </td>
            </tr>

        </table>
    </div>
@endsection
