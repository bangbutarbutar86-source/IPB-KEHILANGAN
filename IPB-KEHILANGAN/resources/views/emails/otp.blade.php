<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP - IPB Kehilangan</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Inter',Arial,sans-serif">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08)">

                    {{-- Header --}}
                    <tr>
                        <td style="background:#314494;padding:32px 40px;text-align:center">
                            <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:12px;padding:10px 20px">
                                <span style="color:#ffffff;font-size:18px;font-weight:700;letter-spacing:2px">IPB KEHILANGAN</span>
                            </div>
                            <p style="color:rgba(255,255,255,0.7);font-size:13px;margin:10px 0 0">Institut Pertanian Bogor</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:36px 40px">
                            <p style="color:#374151;font-size:15px;margin:0 0 8px">Halo, <strong style="color:#314494">{{ $userName }}</strong> 👋</p>
                            <p style="color:#6b7280;font-size:14px;line-height:1.7;margin:0 0 28px">
                                Gunakan kode OTP di bawah ini untuk {{ $purposeText }} di <strong>IPB Kehilangan</strong>:
                            </p>

                            {{-- OTP Box --}}
                            <div style="background:#f0f4ff;border:2px dashed #314494;border-radius:12px;padding:28px;text-align:center;margin-bottom:28px">
                                <p style="color:#6b7280;font-size:12px;margin:0 0 12px;text-transform:uppercase;letter-spacing:1px;font-weight:600">Kode Verifikasi OTP</p>
                                <p style="color:#314494;font-size:44px;font-weight:700;letter-spacing:16px;margin:0;font-family:'Courier New',monospace">{{ $otp }}</p>
                                <p style="color:#9ca3af;font-size:12px;margin:12px 0 0">Berlaku selama <strong style="color:#ef4444">5 menit</strong></p>
                            </div>

                            {{-- Warning --}}
                            <div style="background:#fef9ec;border-left:4px solid #FFD700;border-radius:4px;padding:12px 16px;margin-bottom:24px">
                                <p style="color:#92400e;font-size:13px;margin:0;line-height:1.6">
                                    ⚠️ <strong>Jangan bagikan kode ini</strong> kepada siapapun, termasuk tim IPB Kehilangan. Kami tidak pernah meminta kode OTP kamu.
                                </p>
                            </div>

                            <p style="color:#6b7280;font-size:13px;line-height:1.7;margin:0">
                                Jika kamu tidak meminta kode ini, abaikan email ini dan jangan berikan kodenya kepada siapa pun.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8fafc;padding:20px 40px;border-top:1px solid #e5e7eb;text-align:center">
                            <p style="color:#9ca3af;font-size:12px;margin:0">
                                © {{ date('Y') }} IPB Kehilangan · Institut Pertanian Bogor<br>
                                Email ini dikirim otomatis, mohon tidak membalas email ini.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
