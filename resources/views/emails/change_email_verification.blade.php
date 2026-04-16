<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Email Address Change</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #007bff;">Confirm Your Email Address Change</h2>

    <p>Hello {{ $userName }},</p>

    <p>You requested a change of email address from <strong>{{ $oldEmail }}</strong> to <strong>{{ $newEmail }}</strong>.</p>

    <p>For security reasons, we are sending you a message to your new address to confirm that it belongs to you. Your email address will be updated as soon as you open the URL sent to you in the message.</p>

    <p>The confirmation link will expire in 10 minutes.</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $verificationUrl }}" style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Confirm Email Change</a>
    </p>

    <p>If you did not request this change, please ignore this email.</p>

    <p>Best regards,<br>Family Tree Team</p>
</body>
</html>