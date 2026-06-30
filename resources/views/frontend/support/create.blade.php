@extends('layouts.app')

@section('title', 'Raise a Support Ticket - UniShare')

@section('content')

{{-- ── Page Header ─────────────────────────────────────────── --}}
<div style="margin-bottom: 32px;">
    <a href="{{ route('frontend.support.index') }}"
       style="color: var(--primary-blue); text-decoration: none; font-size: 0.9rem; font-weight: 600;">
        <i class="bi bi-arrow-left me-1"></i> Back to My Tickets
    </a>
    <h1 class="page-title mt-2">Raise a Support Ticket</h1>
    <p class="text-muted">Describe your issue and your University Admin will respond shortly.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-ticket-perforated me-2"></i> New Ticket
            </div>
            <div class="card-body" style="padding: 28px;">
                <form method="POST" action="{{ route('frontend.support.store') }}">
                    @csrf

                    {{-- Subject --}}
                    <div class="mb-4">
                        <label class="form-label" for="subject">
                            Subject <span style="color: #dc3545;">*</span>
                        </label>
                        <input
                            type="text"
                            id="subject"
                            name="subject"
                            class="form-control @error('subject') is-invalid @enderror"
                            placeholder="Brief summary of your issue"
                            value="{{ old('subject') }}"
                            maxlength="255"
                            required
                        >
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Category + Priority (side by side on md+) --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label" for="category">
                                Category <span style="color: #dc3545;">*</span>
                            </label>
                            <select id="category" name="category"
                                    class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">Select a category</option>
                                <option value="transaction_issue"  {{ old('category') === 'transaction_issue'  ? 'selected' : '' }}>Transaction Issue</option>
                                <option value="item_condition"     {{ old('category') === 'item_condition'     ? 'selected' : '' }}>Item Condition</option>
                                <option value="penalty_dispute"    {{ old('category') === 'penalty_dispute'    ? 'selected' : '' }}>Penalty Dispute</option>
                                <option value="user_behaviour"     {{ old('category') === 'user_behaviour'     ? 'selected' : '' }}>User Behaviour</option>
                                <option value="account_issue"      {{ old('category') === 'account_issue'      ? 'selected' : '' }}>Account Issue</option>
                                <option value="other"              {{ old('category') === 'other'              ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="priority">
                                Priority <span style="color: #dc3545;">*</span>
                            </label>
                            <select id="priority" name="priority"
                                    class="form-select @error('priority') is-invalid @enderror" required>
                                <option value="low"    {{ old('priority', 'medium') === 'low'    ? 'selected' : '' }}>Low — Not urgent</option>
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium — Needs attention</option>
                                <option value="high"   {{ old('priority', 'medium') === 'high'   ? 'selected' : '' }}>High — Urgent issue</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-4">
                        <label class="form-label" for="description">
                            Description <span style="color: #dc3545;">*</span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            class="form-control @error('description') is-invalid @enderror"
                            rows="6"
                            placeholder="Describe the issue in detail — what happened, when, and what you expected..."
                            maxlength="2000"
                            required
                        >{{ old('description') }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <small class="text-muted">Minimum 20 characters.</small>
                            @enderror
                            <small class="text-muted" id="desc-count">0 / 2000</small>
                        </div>
                    </div>

                    {{-- Optional context: link transaction --}}
                    <div class="mb-4">
                        <label class="form-label" for="transaction_id">
                            Linked Transaction
                            <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <select id="transaction_id" name="transaction_id"
                                class="form-select @error('transaction_id') is-invalid @enderror">
                            <option value="">Not related to a specific transaction</option>
                            @foreach ($userTransactions as $transaction)
                                <option value="{{ $transaction->id }}"
                                    {{ (old('transaction_id', $selectedTransaction) == $transaction->id) ? 'selected' : '' }}>
                                    #{{ $transaction->id }} — {{ Str::limit($transaction->item->title ?? 'Item', 35) }}
                                    ({{ ucfirst($transaction->type) }} · {{ $transaction->status }})
                                </option>
                            @endforeach
                        </select>
                        @error('transaction_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Helps the admin investigate faster.</small>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex gap-3 mt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-1"></i> Submit Ticket
                        </button>
                        <a href="{{ route('frontend.support.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar: tips --}}
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightbulb me-2"></i> Tips for a faster response
            </div>
            <div class="card-body" style="padding: 20px;">
                <ul style="padding-left: 18px; color: #555; line-height: 1.8; margin: 0;">
                    <li>Be specific — mention item names, dates, and amounts.</li>
                    <li>Link the related transaction if applicable.</li>
                    <li>Choose the right category so the admin can triage faster.</li>
                    <li>Set priority honestly — reserve <strong>High</strong> for urgent issues only.</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i> What happens next?
            </div>
            <div class="card-body" style="padding: 20px;">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @foreach ([
                        ['bi-send',          '#0d6efd', 'You submit the ticket'],
                        ['bi-eye',           '#6f42c1', 'Admin reviews your issue'],
                        ['bi-chat-dots',     '#fd7e14', 'Admin replies in the thread'],
                        ['bi-check-circle',  '#28a745', 'Issue resolved & ticket closed'],
                    ] as [$icon, $color, $label])
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: {{ $color }}22; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi {{ $icon }}" style="color: {{ $color }};"></i>
                            </div>
                            <small style="color: #555;">{{ $label }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-js')
<script>
    // Character counter for description
    const desc  = document.getElementById('description');
    const count = document.getElementById('desc-count');
    const update = () => count.textContent = desc.value.length + ' / 2000';
    desc.addEventListener('input', update);
    update();
</script>
@endsection