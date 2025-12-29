<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{
    /**
     * List orders for admin with filters and pagination.
     */
    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($w) use ($q) {
                $w->where('id', 'like', "%{$q}%")
                  ->orWhere('customer_name', 'like', "%{$q}%")
                  ->orWhere('customer_email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->get('assigned_to'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(200, $perPage));

        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders)->response()->getData(true),
        ]);
    }

    /**
     * Show order details.
     */
    public function show($id)
    {
        try {
            $order = Order::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['success' => true, 'data' => new OrderResource($order)]);
    }

    /**
     * Update basic order fields (status, tracking, note) by admin.
     */
    public function update(OrderRequest $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $data = $request->validated();

        if (isset($data['status'])) {
            $order->status = $data['status'];
            $order->status_changed_at = now();
        }

        if (isset($data['tracking_number'])) {
            $order->tracking_number = $data['tracking_number'];
        }

        if (isset($data['assigned_to'])) {
            $order->assigned_to = $data['assigned_to'];
        }

        if (isset($data['admin_note'])) {
            $order->admin_note = $data['admin_note'];
        }

        $order->save();

        return response()->json(['success' => true, 'message' => 'Order updated', 'data' => new OrderResource($order)]);
    }

    /**
     * Change status via dedicated endpoint.
     */
    public function changeStatus(Request $request, $id)
    {
        $status = $request->get('status');
        if (!$status) {
            return response()->json(['success' => false, 'message' => 'Status is required'], 400);
        }

        try {
            $order = Order::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $order->status = $status;
        $order->status_changed_at = now();
        $order->save();

        return response()->json(['success' => true, 'message' => 'Order status updated', 'data' => new OrderResource($order)]);
    }

    /**
     * Assign order to admin user.
     */
    public function assign(Request $request, $id)
    {
        $adminId = $request->get('assigned_to');
        if (!$adminId) {
            return response()->json(['success' => false, 'message' => 'assigned_to is required'], 400);
        }

        try {
            $order = Order::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $order->assigned_to = $adminId;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Order assigned', 'data' => new OrderResource($order)]);
    }

    /**
     * Bulk update status for multiple orders.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('status');

        if (!is_array($ids) || empty($ids) || !$status) {
            return response()->json(['success' => false, 'message' => 'Invalid ids or status'], 400);
        }

        $updated = Order::whereIn('id', $ids)->update(['status' => $status, 'status_changed_at' => now()]);

        return response()->json(['success' => true, 'message' => "Updated status for {$updated} orders"]);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $order->status = 'cancelled';
        $order->cancel_reason = $request->get('reason');
        $order->status_changed_at = now();
        $order->save();

        return response()->json(['success' => true, 'message' => 'Order cancelled', 'data' => new OrderResource($order)]);
    }
}
