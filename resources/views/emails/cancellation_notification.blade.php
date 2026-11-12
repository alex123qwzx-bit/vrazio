<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancellation Update | VRAZIO</title>
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
                    @if($event === 'approved') background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;
                    @elseif($event === 'rejected') background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca;
                    @else background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a; @endif">
                    @if($event === 'approved') Cancellation Approved
                    @elseif($event === 'rejected') Cancellation Request Declined
                    @else Cancellation Request Received
                    @endif
                </span>

                <h2 style="color: #0E261C; font-size: 18px; margin: 0 0 16px 0; font-family: Georgia, serif;">
                    Dear {{ $order->client_name }},
                </h2>

                <p style="font-size: 14px; line-height: 1.6; color: #44403c; margin: 0 0 20px 0;">
                    @if($event === 'requested')
                        We have received your cancellation request for order <strong style="color: #0E261C;">#{{ $order->order_number }}</strong>. Our atelier team is reviewing the consignment status and will update you shortly.
                    @elseif($event === 'approved')
                        Your cancellation for order <strong style="color: #0E261C;">#{{ $order->order_number }}</strong> has been approved. The items have been safely returned to vault inventory, and any applicable refund has been queued.
                    @elseif($event === 'rejected')
                        We regret to inform you that your cancellation request for order <strong style="color: #0E261C;">#{{ $order->order_number }}</strong> could not be processed.
                        @if($cancellation->rejection_reason)
                            <br><br><strong>Atelier Reason:</strong> {{ $cancellation->rejection_reason }}
                        @endif
                    @endif
                </p>

                <!-- Order Summary Table -->
                <table width="100%" cellpadding="8" cellspacing="0" style="background-color: #FAF8F5; border: 1px solid #e7e5e4; border-radius: 6px; font-size: 13px; margin-bottom: 24px;">
                    <tr>
                        <td style="color: #78716c; border-bottom: 1px solid #e7e5e4;">Reference Docket:</td>
                        <td style="font-weight: bold; font-family: monospace; color: #0E261C; border-bottom: 1px solid #e7e5e4;">{{ $cancellation->cancellation_number }}</td>
                    </tr>
                    <tr>
                        <td style="color: #78716c; border-bottom: 1px solid #e7e5e4;">Order Number:</td>
                        <td style="font-weight: bold; font-family: monospace; color: #0E261C; border-bottom: 1px solid #e7e5e4;">#{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="color: #78716c; border-bottom: 1px solid #e7e5e4;">Cancellation Scope:</td>
                        <td style="color: #0E261C; border-bottom: 1px solid #e7e5e4;">{{ $cancellation->type === 'partial' ? 'Partial Item' : 'Full Consignment' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #78716c; border-bottom: 1px solid #e7e5e4;">Stated Reason:</td>
                        <td style="color: #0E261C; border-bottom: 1px solid #e7e5e4;">{{ $cancellation->reason }}</td>
                    </tr>
                    @if($cancellation->refund_amount > 0)
                    <tr>
                        <td style="color: #78716c;">Applicable Refund:</td>
                        <td style="font-weight: bold; color: #0E261C;">₹{{ number_format((float) $cancellation->refund_amount, 2) }}</td>
                    </tr>
                    @endif
                </table>

                <div style="text-align: center; margin-top: 32px;">
                    <a href="{{ url('/orders') }}" style="display: inline-block; padding: 12px 28px; background-color: #0E261C; color: #D4AF37; text-decoration: none; font-size: 12px; font-weight: bold; letter-spacing: 0.15em; text-transform: uppercase; border: 1px solid #D4AF37; border-radius: 4px;">
                        View Orders in Atelier Vault &rarr;
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
