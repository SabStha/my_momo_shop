@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Campaign Trigger</h1>
        <a href="{{ route('admin.campaigns.triggers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Triggers
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.campaigns.triggers.update', $trigger) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $trigger->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description', $trigger->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="trigger_type" class="form-label">Trigger Type</label>
                            <select class="form-select @error('trigger_type') is-invalid @enderror" 
                                    id="trigger_type" 
                                    name="trigger_type" 
                                    required>
                                <option value="">Select Type</option>
                                <option value="behavioral" {{ old('trigger_type', $trigger->trigger_type) == 'behavioral' ? 'selected' : '' }}>
                                    Behavioral
                                </option>
                                <option value="scheduled" {{ old('trigger_type', $trigger->trigger_type) == 'scheduled' ? 'selected' : '' }}>
                                    Scheduled
                                </option>
                                <option value="segment" {{ old('trigger_type', $trigger->trigger_type) == 'segment' ? 'selected' : '' }}>
                                    Segment
                                </option>
                            </select>
                            @error('trigger_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="campaign_type" class="form-label">Campaign Type</label>
                            <select class="form-select @error('campaign_type') is-invalid @enderror" 
                                    id="campaign_type" 
                                    name="campaign_type" 
                                    required>
                                <option value="">Select Type</option>
                                <option value="email" {{ old('campaign_type', $trigger->campaign_type) == 'email' ? 'selected' : '' }}>
                                    Email
                                </option>
                                <option value="sms" {{ old('campaign_type', $trigger->campaign_type) == 'sms' ? 'selected' : '' }}>
                                    SMS
                                </option>
                                <option value="push" {{ old('campaign_type', $trigger->campaign_type) == 'push' ? 'selected' : '' }}>
                                    Push Notification
                                </option>
                            </select>
                            @error('campaign_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="segment_id" class="form-label">Segment</label>
                            <select class="form-select @error('segment_id') is-invalid @enderror" 
                                    id="segment_id" 
                                    name="segment_id">
                                <option value="">Select Segment</option>
                                @foreach($segments as $segment)
                                    <option value="{{ $segment->id }}" 
                                            {{ old('segment_id', $trigger->segment_id) == $segment->id ? 'selected' : '' }}>
                                        {{ $segment->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('segment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="frequency" class="form-label">Frequency</label>
                            <select class="form-select @error('frequency') is-invalid @enderror" 
                                    id="frequency" 
                                    name="frequency" 
                                    required>
                                <option value="once" {{ old('frequency', $trigger->frequency) == 'once' ? 'selected' : '' }}>
                                    Once
                                </option>
                                <option value="daily" {{ old('frequency', $trigger->frequency) == 'daily' ? 'selected' : '' }}>
                                    Daily
                                </option>
                                <option value="weekly" {{ old('frequency', $trigger->frequency) == 'weekly' ? 'selected' : '' }}>
                                    Weekly
                                </option>
                                <option value="monthly" {{ old('frequency', $trigger->frequency) == 'monthly' ? 'selected' : '' }}>
                                    Monthly
                                </option>
                            </select>
                            @error('frequency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cooldown_period" class="form-label">Cooldown Period (hours)</label>
                            <input type="number" 
                                   class="form-control @error('cooldown_period') is-invalid @enderror" 
                                   id="cooldown_period" 
                                   name="cooldown_period" 
                                   value="{{ old('cooldown_period', $trigger->cooldown_period) }}" 
                                   min="1" 
                                   required>
                            @error('cooldown_period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input @error('is_active') is-invalid @enderror" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $trigger->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="trigger_condition" class="form-label">Trigger Conditions</label>
                            <div id="triggerConditions">
                                <!-- Dynamic conditions will be added here based on trigger type -->
                            </div>
                            @error('trigger_condition')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="campaign_template" class="form-label">Campaign Template</label>
                            <textarea class="form-control @error('campaign_template') is-invalid @enderror" 
                                      id="campaign_template" 
                                      name="campaign_template" 
                                      rows="5" 
                                      required>{{ old('campaign_template', $trigger->campaign_template) }}</textarea>
                            <small class="form-text text-muted">
                                Available variables: {name}, {email}, {last_order_date}, {total_orders}, {total_spent}
                            </small>
                            @error('campaign_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Trigger
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const triggerType = document.getElementById('trigger_type');
    const triggerConditions = document.getElementById('triggerConditions');
    const currentConditions = @json($trigger->trigger_condition);
    
    function updateTriggerConditions() {
        const type = triggerType.value;
        let html = '';
        
        switch(type) {
            case 'behavioral':
                html = `
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Condition Type</label>
                                <select class="form-select" name="trigger_condition[type]">
                                    <option value="purchase_frequency" ${currentConditions.type === 'purchase_frequency' ? 'selected' : ''}>
                                        Purchase Frequency
                                    </option>
                                    <option value="spending_amount" ${currentConditions.type === 'spending_amount' ? 'selected' : ''}>
                                        Spending Amount
                                    </option>
                                    <option value="inactivity" ${currentConditions.type === 'inactivity' ? 'selected' : ''}>
                                        Inactivity Period
                                    </option>
                                    <option value="cart_abandonment" ${currentConditions.type === 'cart_abandonment' ? 'selected' : ''}>
                                        Cart Abandonment
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Value</label>
                                <input type="number" 
                                       class="form-control" 
                                       name="trigger_condition[value]" 
                                       min="1" 
                                       value="${currentConditions.value || ''}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Time Period (days)</label>
                                <input type="number" 
                                       class="form-control" 
                                       name="trigger_condition[period]" 
                                       min="1" 
                                       value="${currentConditions.period || ''}">
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'scheduled':
                html = `
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Schedule Date</label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       name="trigger_condition[schedule_date]"
                                       value="${currentConditions.schedule_date || ''}">
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'segment':
                html = `
                    <div class="card">
                        <div class="card-body">
                            <div class="alert alert-info">
                                Segment conditions will be based on the selected segment above.
                            </div>
                        </div>
                    </div>
                `;
                break;
        }
        
        triggerConditions.innerHTML = html;
    }
    
    triggerType.addEventListener('change', updateTriggerConditions);
    updateTriggerConditions();
});
</script>
@endpush
@endsection 