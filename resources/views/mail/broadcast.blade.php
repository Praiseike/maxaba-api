<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>{{ $subject }}</title>
</head>

<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Helvetica,Arial,sans-serif;color:#333;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0"
          style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 18px rgba(16,24,40,0.06);">
          <tr>
            <td style="padding:24px 32px;text-align:center;background:#ffffff;">
              <img src="{{ url('images/logo.svg') }}" alt="{{ config('app.name', 'Maxaba') }} logo"
                style="max-width:160px;height:auto;display:block;margin:0 auto 16px;" />
              <h2 style="margin:0;color:#0f172a;font-size:20px;font-weight:600;">{{ $subject }}</h2>
            </td>
          </tr>

          <tr>
            <td style="padding:28px 32px 20px;">
              <div style="color:#475569;font-size:15px;line-height:22px;white-space:pre-wrap;">
                {!! nl2br(e($bodyMessage)) !!}
              </div>
            </td>
          </tr>

          <tr>
            <td style="padding:0 32px 24px;">
              <hr style="border:none;border-top:1px solid #eef2f7;margin:24px 0 18px;" />
              <p style="margin:0;color:#9aa4b2;font-size:12px;line-height:18px;">
                This is a broadcast message from Maxaba Administration.
              </p>
            </td>
          </tr>

          <tr>
            <td style="background:#f8fafc;padding:14px 32px;text-align:center;color:#94a3b8;font-size:12px;">
              <div style="max-width:520px;margin:0 auto;">
                <div style="margin-bottom:6px;">{{ config('app.name', 'Maxaba') }}</div>
                <div style="font-size:11px;color:#cbd5e1;">
                  {{ config('app.name') }} • <a href="mailto:{{ config('mail.from.address') }}"
                    style="color:#94a3b8;text-decoration:none;">{{ config('mail.from.address') }}</a>
                </div>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>

</html>
