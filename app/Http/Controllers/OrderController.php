<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\UpdateOrderStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            if (in_array($currentStatus, ['delivered', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể thay đổi trạng thái của đơn hàng đã hoàn thành hoặc đã hủy'
                ], 400);
            }

            $order->status = $newStatus;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
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
            if (!in_array($order->status, ['pending', 'processing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại'
                ], 400);
            }

            $order->status = 'cancelled';
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Hủy đơn hàng thành công',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
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
            if (!in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trạng thái không hợp lệ'
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
                'count' => $orders->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
