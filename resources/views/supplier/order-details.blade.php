<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Momo Shop</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e3e3e3;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .order-number {
            background-color: #2563eb;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-sent { background-color: #fef3c7; color: #92400e; }
        .status-supplier_confirmed { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .order-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .order-details h3 {
            margin-top: 0;
            color: #2563eb;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e3e3e3;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e3e3e3;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-success {
            background-color: #059669;
            color: white;
        }
        .btn-success:hover {
            background-color: #047857;
        }
        .btn-danger {
            background-color: #dc2626;
            color: white;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }
        .btn-warning:hover {
            background-color: #d97706;
        }
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            border-bottom: 1px solid #e3e3e3;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .modal-title {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
        }
        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #666;
        }
        .close:hover {
            color: #000;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .quantity-input {
            width: 80px;
            text-align: center;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e3e3e3;
            color: #666;
            font-size: 14px;
        }
        .urgent {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #059669;
            color: #065f46;
        }
        .alert-danger {
            background-color: #fee2e2;
            border: 1px solid #dc2626;
            color: #991b1b;
        }
        .alert-warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Momo Shop</div>
            <div class="order-number">
                Order #{{ $order->order_number }}
                <span class="status-badge status-{{ $order->status }}">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
            <p>Inventory Order Details</p>
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

        <p>Dear <strong>{{ $supplier->contact_person }}</strong>,</p>

        <p>You have received an inventory order from <strong>{{ $order->branch->name }}</strong> that requires your attention.</p>

        @if($order->expected_delivery)
        <div class="urgent">
            <strong>⚠️ Expected Delivery:</strong> {{ \Carbon\Carbon::parse($order->expected_delivery)->format('M d, Y') }}
        </div>
        @endif

        <div class="order-details">
            <h3>Order Information</h3>
            <div class="detail-row">
                <span><strong>Order Number:</strong></span>
                <span>{{ $order->order_number }}</span>
            </div>
            <div class="detail-row">
                <span><strong>Branch:</strong></span>
                <span>{{ $order->branch->name }}</span>
            </div>
            <div class="detail-row">
                <span><strong>Order Date:</strong></span>
                <span>{{ $order->created_at->format('M d, Y H:i') }}</span>
            </div>
            @if($order->expected_delivery)
            <div class="detail-row">
                <span><strong>Expected Delivery:</strong></span>
                <span>{{ \Carbon\Carbon::parse($order->expected_delivery)->format('M d, Y') }}</span>
            </div>
            @endif
            @if($order->notes)
            <div class="detail-row">
                <span><strong>Notes:</strong></span>
                <span>{{ $order->notes }}</span>
            </div>
            @endif
            @if($order->supplier_confirmed_at)
            <div class="detail-row">
                <span><strong>Supplier Confirmed:</strong></span>
                <span>{{ $order->supplier_confirmed_at->format('M d, Y H:i') }}</span>
            </div>
            @endif
        </div>

        <h3>Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Requested Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->item->name }}</strong><br>
                        <small style="color: #666;">{{ $item->item->sku }}</small>
                    </td>
                    <td>{{ $item->quantity }} {{ $item->item->unit }}</td>
                    <td>Rs. {{ number_format($item->unit_price, 2) }}</td>
                    <td>Rs. {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>Total Amount:</strong></td>
                    <td><strong>Rs. {{ number_format($order->total_amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        @if($order->status === 'sent')
        <div class="action-buttons">
            <button onclick="openConfirmModal()" class="btn btn-success">
                ✅ Confirm Full Order
            </button>
            <button onclick="openPartialModal()" class="btn btn-warning">
                ⚠️ Partial Confirmation
            </button>
            <button onclick="openRejectModal()" class="btn btn-danger">
                ❌ Reject Order
            </button>
        </div>
        @elseif($order->status === 'supplier_confirmed')
        <div class="alert alert-success">
            <strong>Order Confirmed!</strong> You have confirmed this order. The branch will be notified and will proceed with receipt confirmation.
        </div>
        @elseif($order->status === 'rejected')
        <div class="alert alert-danger">
            <strong>Order Rejected!</strong> This order has been rejected. The branch has been notified of the rejection.
        </div>
        @endif

        <div class="footer">
            <p><strong>Momo Shop Inventory Management System</strong></p>
            <p>If you need assistance, please contact the main branch directly.</p>
        </div>
    </div>

    <!-- Confirm Full Order Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Confirm Full Order</span>
                <span class="close" onclick="closeConfirmModal()">&times;</span>
            </div>
            <form id="confirmForm">
                <div class="form-group">
                    <label for="confirmNotes">Additional Notes (Optional):</label>
                    <textarea id="confirmNotes" name="notes" placeholder="Any additional information about the delivery..."></textarea>
                </div>
                <div class="form-group">
                    <label for="confirmDeliveryDate">Expected Delivery Date:</label>
                    <input type="date" id="confirmDeliveryDate" name="delivery_date" value="{{ now()->addDays(3)->format('Y-m-d') }}" required>
                </div>
                <div style="text-align: center;">
                    <button type="button" onclick="closeConfirmModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Partial Confirmation Modal -->
    <div id="partialModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Partial Confirmation</span>
                <span class="close" onclick="closePartialModal()">&times;</span>
            </div>
            <form id="partialForm">
                <p>Please specify the quantities you can provide for each item:</p>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Requested</th>
                            <th>Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->item->name }}</strong><br>
                                <small style="color: #666;">{{ $item->item->sku }}</small>
                            </td>
                            <td>{{ $item->quantity }} {{ $item->item->unit }}</td>
                            <td>
                                <input type="number" 
                                       name="available_quantities[{{ $item->id }}]" 
                                       class="quantity-input" 
                                       min="0" 
                                       max="{{ $item->quantity }}" 
                                       value="{{ $item->quantity }}"
                                       required>
                                {{ $item->item->unit }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="form-group">
                    <label for="partialNotes">Reason for Partial Confirmation:</label>
                    <textarea id="partialNotes" name="notes" placeholder="Please explain why you cannot provide the full quantity..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="partialDeliveryDate">Expected Delivery Date:</label>
                    <input type="date" id="partialDeliveryDate" name="delivery_date" value="{{ now()->addDays(3)->format('Y-m-d') }}" required>
                </div>
                <div style="text-align: center;">
                    <button type="button" onclick="closePartialModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Partial Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Order Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Reject Order</span>
                <span class="close" onclick="closeRejectModal()">&times;</span>
            </div>
            <form id="rejectForm">
                <div class="form-group">
                    <label for="rejectReason">Reason for Rejection:</label>
                    <select id="rejectReason" name="reason" required>
                        <option value="">Select a reason...</option>
                        <option value="out_of_stock">Items out of stock</option>
                        <option value="discontinued">Items discontinued</option>
                        <option value="price_change">Price has changed</option>
                        <option value="delivery_issue">Cannot deliver to this location</option>
                        <option value="other">Other reason</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="rejectNotes">Additional Details:</label>
                    <textarea id="rejectNotes" name="notes" placeholder="Please provide additional details about the rejection..." required></textarea>
                </div>
                <div style="text-align: center;">
                    <button type="button" onclick="closeRejectModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Order</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openConfirmModal() {
            document.getElementById('confirmModal').style.display = 'block';
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
        }

        function openPartialModal() {
            document.getElementById('partialModal').style.display = 'block';
        }

        function closePartialModal() {
            document.getElementById('partialModal').style.display = 'none';
        }

        function openRejectModal() {
            document.getElementById('rejectModal').style.display = 'block';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Form submissions
        document.getElementById('confirmForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('/supplier/orders/{{ $order->id }}/confirm', this);
        });

        document.getElementById('partialForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('/supplier/orders/{{ $order->id }}/partial-confirm', this);
        });

        document.getElementById('rejectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('/supplier/orders/{{ $order->id }}/reject', this);
        });

        function submitForm(url, form) {
            const formData = new FormData(form);
            const data = {};
            formData.forEach((value, key) => {
                if (key.startsWith('available_quantities')) {
                    if (!data['available_quantities']) data['available_quantities'] = {};
                    const id = key.match(/\[(\d+)\]/)[1];
                    data['available_quantities'][id] = parseInt(value);
                } else {
                    data[key] = value;
                }
            });

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            });
        }
    </script>
</body>
</html> 