@extends('mobile.layout')

@section('content')

<!-- Action Cards -->
<div class="container mt-3">
  <div class="row g-3">
    <div class="col-6">
      <div class="card text-center bg-warning text-dark rounded-4 p-2">
        <div class="card-body">
          <h6 class="card-title fw-bold">Mobile Order</h6>
          <p class="card-text small">Pick up at the restaurant</p>
          <a href="#" class="btn btn-light btn-sm rounded-pill mt-2">Order Now</a>
        </div>
      </div>
    </div>
    <div class="col-6">
      <div class="card text-center bg-danger text-white rounded-4 p-2">
        <div class="card-body">
          <h6 class="card-title fw-bold">McDelivery</h6>
          <p class="card-text small">Delivered to your door</p>
          <a href="#" class="btn btn-light btn-sm text-danger rounded-pill mt-2">Delivery Order</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Product Grid -->
<div class="container mt-4">
  <h5 class="fw-bold mb-3">Featured</h5>
  <div class="row g-3">
    @for ($i = 0; $i < 4; $i++)
    <div class="col-6">
      <div class="card text-center border rounded-4 p-2">
        <img src="https://via.placeholder.com/100" class="card-img-top rounded mb-2">
        <h6 class="card-title small">Momo Combo {{ $i+1 }}</h6>
        <p class="card-text small text-muted">Rs. 150</p>
        <button class="btn btn-outline-secondary btn-sm rounded-pill">Add</button>
      </div>
    </div>
    @endfor
  </div>
</div>

@endsection
