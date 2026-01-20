<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .button { background-color: #00A651; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello,</h2>
        <p>You have been invited to join the Emirates Park Zoo ticketing platform team.</p>
        
        <p>Please click the button below to accept your invitation and set up your account:</p>
        
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/admin/register?token=' . $invitation->token) }}" class="button">Accept Invitation</a>
        </p>
        
        <p>This link will expire in 7 days.</p>
        
        <p>If you did not expect this invitation, you can safely ignore this email.</p>
        
        <p>Best regards,<br>Emirates Park Zoo Team</p>
    </div>
</body>
</html>
