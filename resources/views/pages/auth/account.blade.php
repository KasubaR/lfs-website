@extends('layouts.app')

@section('content')
<x-auth-form-card
  auth-title="My Account"
  auth-subtitle="Welcome back, {{ $user->name }}."
  breadcrumb-label="Account"
  :status="$status ?? null">

  @if($user->registered_at)
    <div class="auth-account-card">
      <div class="auth-account-card__label">Registered</div>
      <div class="auth-account-card__value">{{ $user->registered_at->format('j M Y g:i A') }}</div>
    </div>
  @endif

  <div class="auth-account-card">
    <div class="auth-account-card__label">Full name</div>
    <div class="auth-account-card__value">{{ $user->name }}</div>
  </div>

  <div class="auth-account-card">
    <div class="auth-account-card__label">Email</div>
    <div class="auth-account-card__value">{{ $user->email }}</div>
  </div>

  <div class="auth-account-card">
    <div class="auth-account-card__label">Phone</div>
    <div class="auth-account-card__value">{{ $user->phone ?? '—' }}</div>
  </div>

  @if($user->gender)
    <div class="auth-account-card">
      <div class="auth-account-card__label">Sex</div>
      <div class="auth-account-card__value">{{ ucfirst(str_replace('_', ' ', $user->gender)) }}</div>
    </div>
  @endif

  @if($user->nationality)
    <div class="auth-account-card">
      <div class="auth-account-card__label">Nationality</div>
      <div class="auth-account-card__value">{{ $user->nationality }}</div>
    </div>
  @endif

  @if($user->t_shirt_size)
    <div class="auth-account-card">
      <div class="auth-account-card__label">T-shirt size</div>
      <div class="auth-account-card__value">{{ $user->t_shirt_size }}</div>
    </div>
  @endif

  @if($user->town)
    <div class="auth-account-card">
      <div class="auth-account-card__label">Town</div>
      <div class="auth-account-card__value">{{ $user->town }}</div>
    </div>
  @endif

  @if($user->satellite)
    <div class="auth-account-card">
      <div class="auth-account-card__label">Satellite</div>
      <div class="auth-account-card__value">{{ $user->satellite->name }}</div>
    </div>
  @endif

  @if($membership)
    <div class="auth-account-card">
      <div class="auth-account-card__label">Membership number</div>
      <div class="auth-account-card__value">{{ $membership->membership_number }}</div>
    </div>

    <div class="auth-account-card">
      <div class="auth-account-card__label">Status</div>
      <div class="auth-account-card__value">
        <span class="auth-status-badge auth-status-badge--{{ $membership->status === 'active' ? 'active' : ($membership->status === 'draft' ? 'draft' : 'pending') }}">
          {{ str_replace('_', ' ', $membership->status) }}
        </span>
      </div>
    </div>

    @if($membership->plan)
      <div class="auth-account-card">
        <div class="auth-account-card__label">Plan</div>
        <div class="auth-account-card__value">
          {{ $membership->plan->name }} — K{{ number_format((float) $membership->plan->price) }}
          ({{ $membership->plan->duration_months }} months)
        </div>
      </div>
    @endif

    @if($membership->start_date && $membership->expiry_date)
      <div class="auth-account-card">
        <div class="auth-account-card__label">Coverage period</div>
        <div class="auth-account-card__value">
          {{ $membership->start_date->format('j M Y') }} — {{ $membership->expiry_date->format('j M Y') }}
        </div>
      </div>
    @endif

    @if($canContinuePayment)
      <div class="auth-account-card auth-account-card--payment">
        <p class="auth-account-card__payment-text">
          Your membership application is ready. Continue to payment to activate your membership.
        </p>
        <button type="button" class="btn btn-primary w-full justify-center" disabled aria-disabled="true">
          <i class="fas fa-credit-card mr-2" aria-hidden="true"></i> Continue to Payment
        </button>
        <p class="auth-account-card__payment-note">
          Online payment coming soon.
        </p>
      </div>
    @endif
  @else
    <div class="auth-account-card">
      <p class="auth-account-card__empty-text">
        You don't have a membership yet.
        <a href="{{ url('/membership/apply') }}" class="auth-account-card__link">Start an application</a>.
      </p>
    </div>
  @endif

  <p class="auth-links auth-links--account">
    <form action="{{ url('/logout') }}" method="post" class="auth-logout-form">
      @csrf
      <button type="submit" class="auth-logout-form__btn">Sign out</button>
    </form>
  </p>

</x-auth-form-card>
@endsection
