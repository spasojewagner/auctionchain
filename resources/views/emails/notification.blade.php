<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; max-width:560px; width:100%;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5,#6366f1); padding:24px 32px;">
                            <span style="color:#ffffff; font-size:22px; font-weight:bold;">⚖ AuctionChain</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h2 style="margin:0 0 16px; color:#0f172a; font-size:18px;">Imate novo obaveštenje</h2>
                            <p style="margin:0 0 24px; color:#334155; font-size:15px; line-height:1.6;">
                                {{ $notification->message }}
                            </p>

                            @if($notification->auction)
                                <a href="{{ route('auctions.show', $notification->auction) }}"
                                   style="display:inline-block; background:#6366f1; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:8px; font-weight:bold; font-size:14px;">
                                    Pogledaj aukciju
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; background:#0f172a; color:#94a3b8; font-size:12px;">
                            AuctionChain &copy; {{ date('Y') }} — Seminarski rad, FTN Čačak.<br>
                            Ovo je automatska poruka, ne odgovarajte na nju.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
