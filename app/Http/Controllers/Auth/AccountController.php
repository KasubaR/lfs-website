<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MembershipStatus;
use App\Http\Controllers\Concerns\ProvidesAuthViews;
use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Services\MemberOnboardingService;
use App\Services\MembershipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    use ProvidesAuthViews;

    public function __construct(
        private readonly MembershipService $membershipService,
        private readonly MemberOnboardingService $onboardingService,
    ) {}

    public function show(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if ($user->must_change_password) {
            return redirect()->route('password.change');
        }

        if (! Membership::query()->where('user_id', $user->id)->exists()) {
            return redirect()->route('membership.apply');
        }

        $user->load('satellite');

        $membership = Membership::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        return view('pages.auth.account', $this->authViewData([
            'title' => 'My Account',
            'description' => 'Your LFS member account and membership status.',
            'page' => 'auth',
            'user' => $user,
            'membership' => $membership,
            'canContinuePayment' => $membership !== null
                && in_array($membership->status, [MembershipStatus::Draft, MembershipStatus::PendingPayment], true),
        ]));
    }
}
