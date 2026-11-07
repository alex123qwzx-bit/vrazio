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
use Illuminate\Support\Str;

class WarrantyClaimController extends Controller
{
    /**
     * Show the warranty claim submission form for an eligible delivered order.
     */
    public function create(Request $request, string $orderNumber): View|RedirectResponse
    {
        $order = Order::with(['items.product', 'warrantyClaim'])
            ->where('order_number', trim($orderNumber))
            ->firstOrFail();

        // 0. Ownership check — only authorized order holder or admin can file
        if (! $order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to warranty claim. Please sign in with your patron account.');
        }

        // 1. If claim already exists, redirect to claim view
        if ($order->warrantyClaim) {
            return redirect()
                ->route('warranty.show', $order->warrantyClaim->claim_number)
                ->with('info', 'A warranty claim has already been registered for this order.');
        }

        // 2. Only delivered orders can claim warranty
        if ($order->fulfillment_status !== 'delivered') {
            return redirect()
                ->route('order.track', $order->order_number)
                ->with('error', 'Warranty claims can only be filed once the piece has completed transit and has been safely delivered to your hands.');
        }

        // Auto-select or compile primary product names
        $productTitles = $order->items->pluck('product_title')->implode(', ');

        return view('warranty.claim', [
            'order' => $order,
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
        $order = Order::with('warrantyClaim')->findOrFail($request->validated('order_id'));

        // 0. Ownership check
        if (! $order->isAccessibleBy(auth()->user())) {
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

        $uploadedDiskPaths = [];

        try {
            $claim = DB::transaction(function () use ($request, $order, &$uploadedDiskPaths): WarrantyClaim {
                // 1. Create main claim record
                $claim = WarrantyClaim::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id() ?? $order->user_id,
                    'customer_name' => $request->validated('customer_name'),
                    'phone' => $request->validated('phone'),
                    'email' => $request->validated('email'),
                    'product_name' => $request->validated('product_name') ?: $order->items->first()?->product_title,
                    'issue_type' => $request->validated('issue_type'),
                    'description' => $request->validated('description'),
                    'status' => 'submitted',
                    'is_checked' => false,
                ]);

                // 2. Validate and upload photos to private disk
                $photos = $request->file('photos', []);

                foreach ($photos as $index => $photo) {
                    // Deep MIME verification via finfo
                    $mime = $photo->getMimeType();
                    if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                        throw new Exception("File {$photo->getClientOriginalName()} has an invalid MIME type ({$mime}).");
                    }

                    $ext = strtolower($photo->getClientOriginalExtension());
                    if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                        $ext = 'jpg';
                    }

                    // Generate secure randomized filename
                    $safeFileName = sprintf(
                        'wc_%s_%d_%s.%s',
                        Str::slug($claim->claim_number),
                        $index + 1,
                        Str::random(16),
                        $ext
                    );

                    $storedRelativePath = 'warranty/' . $safeFileName;
                    \Illuminate\Support\Facades\Storage::disk('local')->putFileAs('warranty', $photo, $safeFileName);
                    $uploadedDiskPaths[] = $storedRelativePath;

                    // Insert photo record
                    WarrantyClaimImage::create([
                        'claim_id' => $claim->id,
                        'image_path' => $storedRelativePath,
                        'created_at' => now(),
                    ]);
                }

                return $claim;
            });

            // Format celebratory / reassuring luxury greeting message
            $greetingMessage = "Thank you, {$claim->customer_name}! You have successfully claimed your warranty with VRAZIO. Our master atelier team will review it within 24-48 hours.";

            return redirect()
                ->route('warranty.show', $claim->claim_number)
                ->with('warranty_success', $greetingMessage);

        } catch (Exception $e) {
            // Delete any uploaded private files on transaction rollback
            foreach ($uploadedDiskPaths as $diskPath) {
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($diskPath)) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($diskPath);
                }
            }

            return back()
                ->withInput()
                ->with('error', 'Unable to record warranty claim: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific warranty claim with status timeline, admin dossier notes, and photo gallery.
     */
    public function show(string $claimNumber): View
    {
        $claim = WarrantyClaim::with(['order.items.product', 'images', 'checkedByUser'])
            ->where('claim_number', trim($claimNumber))
            ->firstOrFail();

        if ($claim->order && ! $claim->order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to warranty claim dossier.');
        }

        return view('warranty.show', [
            'claim' => $claim,
            'order' => $claim->order,
        ]);
    }

    /**
     * Stream a private warranty claim photo securely to authorized viewers.
     */
    public function streamImage(int $id): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $image = WarrantyClaimImage::with('claim.order')->findOrFail($id);

        if (! $image->claim || ! $image->claim->order || ! $image->claim->order->isAccessibleBy(auth()->user())) {
            abort(403, 'Unauthorized access to warranty photo.');
        }

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($image->image_path)) {
            return response()->file(\Illuminate\Support\Facades\Storage::disk('local')->path($image->image_path));
        }

        $legacyPublicPath = public_path($image->image_path);
        if (file_exists($legacyPublicPath)) {
            return response()->file($legacyPublicPath);
        }

        abort(404, 'Image not located in atelier storage.');
    }

    /**
     * Real-time AJAX endpoint to poll warranty status for an order.
     */
    public function checkStatus(string $orderNumber): JsonResponse
    {
        $order = Order::with('warrantyClaim')
            ->where('order_number', trim($orderNumber))
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (! $order->warrantyClaim) {
            return response()->json([
                'success' => true,
                'has_claim' => false,
                'eligible' => $order->fulfillment_status === 'delivered',
            ]);
        }

        $claim = $order->warrantyClaim;

        return response()->json([
            'success' => true,
            'has_claim' => true,
            'claim_number' => $claim->claim_number,
            'status' => $claim->status,
            'status_label' => $claim->status_label,
            'status_color' => $claim->status_color,
            'is_checked' => $claim->is_checked,
            'admin_note' => $claim->admin_note,
            'view_url' => route('warranty.show', $claim->claim_number),
            'updated_at' => $claim->updated_at?->diffForHumans(),
        ]);
    }
}
