@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Campaign Management</h1>
        <button onclick="showCreateCampaignModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Create Campaign
        </button>
    </div>

    <!-- Campaign Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status-filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Date Range</label>
                <div class="flex gap-2 mt-1">
                    <input type="date" id="start-date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <input type="date" id="end-date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Segment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Offer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metrics</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="campaigns-table-body" class="bg-white divide-y divide-gray-200">
                <!-- Campaign rows will be dynamically inserted here -->
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4" id="pagination">
        <!-- Pagination will be dynamically inserted here -->
    </div>
</div>

<!-- Create Campaign Modal -->
<div id="create-campaign-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Create New Campaign</h3>
            <form id="create-campaign-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Segment</label>
                    <select name="segment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <!-- Segments will be dynamically loaded -->
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Offer Type</label>
                    <select name="offer_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="discount">Discount</option>
                        <option value="free_shipping">Free Shipping</option>
                        <option value="loyalty_points">Loyalty Points</option>
                        <option value="gift">Gift</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Offer Value</label>
                    <input type="text" name="offer_value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="datetime-local" name="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="datetime-local" name="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="hideCreateCampaignModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Create Campaign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Campaign Details Modal -->
<div id="campaign-details-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Campaign Details</h3>
            <div id="campaign-details-content">
                <!-- Campaign details will be dynamically inserted here -->
            </div>
            <div class="flex justify-end mt-4">
                <button onclick="hideCampaignDetailsModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let campaigns = [];

// Load campaigns on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCampaigns();
    loadSegments();
});

// Load campaigns with filters
function loadCampaigns(page = 1) {
    const status = document.getElementById('status-filter').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;

    let url = `/api/campaigns?page=${page}`;
    if (status) url += `&status=${status}`;
    if (startDate) url += `&start_date=${startDate}`;
    if (endDate) url += `&end_date=${endDate}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            campaigns = data.data;
            updateCampaignsTable();
            updatePagination(data);
        })
        .catch(error => console.error('Error loading campaigns:', error));
}

// Update campaigns table
function updateCampaignsTable() {
    const tableBody = document.getElementById('campaigns-table-body');
    tableBody.innerHTML = '';

    campaigns.forEach(campaign => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${campaign.name}</div>
                <div class="text-sm text-gray-500">${campaign.description || ''}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${campaign.segment?.name || 'N/A'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${campaign.offer_type}</div>
                <div class="text-sm text-gray-500">${campaign.offer_value}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(campaign.status)}">
                    ${campaign.status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div>Start: ${new Date(campaign.start_date).toLocaleDateString()}</div>
                <div>End: ${new Date(campaign.end_date).toLocaleDateString()}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div>Reached: ${campaign.reached_customers}</div>
                <div>Converted: ${campaign.converted_customers}</div>
                <div>ROI: ${campaign.roi}%</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="showCampaignDetails(${campaign.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                    <i class="fas fa-eye"></i>
                </button>
                <button onclick="updateCampaignStatus(${campaign.id})" class="text-green-600 hover:text-green-900 mr-3">
                    <i class="fas fa-play"></i>
                </button>
                <button onclick="deleteCampaign(${campaign.id})" class="text-red-600 hover:text-red-900">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Get status class for styling
function getStatusClass(status) {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'scheduled':
            return 'bg-blue-100 text-blue-800';
        case 'draft':
            return 'bg-gray-100 text-gray-800';
        case 'paused':
            return 'bg-yellow-100 text-yellow-800';
        case 'completed':
            return 'bg-purple-100 text-purple-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Load segments for campaign creation
function loadSegments() {
    fetch('/api/customer-segments')
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('select[name="segment_id"]');
            data.data.forEach(segment => {
                const option = document.createElement('option');
                option.value = segment.id;
                option.textContent = segment.name;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading segments:', error));
}

// Show create campaign modal
function showCreateCampaignModal() {
    document.getElementById('create-campaign-modal').classList.remove('hidden');
}

// Hide create campaign modal
function hideCreateCampaignModal() {
    document.getElementById('create-campaign-modal').classList.add('hidden');
}

// Show campaign details modal
function showCampaignDetails(campaignId) {
    fetch(`/api/campaigns/${campaignId}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('campaign-details-content');
            content.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium">Name</h4>
                        <p>${data.data.name}</p>
                    </div>
                    <div>
                        <h4 class="font-medium">Description</h4>
                        <p>${data.data.description || 'N/A'}</p>
                    </div>
                    <div>
                        <h4 class="font-medium">Segment</h4>
                        <p>${data.data.segment?.name || 'N/A'}</p>
                    </div>
                    <div>
                        <h4 class="font-medium">Offer</h4>
                        <p>${data.data.offer_type}: ${data.data.offer_value}</p>
                    </div>
                    <div>
                        <h4 class="font-medium">Status</h4>
                        <p>${data.data.status}</p>
                    </div>
                    <div>
                        <h4 class="font-medium">Dates</h4>
                        <p>Start: ${new Date(data.data.start_date).toLocaleString()}</p>
                        <p>End: ${new Date(data.data.end_date).toLocaleString()}</p>
                    </div>
                    <div>
                        <h4 class="font-medium">Metrics</h4>
                        <p>Reached: ${data.data.reached_customers}</p>
                        <p>Converted: ${data.data.converted_customers}</p>
                        <p>ROI: ${data.data.roi}%</p>
                    </div>
                </div>
            `;
            document.getElementById('campaign-details-modal').classList.remove('hidden');
        })
        .catch(error => console.error('Error loading campaign details:', error));
}

// Hide campaign details modal
function hideCampaignDetailsModal() {
    document.getElementById('campaign-details-modal').classList.add('hidden');
}

// Create campaign
document.getElementById('create-campaign-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('/api/campaigns', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        hideCreateCampaignModal();
        loadCampaigns();
        this.reset();
    })
    .catch(error => console.error('Error creating campaign:', error));
});

// Update campaign status
function updateCampaignStatus(campaignId) {
    const campaign = campaigns.find(c => c.id === campaignId);
    if (!campaign) return;

    const newStatus = campaign.status === 'active' ? 'paused' : 'active';
    
    fetch(`/api/campaigns/${campaignId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        loadCampaigns();
    })
    .catch(error => console.error('Error updating campaign status:', error));
}

// Delete campaign
function deleteCampaign(campaignId) {
    if (!confirm('Are you sure you want to delete this campaign?')) return;

    fetch(`/api/campaigns/${campaignId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        loadCampaigns();
    })
    .catch(error => console.error('Error deleting campaign:', error));
}

// Apply filters
function applyFilters() {
    loadCampaigns(1);
}

// Update pagination
function updatePagination(data) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if (data.last_page <= 1) return;

    const ul = document.createElement('ul');
    ul.className = 'flex justify-center space-x-2';

    // Previous button
    if (data.current_page > 1) {
        const li = document.createElement('li');
        li.innerHTML = `
            <button onclick="loadCampaigns(${data.current_page - 1})" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300">
                Previous
            </button>
        `;
        ul.appendChild(li);
    }

    // Page numbers
    for (let i = 1; i <= data.last_page; i++) {
        const li = document.createElement('li');
        li.innerHTML = `
            <button onclick="loadCampaigns(${i})" class="px-3 py-1 rounded ${i === data.current_page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'}">
                ${i}
            </button>
        `;
        ul.appendChild(li);
    }

    // Next button
    if (data.current_page < data.last_page) {
        const li = document.createElement('li');
        li.innerHTML = `
            <button onclick="loadCampaigns(${data.current_page + 1})" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300">
                Next
            </button>
        `;
        ul.appendChild(li);
    }

    pagination.appendChild(ul);
}
</script>
@endpush 