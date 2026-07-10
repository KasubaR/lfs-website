<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Response;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use App\Models\ContactMessage;
use App\Services\ActivityService;
use App\Services\EventService;
use App\Services\OrderService;

use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private readonly EventService $eventService,
        private readonly OrderService $orderService,
        private readonly ActivityService $activityService,
    ) {}

    public function index(): View
    {
        $newMessages = 0;
        $pendingOrders = 0;
        $upcomingEvents = [];

        try {
            $upcomingEvents = $this->eventService->getUpcomingEvents(5);
            $newMessages = ContactMessage::query()->where('status', 'New')->count();
            $pendingOrders = $this->orderService->countByStatus('pending_payment')
                + $this->orderService->countByStatus('paid');
        } catch (Throwable) {
        }

        $recentActivity = [];
        try {
            $recentActivity = $this->activityService->getRecentActivity(8);
        } catch (Throwable) {
        }

        return view('admin.dashboard.index', [
            'pageTitle' => 'Dashboard',
            'activePage' => 'dashboard',
            'adminUser' => $this->adminUser(),
            'stats' => [
                'totalMembers' => 0,
                'newMessages' => $newMessages,
                'upcomingEvents' => count($upcomingEvents),
                'pendingOrders' => $pendingOrders,
                'monthlyRevenue' => 0,
                'galleryUploads' => 0,
            ],
            'counts' => [
                'pendingMembers' => 0,
                'pendingOrders' => $pendingOrders,
                'pendingGallery' => 0,
                'newMessages' => $newMessages,
            ],
            'notifications' => ['unread' => 0, 'items' => []],
            'recentActivity' => $recentActivity,
            'pendingTasks' => ['orders' => $pendingOrders, 'events' => 0, 'gallery' => 0, 'memberships' => 0],
            'chartData' => ['members' => [], 'events' => [], 'sales' => [], 'gallery' => []],
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function activity(): View
    {
        $recentActivity = [];
        try {
            $recentActivity = $this->activityService->getRecentActivity(50);
        } catch (Throwable) {
        }

        return view('admin.activity.index', [
            'pageTitle' => 'Activity',
            'activePage' => 'dashboard',
            'adminUser' => $this->adminUser(),
            'counts' => [
                'pendingMembers' => 0,
                'pendingOrders' => 0,
                'pendingGallery' => 0,
                'newMessages' => 0,
            ],
            'breadcrumbs' => [
                ['label' => 'Admin', 'url' => '/admin/dashboard'],
                ['label' => 'Activity'],
            ],
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * @return array{name: string, email: string, role: string}
     */
    private function adminUser(): array
    {
        return [
            'name' => 'Admin User',
            'email' => config('admin.email'),
            'role' => 'admin',
        ];
    }
}
