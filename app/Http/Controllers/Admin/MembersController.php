<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportMembersRequest;
use App\Models\MembershipImportBatch;
use App\Services\MemberImportService;
use App\Services\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class MembersController extends Controller
{
    public function __construct(
        private readonly MembershipService $membershipService,
        private readonly MemberImportService $importService,
    ) {}

    public function index(Request $request): View
    {
        $filterStatus = $request->query('status', '');
        $search = $request->query('search', '');

        $members = $this->membershipService->getMembersForAdmin([
            'filterStatus' => $filterStatus,
            'search' => $search,
            'limit' => 200,
        ]);

        return view('admin.members.list', [
            'pageTitle' => 'Members',
            'activePage' => 'members',
            'breadcrumbs' => [],
            'members' => $members,
            'filterStatus' => $filterStatus,
            'search' => $search,
            'counts' => [
                'unreadMessages' => 0,
                'pendingMembers' => count(array_filter($members, fn ($m) => ($m['status'] ?? '') === 'pending')),
                'pendingOrders' => 0,
                'pendingGallery' => 0,
            ],
        ]);
    }

    public function importForm(): View
    {
        $batches = MembershipImportBatch::query()
            ->orderByDesc('imported_at')
            ->limit(20)
            ->get();

        return view('admin.members.import', [
            'pageTitle' => 'Import Members',
            'activePage' => 'members',
            'breadcrumbs' => [
                ['label' => 'Members', 'url' => '/admin/members'],
                ['label' => 'Import'],
            ],
            'batches' => $batches,
            'counts' => [
                'unreadMessages' => 0,
                'pendingMembers' => 0,
                'pendingOrders' => 0,
                'pendingGallery' => 0,
            ],
        ]);
    }

    public function import(ImportMembersRequest $request): RedirectResponse
    {
        $importedBy = (string) session('admin_user', 'admin');

        try {
            $result = $this->importService->importFromFile(
                $request->file('import_file'),
                $importedBy,
                $request->boolean('send_welcome_email'),
            );
        } catch (Throwable $e) {
            return redirect()->route('admin.members.import')
                ->with('import_error', 'Import failed: '.$e->getMessage());
        }

        return redirect()->route('admin.members.import')
            ->with('import_result', $result);
    }

    public function rollback(int $batchId): RedirectResponse
    {
        try {
            $this->importService->rollbackBatch($batchId);
        } catch (Throwable $e) {
            return redirect()->route('admin.members.import')
                ->with('import_error', $e->getMessage());
        }

        return redirect()->route('admin.members.import')
            ->with('import_status', 'Import batch rolled back successfully.');
    }
}
