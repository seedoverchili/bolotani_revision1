@props(['title', 'preheader' => ''])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f6f4;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;color:#151b2b">
<span style="display:none;max-height:0;overflow:hidden;opacity:0">{{ $preheader }}</span>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f6f4;padding:28px 12px">
    <tr><td align="center">
        <table role="presentation" width="480" cellspacing="0" cellpadding="0" style="max-width:480px;width:100%">
            <tr><td style="padding:0 4px 16px">
                <table role="presentation" cellspacing="0" cellpadding="0"><tr>
                    <td style="background:#147a3a;border-radius:10px;width:36px;height:36px;text-align:center;color:#fff;font-size:18px;line-height:36px">&#127793;</td>
                    <td style="padding-left:10px;font-weight:800;font-size:18px;letter-spacing:-.3px;color:#111827">BOLO <span style="color:#147a3a">TANI</span><br><span style="font-size:8px;letter-spacing:1.4px;font-weight:700;color:#6b7280">MARKETPLACE AGRIBISNIS LOKAL</span></td>
                </tr></table>
            </td></tr>
            <tr><td style="background:#ffffff;border-radius:20px;border-top:4px solid #1f8a4c;padding:30px 30px 26px;box-shadow:0 8px 24px rgba(15,23,42,.06)">
                {{ $slot }}
            </td></tr>
            <tr><td style="padding:18px 8px 0;text-align:center;font-size:11px;line-height:17px;color:#8a93a3">
                Email ini dikirim otomatis oleh Bolo Tani. Butuh bantuan? WhatsApp Admin +62 812-3456-7890.<br>
                &copy; 2026 Bolo Tani &middot; Didukung oleh Politeknik Negeri Banyuwangi &middot; Kementerian Pertanian &middot; Persemaian Ramadhan
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
