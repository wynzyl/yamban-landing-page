<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New inquiry · {{ config('site.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f7f5f0;font-family:Arial,Helvetica,sans-serif;color:#0b0c0e;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f5f0;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border:1px solid #e5e0d6;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:#0b0c0e;padding:24px 28px;">
                            <p style="margin:0;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:#ff5a1f;font-weight:bold;">New website inquiry</p>
                            <h1 style="margin:10px 0 0;font-size:22px;line-height:1.25;color:#f7f5f0;">{{ $inquiry->interestLabel() }}</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.6;">
                                <tr>
                                    <td style="padding:6px 0;width:130px;color:#6b6b6b;">Name</td>
                                    <td style="padding:6px 0;font-weight:bold;">{{ $inquiry->name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;color:#6b6b6b;">Email</td>
                                    <td style="padding:6px 0;"><a href="mailto:{{ $inquiry->email }}" style="color:#d8430f;">{{ $inquiry->email }}</a></td>
                                </tr>
                                @if ($inquiry->phone)
                                    <tr>
                                        <td style="padding:6px 0;color:#6b6b6b;">Phone</td>
                                        <td style="padding:6px 0;">{{ $inquiry->phone }}</td>
                                    </tr>
                                @endif
                                @if ($inquiry->company)
                                    <tr>
                                        <td style="padding:6px 0;color:#6b6b6b;">Team / Company</td>
                                        <td style="padding:6px 0;">{{ $inquiry->company }}</td>
                                    </tr>
                                @endif
                                @if ($inquiry->category)
                                    <tr>
                                        <td style="padding:6px 0;color:#6b6b6b;">Category</td>
                                        <td style="padding:6px 0;">{{ $inquiry->category->name }}</td>
                                    </tr>
                                @endif
                                @if ($inquiry->product)
                                    <tr>
                                        <td style="padding:6px 0;color:#6b6b6b;">Product</td>
                                        <td style="padding:6px 0;">{{ $inquiry->product->name }}</td>
                                    </tr>
                                @endif
                                @if ($inquiry->quantity)
                                    <tr>
                                        <td style="padding:6px 0;color:#6b6b6b;">Quantity</td>
                                        <td style="padding:6px 0;">{{ number_format($inquiry->quantity) }} pcs</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:6px 0;color:#6b6b6b;">Received</td>
                                    <td style="padding:6px 0;">{{ $inquiry->created_at?->format('M j, Y g:i A') }}</td>
                                </tr>
                            </table>

                            <div style="margin-top:24px;padding-top:24px;border-top:1px solid #ece7dd;">
                                <p style="margin:0 0 8px;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#6b6b6b;font-weight:bold;">Message</p>
                                <p style="margin:0;white-space:pre-line;font-size:15px;line-height:1.7;">{{ $inquiry->message }}</p>
                            </div>

                            <a href="mailto:{{ $inquiry->email }}" style="display:inline-block;margin-top:28px;padding:12px 22px;border-radius:999px;background:#ff5a1f;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;">
                                Reply to {{ $inquiry->name }}
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px;background:#f7f5f0;border-top:1px solid #ece7dd;font-size:12px;color:#8a857b;">
                            Sent from the {{ config('site.name') }} website · Reference YBE-{{ str_pad((string) $inquiry->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
