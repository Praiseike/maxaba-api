<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Complete Your Registration</title>
</head>

<body style="margin:0;padding:0;background-color:#f4f6f8;font-family:Helvetica,Arial,sans-serif;color:#333;">
  <span
    style="display:none!important;visibility:hidden;mso-hide:all;font-size:1px;color:#f4f6f8;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">
    Finish setting up your Maxaba profile to start listing properties and matching with roommates.
  </span>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0"
          style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 18px rgba(16,24,40,0.06);">
          <tr>
            <td style="padding:24px 32px;text-align:center;background:#ffffff;">
              <img src="{{ url('images/logo.svg') }}" alt="{{ config('app.name', 'Maxaba') }} logo"
                style="max-width:160px;height:auto;display:block;margin:0 auto 16px;" />
              <h2 style="margin:0;color:#0f172a;font-size:20px;font-weight:600;">Complete your profile</h2>
              <p style="margin:8px 0 0;color:#475569;font-size:14px;">We noticed you started your registration on Maxaba but didn't finish setting up your profile.</p>
            </td>
          </tr>

          <tr>
            <td style="padding:28px 32px 20px;text-align:center;">
              <p style="margin:0 0 24px;color:#475569;font-size:15px;line-height:22px;">
                Complete your registration today to start finding roommates, list properties, or message other users!
              </p>
              
              <div style="display:inline-block;">
                <a href="{{ url('/') }}" style="background:#0f172a;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:600;font-size:15px;display:inline-block;">
                  Complete Registration
                </a>
              </div>
            </td>
          </tr>

          <tr>
            <td style="padding:0 32px 24px;">
              <hr style="border:none;border-top:1px solid #eef2f7;margin:0 0 18px;" />
              <p style="margin:0;color:#9aa4b2;font-size:12px;line-height:18px;">
                If you have any questions or did not intend to sign up, please disregard this email.
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
