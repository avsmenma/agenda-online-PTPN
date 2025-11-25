<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeadlineNotification;
use Illuminate\Support\Facades\Auth;

class DeadlineNotificationController extends Controller
{
    /**
     * Get unread notifications for current user's handler
     */
    public function getNotifications(Request $request)
    {
        // Get current user's handler based on module
        $handler = $this->getHandlerFromModule($request);
        
        if (!$handler) {
            return response()->json(['notifications' => [], 'count' => 0]);
        }
        
        // Get notifications and filter out those where document is no longer in this handler
        $notifications = DeadlineNotification::with('dokumen')
            ->where('handler', $handler)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function($notification) use ($handler) {
                // Check if document still exists and is still in this handler
                if (!$notification->dokumen) {
                    // Document deleted, remove notification
                    $notification->delete();
                    return false;
                }
                
                // Check if document is still in this handler
                if ($notification->dokumen->current_handler !== $handler) {
                    // Document moved to another handler, remove notification
                    $notification->delete();
                    return false;
                }
                
                return true;
            })
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'dokumen_id' => $notification->dokumen_id,
                    'nomor_agenda' => $notification->dokumen->nomor_agenda ?? '-',
                    'status' => $notification->status,
                    'days_overdue' => $notification->days_overdue,
                    'deadline_at' => $notification->deadline_at->format('d/m/Y H:i'),
                    'color' => $notification->status === 'danger' ? 'red' : 'yellow',
                    'created_at' => $notification->created_at->format('d/m/Y H:i'),
                ];
            });
        
        return response()->json([
            'notifications' => $notifications->values(),
            'count' => $notifications->count(),
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = DeadlineNotification::findOrFail($id);
        $notification->update(['is_read' => true]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $handler = $this->getHandlerFromModule($request);
        
        if ($handler) {
            DeadlineNotification::where('handler', $handler)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Get handler from module parameter
     */
    private function getHandlerFromModule(Request $request)
    {
        $module = $request->get('module') ?? $request->header('X-Module') ?? session('module');
        
        $handlerMap = [
            'akutansi' => 'akutansi',
            'perpajakan' => 'perpajakan',
            'ibu_a' => 'ibu_a',
            'IbuA' => 'ibu_a',
        ];
        
        return $handlerMap[$module] ?? null;
    }
}
