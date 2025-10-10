<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Verify Your Email</title>
</head>

<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Helvetica,Arial,sans-serif;color:#333;">
  <!-- Preheader : hidden preview text -->
  <span
    style="display:none!important;visibility:hidden;mso-hide:all;font-size:1px;color:#f4f6f8;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
    Use the verification code below to complete your sign in to {{ config('app.name', 'Company') }}.
  </span>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0"
          style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 18px rgba(16,24,40,0.06);">
          <tr>
            <td style="padding:24px 32px;text-align:center;background:#ffffff;">
              <img src="{{ url('images/logo.svg') }}" alt="{{ config('app.name', 'Company') }} logo"
                style="max-width:160px;height:auto;display:block;margin:0 auto 16px;" />
              <h2 style="margin:0;color:#0f172a;font-size:20px;font-weight:600;">Verify your email</h2>
              <p style="margin:8px 0 0;color:#475569;font-size:14px;">Enter the code below to complete your sign in.
                This code expires in 10 minutes.</p>
            </td>
          </tr>

          <tr>
            <td style="padding:28px 32px 20px;text-align:center;">
              <div style="display:inline-block;background:#f1f5f9;border-radius:10px;padding:18px 24px;">
                <div
                  style="font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, 'Roboto Mono', monospace;font-size:28px;letter-spacing:4px;color:#0f172a;font-weight:700;">
                  {{ $token }}
                </div>
              </div>

              <p
                style="margin:18px 0 0;color:#64748b;font-size:13px;max-width:420px;margin-left:auto;margin-right:auto;">
                If you did not request this code, you can safely ignore this email.
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:0 32px 24px;">
              <hr style="border:none;border-top:1px solid #eef2f7;margin:0 0 18px;" />
              <p style="margin:0;color:#9aa4b2;font-size:12px;line-height:18px;">
                Need another code? Please request a new verification token from the app. This email was sent by
                <strong>{{ config('app.name', 'Company') }}</strong>.
              </p>
            </td>
          </tr>

          <tr>
            <td style="background:#f8fafc;padding:14px 32px;text-align:center;color:#94a3b8;font-size:12px;">
              <div style="max-width:520px;margin:0 auto;">
                <div style="margin-bottom:6px;">{{ config('app.name', 'Company') }}</div>
                <div style="font-size:11px;color:#cbd5e1;">
                  {{ config('app.name') }} • <a href="mailto:{{ config('mail.from.address') }}"
                    style="color:#94a3b8;text-decoration:none;">{{ config('mail.from.address') }}</a>
                </div>
              </div>
            </td>
          </tr>
        </table>

        <p style="font-size:12px;color:#9aa4b2;margin-top:12px;">If you have trouble viewing this email, copy the code
          into the app to verify your account.</p>
      </td>
    </tr>
  </table>
</body>

</html>