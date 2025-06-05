@extends('desktop.admin.layouts.admin')

@section('title', 'Daily Stock Check')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Daily Stock Check</h2>
        </div>
        <div class="col-md-6 text-end">
            <span class="text-muted">Date: {{ now()->format('F j, Y') }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.inventory.checks.store') }}" method="POST" id="stockCheckForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>SKU</th>
                                <th>Current Stock</th>
                                <th>Checked Quantity</th>
                                <th>Notes</th>
                                <th>Last Checked</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->sku }}</td>
                                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td>
                                        <input type="number" 
                                               name="quantities[{{ $item->id }}]" 
                                               class="form-control form-control-sm" 
                                               step="0.01" 
                                               min="0"
                                               value="{{ $item->dailyChecks->first()?->quantity_checked ?? '' }}"
                                               required>
                                        <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="notes[{{ $item->id }}]" 
                                               class="form-control form-control-sm"
                                               value="{{ $item->dailyChecks->first()?->notes ?? '' }}"
                                               placeholder="Optional notes">
                                    </td>
                                    <td>
                                        @if($item->dailyChecks->first())
                                            {{ $item->dailyChecks->first()->created_at->format('H:i') }}
                                        @else
                                            <span class="text-muted">Not checked today</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save All Checks
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('stockCheckForm').addEventListener('submit', function(e) {
    if (!confirm('Are you sure you want to save all stock checks?')) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection 