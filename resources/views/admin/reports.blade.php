@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('content')
<div id="admin-report-manager-app">
  <div>Test message: If you see this, Vue is not mounting.</div>
  <report-manager></report-manager>
</div>
@endsection

@push('scripts')
@vite('resources/js/app.js')
@endpush 