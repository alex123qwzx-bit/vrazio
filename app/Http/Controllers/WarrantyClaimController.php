<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarrantyClaimRequest;
use App\Models\Order;
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimImage;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WarrantyClaimController extends Controller
{
    /**
     * Show the warranty claim submission form for an eligible delivered order.
     * Enforces active VRAZIO PRIVÉ CLUB membership requirement.
     */
    public function create(Request $request, string $orderNumber): View|RedirectResponse
    {
        // 0. Authentication requirement
        if (! auth()->check()) {
            return redirect()->route('login')->with('info', 'Please sign in to access warranty services.');
        }

        $user = auth()->user();

        $order = Order::with(['items.product', 'warrantyClaim'])
            ->where('order_number', trim($orderNumber))
            ->firstOrFail();

        // 1. Ownership check — only authorized order holder or admin can file
        if (! $order->isAccessibleBy($user)) {
            abort(403, 'Unauthorized access to warranty claim. Please sign in with your patron account.');
        }

        // 2. PRIVÉ CLUB Membership check
        if (! $user->isPriveMember()) {
            return view('warranty.members_only', [
                'order' => $order,
                'message' => 'Warranty claim access is an exclusive privilege of VRAZIO PRIVÉ CLUB members. Join our membership programme to access warranty claim services on eligible products.',
            ]);
        }

        $membership = $user->activeMembership();

        // 3. If claim already exists, redirect to claim view
        if ($order->warrantyClaim) {
            return redirect()
                ->route('warranty.show', $order->warrantyClaim->claim_number)
                ->with('info', 'A warranty claim has already been registered for this order.');
        }

        // 4. Only delivered orders can claim warranty
        if ($order->fulfillment_status !== 'delivered') {
            return redirect()
                ->route('order.track', $order->order_number)
                ->with('error', 'Warranty claims can only be filed once the piece has completed transit and has been safely delivered to your hands.');
        }

        // 5. Warranty eligibility & period validation
        $orderDate = $order->delivered_at ?? $order->created_at;
        $firstItem = $order->items->first();
        $product = $firstItem?->product;

        if ($product && ! $product->is_warranty_eligible) {
            return back()->with('error', 'The product in this order is not covered under our atelier warranty policy.');
        }

        $warrantyPeriodMonths = $product?->warranty_period_months ?? 12;
        if ($orderDate && $orderDate->copy()->addMonths($warrantyPeriodMonths)->isPast()) {
            return back()->with('error', "The warranty duration of {$warrantyPeriodMonths} months for this order has expired.");
        }

        // Auto-select or compile primary product names
        $productTitles = $order->items->pluck('product_title')->implode(', ');

        return view('warranty.claim', [
            'order' => $order,
            'membership' => $membership,
            'defaultProductName' => $productTitles,
            'issueTypes' => WarrantyClaim::ISSUE_TYPES,
        ]);
    }

    /**
     * Process and store the warranty claim with uploaded evidence photos in a secure transaction.
     * Evidence photos are stored on the private storage disk to prevent unauthorized public downloads.
     */
    public function store(StoreWarrantyClaimRequest $request): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Enforce active PRIVÉ CLUB membership
        if (! $user->isPriveMember()) {
            return redirect()->route('prive-club.index')
                ->with('error', 'Warranty claim access is an exclusive privilege of active VRAZIO PRIVÉ CLUB members.');
        }

        $membership = $user->activeMembership();
        $order = Order::with(['items.product', 'warrantyClaim'])->findOrFail($request->validated('order_id'));

        // Ownership check
        if (! $order->isAccessibleBy($user)) {
            abort(403, 'Unauthorized access to warranty claim.');
        }

        if ($order->warrantyClaim) {
            return redirect()
                ->route('warranty.show', $order->warrantyClaim->claim_number)
                ->with('info', 'A warranty claim already exists for this order.');
        }

        if ($order->fulfillment_status !== 'delivered') {
            return back()->with('error', 'Warranty claims can only be filed for delivered orders.');
        }

        $firstItem = $order->items->first();
        $product = $firstItem?->product;

        $uploadedDiskPaths = [];

        try {
            $claim = DB::transaction(function () use ($request, $order, $membership, $product, $firstItem, &$uploadedDiskPaths): WarrantyClaim {
                // 1. Create main claim record
                $claim = WarrantyClaim::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id() ?? $order->user_id,
                    'member_id' => $membership->member_id,
                    'product_id' => $product?->id,
                    'product_sku' => $product?->sku ?? $firstItem?->product_sku,
                    'customer_name' => $request->validated('customer_name'),
                    'phone' => $request->validated('phone'),
                    'email' => $request->validated('email'),
                    'product_name' => $request->validated('product_name') ?: $order->items->first()?->product_title,
                    'issue_type' => $request->validated('issue_type'),
                    'remedy_requested' => $request->input('remedy_requested', 'complimentary_repair'),
                    'description' => $request->validated('description'),
                    'status' => 'submitted',
                    'is_checked' => false,
                ]);

                // 2. Validate and upload photos to private disk
                $photos = $request->file('photos', []);

                foreach ($photos as $index => $photo) {
                    $mime = $photo->getMimeType();
                    if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                        throw new Exception("File {$photo->getClientOriginalName()} has an invalid MIME type ({$mime}).");
                    }

                    $ext = strtolower($photo->getClientOriginalExtension());
                    if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                        $ext = 'jpg';
                    }

                    $safeFileName = sprintf(
                        'wc_%s_%d_%s.%s',
                        Str::slug($claim->claim_number),
                        $index + 1,
                        Str::random(16),
                        $ext
                    );

                    $storedPath = $photo->storeAs('warranty_claims/' . $claim->claim_number, $safeFileName, 'local');

                    if (! $storedPath) {
                        throw new Exception('Failed to write photo to private secure storage.');
                    }

                    $uploadedDiskPaths[] = $storedPath;

                    WarrantyClaimImage::create([
                        'claim_id' => $claim->id,
                        'file_path' => $storedPath,
                        'file_name' => $photo->getClientOriginalName(),
                        'mime_type' => $mime,
                        'file_size_bytes' => $photo->getSize(),
                    ]);
                }

                return $claim;
            });

            return redirect()
                ->route('warranty.show', $claim->claim_number)
                ->with('success', "Warranty claim {$claim->claim_number} successfully registered with VRAZIO Atelier.");

        } catch (Exception $e) {
            // Roll back any stored disk files if transaction failed
            foreach ($uploadedDiskPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            return back()
                ->withInput()
                ->with('error', 'Error submitting claim: ' . $e->getMessage());
        }
    }

    /**
     * View warranty claim status and atelier review notes.
     */
    public function show(string $claimNumber): View
    {
        $claim = WarrantyClaim::with(['order', 'images'])
            ->where('claim_number', trim($claimNumber))
            ->firstOrFail();

        if (! auth()->check() || (! auth()->user()->isAdmin() && auth()->id() !== $claim->user_id && auth()->id() !== $claim->order->user_id)) {
            abort(403, 'Unauthorized access to warranty claim details.');
        }

        return view('warranty.show', compact('claim'));
    }

    /**
     * Securely stream private warranty evidence photos to authenticated users.
     */
    public function streamImage(int $id): StreamedResponse
    {
        $image = WarrantyClaimImage::with('claim.order')->findOrFail($id);

        if (! auth()->check() || (! auth()->user()->isAdmin() && auth()->id() !== $image->claim->user_id && auth()->id() !== $image->claim->order->user_id)) {
            abort(403, 'Unauthorized access to warranty evidence photo.');
        }

        if (! Storage::disk('local')->exists($image->file_path)) {
            abort(404, 'Evidence photo not found on storage disk.');
        }

        return Storage::disk('local')->response(
            $image->file_path,
            $image->file_name,
            ['Content-Type' => $image->mime_type]
        );
    }

    /**
     * API check status for real-time customer polling.
     */
    public function checkStatus(string $orderNumber): JsonResponse
    {
        $order = Order::with('warrantyClaim')
            ->where('order_number', trim($orderNumber))
            ->first();

        if (! $order) {
            return response()->json(['exists' => false], 404);
        }

        if (! $order->warrantyClaim) {
            return response()->json([
                'exists' => false,
                'is_delivered' => $order->fulfillment_status === 'delivered',
            ]);
        }

        return response()->json([
            'exists' => true,
            'claim_number' => $order->warrantyClaim->claim_number,
            'status' => $order->warrantyClaim->status,
            'is_checked' => $order->warrantyClaim->is_checked,
            'admin_note' => $order->warrantyClaim->admin_note,
        ]);
    }
}
