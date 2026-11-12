<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Refund Update | VRAZIO</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #FAF8F5; margin: 0; padding: 24px; color: #1c1917;">
    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <!-- Header -->
        <tr>
            <td style="background-color: #0E261C; padding: 32px 24px; text-align: center; border-bottom: 2px solid #D4AF37;">
                <h1 style="color: #D4AF37; margin: 0; font-size: 24px; letter-spacing: 0.2em; text-transform: uppercase; font-family: Georgia, serif;">V R A Z I O</h1>
                <p style="color: #FAF8F5; font-size: 11px; margin: 6px 0 0 0; letter-spacing: 0.15em; text-transform: uppercase;">Atelier &bull; Haute Joaillerie &bull; Luxury Couture</p>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 32px 24px;">
                <span style="display: inline-block; padding: 4px 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; border-radius: 4px; margin-bottom: 16px;
                    @if($event === 'completed') background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;
                    @elseif($event === 'failed') background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca;
                    @else background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; @endif">
                    @if($event === 'completed') Refund Processed Successfully
                    @elseif($event === 'failed') Refund Processing Failed
                    @else Refund Initiated
                    @endif
                </span>

                <h2 style="color: #0E261C; font-size: 18px; margin: 0 0 16px 0; font-family: Georgia, serif;">
                    Dear {{ $order->client_name }},
                </h2>

                <p style="font-size: 14px; line-height: 1.6; color: #44403c; margin: 0 0 20px 0;">
                    @if($event === 'initiated')
                        A refund of <strong style="color: #0E261C;">₹{{ number_format((float) $refund->amount, 2) }}</strong> has been initiated for your order <strong style="color: #0E261C;">#{{ $order->order_number }}</strong> through {{ strtoupper($refund->gateway) }}. Depending on your issuing bank or card provider, it typically takes 5–7 business days to reflect in your account.
                    @elseif($event === 'completed')
                        We are pleased to inform you that your refund of <strong style="color: #0E261C;">₹{{ number_format((float) $refund->amount, 2) }}</strong> for order <strong style="color: #0E261C;">#{{ $order->order_number }}</strong> has been successfully credited by our payment partner.
                    @elseif($event === 'failed')
                        We experienced an issue while processing the refund of <strong style="color: #0E261C;">₹{{ number_format((float) $refund->amount, 2) }}</strong> for order <strong style="color: #0E261C;">#{{ $order->order_number }}</strong>. Our accounts desk has been alerted and will resolve this immediately or reach out for alternative settlement details.
                        @if($refund->failure_reason)
                            <br><br><strong>Gateway Detail:</strong> {{ $refund->failure_reason }}
                        @endif
                    @endif
                </p>

                <!-- Refund Details -->
                <table width="100%" cellpadding="8" cellspacing="0" style="background-color: #FAF8F5; border: 1px solid #e7e5e4; border-radius: 6px; font-size: 13px; margin-bottom: 24px;">
                    <tr>
                        <td style="color: #78716c; border-bottom: 1px solid #e7e5e4;">Refund Reference:</td>
                        <td style="font-weight: bold; font-family: monospace; color: #0E261C; border-bottom: 1px solid #e7e5e4;">{{ $refund->refund_number }}</td>
                    </tr>
                    @if($refund->razorpay_refund_id)
                    <tr>
                        <td style="color: #78716c; border-bottom: 1px solid #e7e5e4;">Gateway ID:</td>
                        <td style="font-family: monospace; color: #0E261C; border-bottom: 1px solid #e7e5e4;">{{ $refund->razorpay_refund_id }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #78716c; border-bottom: 1px solid #e7e5e4;">Order Number:</td>
                        <td style="font-weight: bold; font-family: monospace; color: #0E261C; border-bottom: 1px solid #e7e5e4;">#{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="color: #78716c; border-bottom: 1px solid #e7e5e4;">Refund Amount:</td>
                        <td style="font-weight: bold; color: #0E261C; border-bottom: 1px solid #e7e5e4;">₹{{ number_format((float) $refund->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #78716c;">Settlement Route:</td>
                        <td style="color: #0E261C;">{{ strtoupper($refund->gateway) }} Original Source</td>
                    </tr>
                </table>

                <div style="text-align: center; margin-top: 32px;">
                    <a href="{{ url('/orders') }}" style="display: inline-block; padding: 12px 28px; background-color: #0E261C; color: #D4AF37; text-decoration: none; font-size: 12px; font-weight: bold; letter-spacing: 0.15em; text-transform: uppercase; border: 1px solid #D4AF37; border-radius: 4px;">
                        Track Status in Atelier Vault &rarr;
                    </a>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #FAF8F5; padding: 20px 24px; text-align: center; font-size: 11px; color: #78716c; border-top: 1px solid #e7e5e4;">
                Concierge Desk: +91 87912 27210 &bull; <a href="mailto:vrazioofficail@gmail.com" style="color: #0E261C; text-decoration: none;">vrazioofficail@gmail.com</a><br>
                &copy; {{ date('Y') }} VRAZIO Atelier. Timeless Beauty, Always Yours.
            </td>
        </tr>
    </table>
</body>
</html>
