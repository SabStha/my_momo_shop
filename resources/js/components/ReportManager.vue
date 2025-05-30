<template>
  <div class="simple-report-dashboard">
    <h2 class="mb-4">Reports & Analytics</h2>
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card card-body mb-2">
          <b>Total Sales</b>
          <div class="display-6">Rs. 120,000</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-body mb-2">
          <b>Total Orders</b>
          <div class="display-6">340</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-body mb-2">
          <b>Total Profit</b>
          <div class="display-6">Rs. 25,000</div>
        </div>
      </div>
    </div>
    <h4 class="mt-4">Employee Working Hours</h4>
    <table class="table table-striped mb-4">
      <thead>
        <tr>
          <th>Employee</th>
          <th>Total Hours</th>
          <th>Overtime</th>
          <th>Total Pay</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Alice</td>
          <td>40</td>
          <td>5</td>
          <td>Rs. 22,000</td>
        </tr>
        <tr>
          <td>Bob</td>
          <td>38</td>
          <td>2</td>
          <td>Rs. 20,000</td>
        </tr>
      </tbody>
    </table>
    <h4 class="mt-4">Profit Analysis</h4>
    <table class="table table-striped mb-4">
      <thead>
        <tr>
          <th>Date</th>
          <th>Revenue</th>
          <th>Cost</th>
          <th>Profit</th>
          <th>Profit Margin</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>2024-06-01</td>
          <td>Rs. 7,000</td>
          <td>Rs. 4,000</td>
          <td>Rs. 3,000</td>
          <td>42.8%</td>
        </tr>
        <tr>
          <td>2024-06-02</td>
          <td>Rs. 9,000</td>
          <td>Rs. 5,000</td>
          <td>Rs. 4,000</td>
          <td>44.4%</td>
        </tr>
        <tr>
          <td>2024-06-03</td>
          <td>Rs. 11,000</td>
          <td>Rs. 6,000</td>
          <td>Rs. 5,000</td>
          <td>45.5%</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
// No logic needed
</script>

<style scoped>
.simple-report-dashboard {
  padding: 20px;
}
.card {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.table th {
  background-color: #f8f9fa;
  font-weight: 600;
}
.table td, .table th {
  vertical-align: middle;
}
</style> 