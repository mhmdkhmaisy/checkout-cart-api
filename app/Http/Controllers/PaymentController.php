<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        $paymentId = $request->query('payment_id') ?? $request->query('token');
        $orderId = $request->query('order_id');
        
        Log::info('Payment success page accessed', [
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'query_params' => $request->query()
        ]);

        $order = null;
        
        // Try to find order by payment_id first, then by order_id
        if ($paymentId) {
            $order = Order::where('payment_id', $paymentId)->with('items.product')->first();
        }
        
        if (!$order && $orderId) {
            $order = Order::where('id', $orderId)->with('items.product')->first();
        }

        if (!$order) {
            Log::warning('Order not found for payment success', [
                'payment_id' => $paymentId,
                'order_id' => $orderId
            ]);
            
            return view('payment.success', [
                'order' => null,
                'error' => 'Order not found'
            ]);
        }


        // Determine payment provider and create tracker URL for Coinbase
        $trackerUrl = null;
        if ($order->payment_method === 'coinbase' && $order->payment_id) {
            $trackerUrl = "https://commerce.coinbase.com/pay/{$order->payment_id}";
        }


        return view('payment.success', [
            'order' => $order,
            'trackerUrl' => $trackerUrl,
            'error' => null
        ]);
    }

    public function cancel(Request $request)
    {
        $paymentId = $request->query('payment_id') ?? $request->query('token');
        $orderId = $request->query('order_id');
        
        Log::info('Payment cancel page accessed', [
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'query_params' => $request->query()
        ]);

        $order = null;
        
        // Try to find order by payment_id first, then by order_id
        if ($paymentId) {
            $order = Order::where('payment_id', $paymentId)->with('items.product')->first();
        }
        
        if (!$order && $orderId) {
            $order = Order::where('id', $orderId)->with('items.product')->first();
        }

        // Update order status to failed if found
        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'failed']);
            Log::info('Order marked as failed due to payment cancellation', [
                'order_id' => $order->id
            ]);
        }

        return view('payment.cancel', [
            'order' => $order,
            'error' => $order ? null : 'Order not found'
        ]);
    }

    public function downloadPdf(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)->with('items.product')->first();
        
        if (!$order) {
            abort(404, 'Order not found');
        }

        Log::info('Payment receipt PDF download', [
            'order_id' => $orderId,
            'user_agent' => $request->userAgent()
        ]);

        // Determine tracker URL for Coinbase
        $trackerUrl = null;
        if ($order->payment_method === 'coinbase' && $order->payment_id) {
            $trackerUrl = "https://commerce.coinbase.com/charges/{$order->payment_id}";
        }

        $pdf = Pdf::loadView('payment.receipt-pdf', [
            'order' => $order,
            'trackerUrl' => $trackerUrl
        ]);

        return $pdf->download("payment-receipt-{$order->id}.pdf");
    }
}