<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ActivityService;
use App\Services\EventService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Throwable;

class StatsController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly EventService $eventService,
    ) {}

    public function index(): JsonResponse
    {
        $pendingOrders = 0;
        $newMessages = 0;
        $upcomingEvents = 0;

        try {
            $newMessages = ContactMessage::query()->where('status', 'New')->count();
            $pendingOrders = $this->orderService->countByStatus('pending_payment')
                + $this->orderService->countByStatus('paid');
            $upcomingList = $this->eventService->getUpcomingEvents(100);
            $upcomingEvents = is_array($upcomingList) ? count($upcomingList) : 0;
        } catch (Throwable) {
        }

        return response()->json([
            'ok' => true,
            'pendingOrders' => $pendingOrders,
            'newMessages' => $newMessages,
            'upcomingEvents' => $upcomingEvents,
            'totalMembers' => 0,
            'monthlyRevenue' => 0,
            'galleryUploads' => 0,
        ]);
    }
}
