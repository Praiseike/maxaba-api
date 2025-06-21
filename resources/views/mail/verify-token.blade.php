<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Your Email</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f6f6f6;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f6f6f6; padding: 20px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); padding: 30px;">
          <tr>
            <td style="text-align: center;">
              <h1 style="color: #333333; margin-bottom: 20px;">Email Verification</h1>
            </td>
          </tr>
          <tr>
            <td style="font-size: 16px; color: #555555;">
              <p>Dear User,</p>
              <p>Thank you for signing in. Please use the verification token below to complete your login process:</p>
              <p style="text-align: center; margin: 30px 0;">
                <span style="display: inline-block; background-color: #edf2f7; color: #2d3748; padding: 12px 24px; font-size: 20px; font-weight: bold; border-radius: 6px;">
                  {{ $token }}
                </span>
              </p>
              <p>If you did not request this, please ignore this email.</p>
              <p>Thank you,</p>
              <p><strong>The Team</strong></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
