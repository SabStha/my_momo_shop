@extends('layouts.app')

@section('title', 'Contact Us – Amako Momo')

@section('content')
<div class="container py-10 max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold mb-4">Contact Us</h1>
    <p class="text-gray-600 mb-2">Have a question or feedback? Reach us at:</p>
    <ul class="list-disc list-inside text-gray-700 space-y-1">
        <li>Email: <a href="mailto:hello@amakomomo.com" class="text-primary">hello@amakomomo.com</a></li>
        <li>Phone: +977-9800000000</li>
        <li>Location: Kathmandu, Nepal</li>
    </ul>
</div>
@endsection
