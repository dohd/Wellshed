@extends('emails.layouts.app')

@section('content')
    <div class="content" style="padding: 20px; font-family: Arial, sans-serif; color: #504f4f; font-size: 16px; line-height: 24px;">
        <table border="0" width="80%" align="center" cellpadding="0" cellspacing="0" class="container590">

            <!-- Content Section -->
            <tr>
                <td align="left" style="padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
                    <div style="line-height: 22px;">
                        {!! $body !!}
                    </div>
                </td>
            </tr>

            <!-- Footer Section -->
            <tr>
                <td align="center" style="padding-top: 20px;">
                    @include('emails.layouts.footer')
                </td>
            </tr>

        </table>
    </div>
@endsection
