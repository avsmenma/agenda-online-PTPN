<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengingat Deadline Dokumen</title>
  </head>
  <body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f6f7fb;padding:24px 12px;">
      <tr>
        <td align="center">
          <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
            <tr>
              <td style="padding:18px 20px;background:#0f766e;color:#ffffff;">
                <div style="font-size:16px;font-weight:700;line-height:22px;">Agenda Online</div>
                <div style="font-size:13px;opacity:0.9;line-height:18px;">Pengingat deadline dokumen</div>
              </td>
            </tr>

            <tr>
              <td style="padding:20px;">
                <div style="font-size:14px;line-height:20px;margin-bottom:14px;">
                  Yth. <span style="font-weight:700;">{{ $user->name ?? 'User' }}</span>,
                </div>

                <div style="font-size:14px;line-height:22px;margin-bottom:16px;color:#1f2937;">
                  Berikut pengingat terkait dokumen yang perlu ditindaklanjuti:
                </div>

                @php
                  $docName = $document->uraian_spp ?? ($document->nama_dokumen ?? 'Dokumen');
                  $deadlineValue = null;
                  if (!empty($document->role_deadline_at)) {
                      try {
                          $deadlineValue = \Illuminate\Support\Carbon::parse($document->role_deadline_at)->format('d M Y H:i');
                      } catch (\Throwable $e) {
                          $deadlineValue = (string) $document->role_deadline_at;
                      }
                  } elseif (!empty($document->deadline_at)) {
                      try {
                          $deadlineValue = \Illuminate\Support\Carbon::parse($document->deadline_at)->format('d M Y H:i');
                      } catch (\Throwable $e) {
                          $deadlineValue = (string) $document->deadline_at;
                      }
                  }
                @endphp

                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:16px;">
                  <tr>
                    <td style="padding:12px 14px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-size:13px;color:#374151;">Nama Dokumen</td>
                    <td style="padding:12px 14px;background:#ffffff;border-bottom:1px solid #e5e7eb;font-size:13px;color:#111827;font-weight:600;">{{ $docName }}</td>
                  </tr>
                  <tr>
                    <td style="padding:12px 14px;background:#f9fafb;border-bottom:1px solid #e5e7eb;font-size:13px;color:#374151;">Nomor Agenda</td>
                    <td style="padding:12px 14px;background:#ffffff;border-bottom:1px solid #e5e7eb;font-size:13px;color:#111827;">{{ $document->nomor_agenda ?? '-' }}</td>
                  </tr>
                  <tr>
                    <td style="padding:12px 14px;background:#f9fafb;font-size:13px;color:#374151;">Deadline</td>
                    <td style="padding:12px 14px;background:#ffffff;font-size:13px;color:#111827;">{{ $deadlineValue ?? '-' }}</td>
                  </tr>
                </table>

                <div style="font-size:14px;line-height:22px;margin-bottom:10px;font-weight:700;color:#111827;">Pesan</div>
                <div style="font-size:13px;line-height:20px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px 14px;white-space:pre-wrap;color:#111827;">
                  {{ $message }}
                </div>

                <div style="font-size:12px;line-height:18px;color:#6b7280;margin-top:18px;">
                  Email ini dikirim otomatis sebagai cadangan ketika notifikasi WhatsApp tidak dapat dikirim.
                </div>
              </td>
            </tr>

            <tr>
              <td style="padding:14px 20px;background:#f9fafb;border-top:1px solid #e5e7eb;">
                <div style="font-size:12px;line-height:18px;color:#6b7280;">
                  &copy; {{ date('Y') }} Agenda Online PTPN
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>

