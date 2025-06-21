<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Created</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f6f6f6;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f6f6f6; padding: 20px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); padding: 30px;">
          <tr>
            <td style="text-align: center;">
              <h1 style="color: #333333; margin-bottom: 20px;">Account Created Successfully</h1>
            </td>
          </tr>
          <tr>
            <td style="font-size: 16px; color: #555555;">
              <p>Hi there</strong>,</p>
              <p>Your have been added as an agent on Maxaba platform. You can now log in using the following email:</p>
              <ul style="padding-left: 20px; margin-top: 10px; margin-bottom: 20px;">
                <li><strong>Email:</strong> {{ $user->email }}</li>
              </ul>
              <p>Please complete your profile after logging in for the first time.</p>
              <p>Thank you for joining us!</p>
              <p style="margin-top: 30px;">Best regards,</p>
              <p><strong>The Maxaba Team</strong></p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
