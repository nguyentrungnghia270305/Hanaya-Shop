<?php

namespace App\Http\Controllers;

use App\Constants\OrderStatus;
use App\Models\Order;
use App\Http\Requests\UpdateOrderStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Lấy danh sách tất cả đơn hàng
     */
    public function index()
    {
        try {
            $orders = Order::with(['user', 'orderDetail', 'payment'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đơn hàng thành công',
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy chi tiết một đơn hàng
     */
    public function show($id)
    {
        try {
            $order = Order::with(['user', 'orderDetail', 'payment', 'appliedCoupon'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết đơn hàng thành công',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Kiểm tra logic chuyển trạng thái hợp lệ
            $currentStatus = $order->status;
            $newStatus = $request->status;
            
            // Không cho phép chuyển từ delivered/cancelled sang trạng thái khác
            if (in_array($currentStatus, OrderStatus::final())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thay đổi trạng thái của đơn hàng đã hoàn thành hoặc đã hủy'
                ], 400);
            }

            // Log thay đổi trạng thái
            Log::info('Cập nhật trạng thái đơn hàng', [
                'order_id' => $order->id,
                'old_status' => $currentStatus,
                'new_status' => $newStatus,
                'user_id' => $order->user_id,
            ]);

            $order->status = $newStatus;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi cập nhật trạng thái đơn hàng', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel($id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Chỉ cho phép hủy đơn hàng ở trạng thái pending hoặc processing
            if (!in_array($order->status, OrderStatus::cancellable())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại: ' . OrderStatus::getLabel($order->status)
                ], 400);
            }

            $oldStatus = $order->status;
            $order->status = OrderStatus::CANCELLED;
            $order->save();

            // Log hủy đơn hàng
            Log::info('Hủy đơn hàng', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'user_id' => $order->user_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hủy đơn hàng thành công',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            Log::error('Lỗi hủy đơn hàng', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách đơn hàng theo trạng thái
     */
    public function getByStatus($status)
    {
        try {
            // Validate status
            if (!OrderStatus::isValid($status)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trạng thái không hợp lệ. Các trạng thái hợp lệ: ' . implode(', ', OrderStatus::all())
                ], 400);
            }

            $orders = Order::with(['user', 'orderDetail', 'payment'])
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách đơn hàng thành công',
                'data' => $orders,
                'count' => $orders->count(),
                'status_label' => OrderStatus::getLabel($status)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy thống kê tổng quan đơn hàng theo trạng thái
     */
    public function statistics()
    {
        try {
            $stats = [];
            
            foreach (OrderStatus::all() as $status) {
                $count = Order::where('status', $status)->count();
                $totalAmount = Order::where('status', $status)->sum('total_price');
                
                $stats[] = [
                    'status' => $status,
                    'label' => OrderStatus::getLabel($status),
                    'count' => $count,
                    'total_amount' => $totalAmount
                ];
            }

            $totalOrders = Order::count();
            $totalRevenue = Order::whereNotIn('status', [OrderStatus::CANCELLED])->sum('total_price');

            return response()->json([
                'success' => true,
                'message' => 'Lấy thống kê đơn hàng thành công',
                'data' => [
                    'by_status' => $stats,
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}

