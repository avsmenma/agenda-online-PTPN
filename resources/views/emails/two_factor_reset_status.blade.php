<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status Reset 2FA</title>
  </head>
  <body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f6f7fb;padding:24px 12px;">
      <tr>
        <td align="center">
          <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
            <tr>
              <td style="padding:18px 20px;background:#0f766e;color:#ffffff;">
                <div style="font-size:16px;font-weight:700;line-height:22px;">Agenda Online</div>
                <div style="font-size:13px;opacity:0.9;line-height:18px;">Status pengajuan reset 2FA</div>
              </td>
            </tr>

            <tr>
              <td style="padding:20px;">
                <div style="font-size:14px;line-height:20px;margin-bottom:14px;">Yth. <span style="font-weight:700;">{{ $user->name ?? 'User' }}</span>,</div>

                @if($status === 'approved')
                  <div style="font-size:14px;line-height:22px;margin-bottom:12px;color:#065f46;font-weight:700;">
                    Pengajuan reset 2FA Anda telah disetujui.
                  </div>
                  <div style="font-size:13px;line-height:20px;color:#374151;margin-bottom:14px;">
                    2FA pada akun Anda sudah dinonaktifkan. Silakan aktifkan kembali 2FA dari menu pengaturan 2FA setelah login.
                  </div>
                @else
                  <div style="font-size:14px;line-height:22px;margin-bottom:12px;color:#991b1b;font-weight:700;">
                    Pengajuan reset 2FA Anda ditolak.
                  </div>
                  <div style="font-size:13px;line-height:20px;color:#374151;margin-bottom:14px;">
                    Alasan penolakan:
                  </div>
                  <div style="font-size:13px;line-height:20px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 14px;white-space:pre-wrap;color:#111827;margin-bottom:14px;">
                    {{ $resetRequest->notes ?? '-' }}
                  </div>
                @endif

                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                  <tr>
                    <td style="padding:12px 14px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-size:13px;color:#374151;">Status</td>
                    <td style="padding:12px 14px;background:#ffffff;border-bottom:1px solid #e5e7eb;font-size:13px;color:#111827;font-weight:600;">{{ strtoupper($status) }}</td>
                  </tr>
                  <tr>
                    <td style="padding:12px 14px;background:#f9fafb;font-size:13px;color:#374151;">Waktu</td>
                    <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#111827;">{{ optional($resetRequest->handled_at ?? $resetRequest->updated_at)->format('d M Y H:i') }}</td>
                  </tr>
                </table>

                <div style="font-size:12px;line-height:18px;color:#6b7280;margin-top:18px;">Email ini dikirim otomatis.</div>
              </td>
            </tr>

            <tr>
              <td style="padding:14px 20px;background:#f9fafb;border-top:1px solid #e5e7eb;">
                <div style="font-size:12px;line-height:18px;color:#6b7280;">&copy; {{ date('Y') }} Agenda Online PTPN</div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>

