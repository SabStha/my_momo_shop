<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SalesAnalyticsController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\WalletTopUpController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CampaignTriggerController;
use App\Http\Controllers\Admin\CampaignPerformanceController;
use App\Http\Controllers\Admin\ChurnPredictionController;
use App\Http\Controllers\Admin\ChurnExportController;
use App\Http\Controllers\Admin\CustomerAnalyticsController;
use App\Http\Controllers\Admin\WeeklyDigestController;
use App\Http\Controllers\Admin\AIAssistantController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RuleController;
use App\Http\Controllers\Admin\ReferralSettingsController;
use App\Http\Controllers\Admin\ClockController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\CashDrawerController;
use App\Http\Controllers\Admin\CashDrawerAlertController;
use App\Http\Controllers\Admin\PaymentManagerController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\UserBadgeController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryCategoryController;
use App\Http\Controllers\Admin\InventoryCheckController;
use App\Http\Controllers\Admin\InventoryStockCheckController;
use App\Http\Controllers\Admin\InventoryOperationsController;
use App\Http\Controllers\Admin\InventoryOrderController;
use App\Http\Controllers\Admin\SupplyOrderController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\AuditReportController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
| All routes here are protected by ['auth', 'admin'] middleware unless
| a narrower middleware is explicitly applied inside.
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // ── Dashboard ──────────────────────────────────────────────────────── 
    Route::get('/dashboard', function () {
        $selectedBranchId = session('selected_branch_id');
        if ($selectedBranchId) {
            return redirect()->route('admin.dashboard.branch', ['branch' => $selectedBranchId]);
        }
        $mainBranch = \App\Models\Branch::where('is_main', true)->first();
        if ($mainBranch) {
            return redirect()->route('admin.dashboard.branch', ['branch' => $mainBranch->id]);
        }
        return redirect()->route('admin.branches.index');
    })->name('dashboard');

    Route::get('/dashboard/{branch}', [AdminDashboardController::class, 'index'])->name('dashboard.branch');
    Route::get('/', fn() => redirect()->route('admin.branches.index'));

    // ── Products ────────────────────────────────────────────────────────── 
    Route::resource('products', AdminProductController::class)->names([
        'index'   => 'products.index',
        'create'  => 'products.create',
        'store'   => 'products.store',
        'show'    => 'products.show',
        'edit'    => 'products.edit',
        'update'  => 'products.update',
        'destroy' => 'products.destroy',
    ]);

    // ── Orders ──────────────────────────────────────────────────────────── 
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show.details');
    Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/orders/pending', [AdminOrderController::class, 'pending'])->name('orders.pending');
    Route::get('/orders/json', [AdminOrderController::class, 'getOrdersJson'])->name('orders.json');
    Route::post('/orders/process-payment', [AdminOrderController::class, 'processPayment'])->name('orders.process-payment');
    Route::post('/orders/{orderId}/mark-as-preparing', [AdminOrderController::class, 'markAsPreparing'])->name('orders.mark-as-preparing');
    Route::post('/orders/{orderId}/mark-out-for-delivery', [AdminOrderController::class, 'markOutForDelivery'])->name('orders.mark-out-for-delivery');
    Route::post('/orders/{order}/mark-as-delivered', [AdminOrderController::class, 'markAsDelivered'])->name('admin.orders.mark-as-delivered');

    // ── Settings ─────────────────────────────────────────────────────────── 
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    Route::get('/site-settings', [SiteSettingsController::class, 'index'])->name('site-settings.index');
    Route::put('/site-settings', [SiteSettingsController::class, 'update'])->name('site-settings.update');
    Route::patch('/site-settings/{setting}/toggle', [SiteSettingsController::class, 'toggle'])->name('site-settings.toggle');

    // ── Activity Logs ───────────────────────────────────────────────────── 
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show');

    // ── Notifications ───────────────────────────────────────────────────── 
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/churn-risks', [NotificationController::class, 'getChurnRisks'])->name('notifications.churn-risks');

    // ── Sales Analytics ─────────────────────────────────────────────────── 
    Route::get('/sales/overview', [SalesAnalyticsController::class, 'index'])->name('sales.overview');

    // ── Analytics ───────────────────────────────────────────────────────── 
    Route::get('/analytics', [CustomerAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/weekly-digest', [WeeklyDigestController::class, 'index'])->name('analytics.weekly-digest');
    Route::get('/analytics/journey-insights', [CustomerAnalyticsController::class, 'getJourneyInsights'])->name('analytics.journey-insights');
    Route::get('/analytics/segment-evolution', [CustomerAnalyticsController::class, 'getSegmentEvolution'])->name('analytics.segment-evolution');
    Route::post('/analytics/explain-trend', [CustomerAnalyticsController::class, 'explainTrend'])->name('analytics.explain-trend');
    Route::get('/analytics/segments', [CustomerAnalyticsController::class, 'segments'])->name('analytics.segments');
    Route::get('/analytics/churn', [CustomerAnalyticsController::class, 'churn'])->name('analytics.churn');
    Route::get('/analytics/segment-suggestions', [CustomerAnalyticsController::class, 'getSegmentSuggestions'])->name('analytics.segment-suggestions');
    Route::post('/analytics/generate-campaign', [CustomerAnalyticsController::class, 'generateCampaign'])->name('analytics.generate-campaign');
    Route::get('/analytics/export-segment/{segment}', [CustomerAnalyticsController::class, 'exportSegment'])->name('analytics.export-segment');
    Route::get('/analytics/journey-analysis', [CustomerAnalyticsController::class, 'journeyAnalysis'])->name('analytics.journey-analysis');
    Route::get('/analytics/retention-campaign/{customerId}', [CustomerAnalyticsController::class, 'generateRetentionCampaign'])->name('analytics.retention-campaign');
    Route::get('/analytics/trend-explanation', [CustomerAnalyticsController::class, 'getTrendExplanation'])->name('admin.analytics.trend-explanation');

    // AI Assistant
    Route::post('/api/customer-analytics/ai-assistant', [AIAssistantController::class, 'handleRequest'])->name('analytics.ai-assistant');

    // ── Campaigns ───────────────────────────────────────────────────────── 
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/performance', [CampaignPerformanceController::class, 'index'])->name('campaigns.performance');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('/campaigns/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaigns.edit');
    Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
    Route::put('/campaigns/{campaign}/status', [CampaignController::class, 'updateStatus'])->name('campaigns.status');
    Route::get('/campaigns/{campaign}/performance', [CampaignPerformanceController::class, 'show'])->name('campaigns.performance.show');

    // Campaign Triggers
    Route::prefix('campaigns/triggers')->name('campaigns.triggers.')->group(function () {
        Route::get('/', [CampaignTriggerController::class, 'index'])->name('index');
        Route::get('/create', [CampaignTriggerController::class, 'create'])->name('create');
        Route::post('/', [CampaignTriggerController::class, 'store'])->name('store');
        Route::get('/{trigger}/edit', [CampaignTriggerController::class, 'edit'])->name('edit');
        Route::put('/{trigger}', [CampaignTriggerController::class, 'update'])->name('update');
        Route::delete('/{trigger}', [CampaignTriggerController::class, 'destroy'])->name('destroy');
        Route::post('/{trigger}/toggle', [CampaignTriggerController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{trigger}/test', [CampaignTriggerController::class, 'testTrigger'])->name('test');
    });

    // ── Churn ────────────────────────────────────────────────────────────── 
    Route::get('/churn', [ChurnPredictionController::class, 'index'])->name('churn.index');
    Route::post('/churn/update', [ChurnPredictionController::class, 'updatePredictions'])->name('churn.update');
    Route::get('/churn/export', [ChurnPredictionController::class, 'export'])->name('churn.export');
    Route::get('/churn/export-data', [ChurnExportController::class, 'exportChurnData'])->name('churn.export-data');
    Route::get('/churn/{customer}', [ChurnPredictionController::class, 'show'])->name('churn.show');

    // ── Roles ────────────────────────────────────────────────────────────── 
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');


    // ── Referral Settings ────────────────────────────────────────────────── 
    Route::get('/referral-settings', [ReferralSettingsController::class, 'index'])->name('referral-settings.index');
    Route::post('/referral-settings', [ReferralSettingsController::class, 'update'])->name('referral-settings.update');

    // ── Branches ─────────────────────────────────────────────────────────── 
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::get('/branches/{branch}', [BranchController::class, 'show'])->name('branches.show');
    Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    Route::post('/branches/{branch}/toggle', [BranchController::class, 'toggleStatus'])->name('branches.toggle');
    Route::post('/branches/{branch}/switch', [BranchController::class, 'switch'])->name('branches.switch');
    Route::post('/branches/{branch}/verify', [BranchController::class, 'verify'])->name('branches.verify');
    Route::post('/branches/{branch}/reset-password', [BranchController::class, 'resetPassword'])->name('branches.reset-password');
    Route::post('/branches/select', [BranchController::class, 'select'])->name('branches.select');

    // ── Employees ─────────────────────────────────────────────────────────── 
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // ── Clock Management ──────────────────────────────────────────────────── 
    Route::get('/clock', [ClockController::class, 'index'])->name('clock.index');
    Route::get('/clock/report', [ClockController::class, 'report'])->name('clock.report');
    Route::post('/clock/in', [ClockController::class, 'clockIn'])->name('clock.in');
    Route::post('/clock/out', [ClockController::class, 'clockOut'])->name('clock.out');
    Route::post('/clock/break/start', [ClockController::class, 'startBreak'])->name('clock.break.start');
    Route::post('/clock/break/end', [ClockController::class, 'endBreak'])->name('clock.break.end');
    Route::get('/clock/employees/search', [ClockController::class, 'searchEmployees'])->name('clock.employees.search');
    Route::get('/clock/logs', [ClockController::class, 'getTimeLogs'])->name('clock.logs');

    // ── Inventory ─────────────────────────────────────────────────────────── 
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/manage', [InventoryOperationsController::class, 'manage'])->name('inventory.manage');
    Route::get('/inventory/stock-check', [InventoryStockCheckController::class, 'stockCheck'])->name('inventory.stock-check');
    Route::get('/inventory/weekly-checks', [InventoryStockCheckController::class, 'weeklyChecks'])->name('inventory.weekly-checks.index');
    Route::post('/inventory/weekly-checks', [InventoryStockCheckController::class, 'storeWeeklyChecks'])->name('inventory.weekly-checks.store');
    Route::get('/inventory/monthly-checks', [InventoryStockCheckController::class, 'monthlyChecks'])->name('inventory.monthly-checks.index');
    Route::post('/inventory/monthly-checks', [InventoryStockCheckController::class, 'storeMonthlyChecks'])->name('inventory.monthly-checks.store');
    Route::post('/inventory/bulk-update', [InventoryOperationsController::class, 'bulkUpdate'])->name('inventory.bulk-update');
    Route::get('/inventory/export', [InventoryOperationsController::class, 'export'])->name('inventory.export');
    Route::post('/inventory/import', [InventoryOperationsController::class, 'import'])->name('inventory.import');

    // Inventory Categories (before /{item} routes)
    Route::get('/inventory/categories', [InventoryCategoryController::class, 'index'])->name('inventory.categories.index');
    Route::get('/inventory/categories/create', [InventoryCategoryController::class, 'create'])->name('inventory.categories.create');
    Route::post('/inventory/categories', [InventoryCategoryController::class, 'store'])->name('inventory.categories.store');
    Route::get('/inventory/categories/{category}', [InventoryCategoryController::class, 'show'])->name('inventory.categories.show');
    Route::get('/inventory/categories/{category}/edit', [InventoryCategoryController::class, 'edit'])->name('inventory.categories.edit');
    Route::put('/inventory/categories/{category}', [InventoryCategoryController::class, 'update'])->name('inventory.categories.update');
    Route::delete('/inventory/categories/{category}', [InventoryCategoryController::class, 'destroy'])->name('inventory.categories.destroy');

    // Daily Stock Checks
    Route::get('/inventory/checks', [InventoryCheckController::class, 'index'])->name('inventory.checks.index');
    Route::post('/inventory/checks', [InventoryCheckController::class, 'store'])->name('inventory.checks.store');

    // Audit Reports
    Route::get('/inventory/audit-reports', [AuditReportController::class, 'index'])->name('inventory.audit-reports.index');
    Route::get('/inventory/audit-reports/detailed', [AuditReportController::class, 'detailedReport'])->name('inventory.audit-reports.detailed');
    Route::get('/inventory/audit-reports/sessions', [AuditReportController::class, 'auditSessions'])->name('inventory.audit-reports.sessions');
    Route::get('/inventory/audit-reports/export-pdf', [AuditReportController::class, 'exportPdf'])->name('inventory.audit-reports.export-pdf');
    Route::get('/inventory/audit-reports/export-excel', [AuditReportController::class, 'exportExcel'])->name('inventory.audit-reports.export-excel');

    // Inventory Orders (before /{item} routes)
    Route::get('/inventory/orders', [InventoryOrderController::class, 'index'])->name('inventory.orders.index');
    Route::get('/inventory/orders/list', [InventoryOrderController::class, 'ordersList'])->name('inventory.orders.list');
    Route::get('/inventory/orders/create', [InventoryOrderController::class, 'create'])->name('inventory.orders.create');
    Route::post('/inventory/orders', [InventoryOrderController::class, 'store'])->name('inventory.orders.store');
    Route::post('/inventory/forecast', [InventoryOrderController::class, 'generateForecast'])->name('inventory.forecast');
    Route::post('/inventory/orders/bulk-status-update', [InventoryOrderController::class, 'bulkStatusUpdate'])->name('inventory.orders.bulk-status-update');
    Route::get('/inventory/orders/supplier-view', [InventoryOrderController::class, 'supplierView'])->name('inventory.orders.supplier-view');
    Route::get('/inventory/orders/history', [InventoryOrderController::class, 'history'])->name('inventory.orders.history');
    Route::get('/inventory/orders/export', [InventoryOrderController::class, 'export'])->name('inventory.orders.export');
    Route::get('/inventory/orders/{order}', [InventoryOrderController::class, 'show'])->name('inventory.orders.show');
    Route::get('/inventory/orders/{order}/edit', [InventoryOrderController::class, 'edit'])->name('inventory.orders.edit');
    Route::put('/inventory/orders/{order}', [InventoryOrderController::class, 'update'])->name('inventory.orders.update');
    Route::delete('/inventory/orders/{order}', [InventoryOrderController::class, 'destroy'])->name('inventory.orders.destroy');
    Route::post('/inventory/orders/{order}/confirm', [InventoryOrderController::class, 'confirm'])->name('inventory.orders.confirm');
    Route::post('/inventory/orders/{order}/detailed-confirm', [InventoryOrderController::class, 'detailedConfirm'])->name('inventory.orders.detailed-confirm');
    Route::post('/inventory/orders/{order}/cancel', [InventoryOrderController::class, 'cancel'])->name('inventory.orders.cancel');
    Route::post('/inventory/orders/{order}/status', [InventoryOrderController::class, 'updateStatus'])->name('inventory.orders.status');
    Route::post('/inventory/orders/{order}/process-branch-order', [InventoryOrderController::class, 'processBranchOrder'])->name('inventory.orders.process-branch-order');
    Route::post('/inventory/orders/{order}/distribute', [InventoryOrderController::class, 'distribute'])->name('inventory.orders.distribute');

    // Inventory Item routes (must be after specific routes above)
    Route::get('/inventory/{item}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{item}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/{item}/adjust', [InventoryOperationsController::class, 'adjust'])->name('inventory.adjust');

    // ── Supply Orders ────────────────────────────────────────────────────── 
    Route::get('/supply/orders', [SupplyOrderController::class, 'index'])->name('supply.orders.index');
    Route::get('/supply/orders/create', [SupplyOrderController::class, 'create'])->name('supply.orders.create');
    Route::post('/supply/orders', [SupplyOrderController::class, 'store'])->name('supply.orders.store');
    Route::get('/supply/orders/{order}', [SupplyOrderController::class, 'show'])->name('supply.orders.show');
    Route::get('/supply/orders/{order}/edit', [SupplyOrderController::class, 'edit'])->name('supply.orders.edit');
    Route::put('/supply/orders/{order}', [SupplyOrderController::class, 'update'])->name('supply.orders.update');
    Route::delete('/supply/orders/{order}', [SupplyOrderController::class, 'destroy'])->name('supply.orders.destroy');
    Route::post('/supply/orders/{order}/send', [SupplyOrderController::class, 'sendToSupplier'])->name('supply.orders.send');
    Route::post('/supply/orders/{order}/receive', [SupplyOrderController::class, 'partialReceive'])->name('supply.orders.receive');

    // ── Suppliers ────────────────────────────────────────────────────────── 
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // ── Cash Drawer ──────────────────────────────────────────────────────── 
    Route::post('/cash-drawer/open', [CashDrawerController::class, 'openSession'])->name('cash-drawer.open');
    Route::post('/cash-drawer/close', [CashDrawerController::class, 'closeSession'])->name('cash-drawer.close');
    Route::get('/cash-drawer/status', [CashDrawerController::class, 'getStatus'])->name('cash-drawer.status');
    Route::get('/cash-drawer/session-sales', [CashDrawerController::class, 'getSessionSales'])->name('cash-drawer.session-sales');
    Route::post('/cash-drawer/adjust', [CashDrawerController::class, 'adjustDenominations'])->name('cash-drawer.adjust');
    Route::post('/cash-drawer/update-denominations', [CashDrawerController::class, 'updateDenominationsWithPassword'])->name('cash-drawer.update-denominations');
    Route::post('/cash-drawer/verify-password', [CashDrawerController::class, 'verifyPassword'])->name('cash-drawer.verify-password');
    Route::post('/cash-drawer/open-physical', [CashDrawerController::class, 'openPhysicalDrawer'])->name('cash-drawer.open-physical');
    Route::get('/cash-drawer/alerts', [CashDrawerAlertController::class, 'index'])->name('cash-drawer.alerts.index');
    Route::post('/cash-drawer/alerts/update', [CashDrawerAlertController::class, 'update'])->name('cash-drawer.alerts.update');
    Route::post('/cash-drawer/alerts/toggle', [CashDrawerAlertController::class, 'toggle'])->name('cash-drawer.alerts.toggle');
    Route::post('/cash-drawer/alerts/current', [CashDrawerAlertController::class, 'getCurrentAlerts'])->name('cash-drawer.alerts.current');

    // Legacy cash drawer route aliases (keep for backwards compat)
    Route::post('/admin/cash-drawer/open', [CashDrawerController::class, 'openDrawer'])->name('admin.cash-drawer.open.legacy');
    Route::post('/admin/cash-drawer/close', [CashDrawerController::class, 'closeDrawer'])->name('admin.cash-drawer.close.legacy');
    Route::get('/admin/cash-drawer/status', [CashDrawerController::class, 'getStatus'])->name('admin.cash-drawer.status.legacy');
    Route::get('/cash-drawer/session-sales-legacy', [CashDrawerController::class, 'getSessionSales'])->name('admin.cash-drawer.session-sales.legacy');
    Route::post('/cash-drawer/open-physical-legacy', [CashDrawerController::class, 'openPhysicalDrawer'])->name('admin.cash-drawer.open-physical.legacy');

    // Cash Denominations
    Route::get('/cash-denominations', [App\Http\Controllers\Admin\CashDenominationController::class, 'index'])->name('cash-denominations.index');
    Route::put('/cash-denominations/{denomination}', [App\Http\Controllers\Admin\CashDenominationController::class, 'update'])->name('cash-denominations.update');
    Route::get('/cash-denominations/{denomination}/history', [App\Http\Controllers\Admin\CashDenominationController::class, 'history'])->name('cash-denominations.history');
    Route::get('/cash-denominations/total', [App\Http\Controllers\Admin\CashDenominationController::class, 'getTotalCash'])->name('cash-denominations.total');

    // ── Payment Management ───────────────────────────────────────────────── 
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
        Route::get('/order/{order}', [AdminPaymentController::class, 'showOrder'])->name('order.show');
        Route::post('/order/{order}/process', [AdminPaymentController::class, 'processPayment'])->name('order.process');
        Route::post('/cash-drawer/open', [AdminPaymentController::class, 'openCashDrawer'])->name('cash-drawer.open');
        Route::post('/cash-drawer/close', [AdminPaymentController::class, 'closeCashDrawer'])->name('cash-drawer.close.payment');
        Route::get('/cash-drawer/status', [AdminPaymentController::class, 'getCashDrawerStatus'])->name('cash-drawer.status');
        Route::get('/wallet/{order}/balance', [AdminPaymentController::class, 'getWalletBalance'])->name('wallet.balance');
        Route::get('/wallet/number/{number}', [AdminPaymentController::class, 'getWalletBalanceByNumber'])->name('wallet.balance.number');
        Route::post('/broadcast-method', [AdminPaymentController::class, 'broadcastMethod'])->name('broadcast-method');
        Route::get('/payment-info', [AdminPaymentController::class, 'getPaymentInfo'])->name('payment-info');
    });

    // Admin payment routes (protected by payment.access middleware)
    Route::middleware(['payment.access'])->group(function () {
        Route::get('/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('admin.payments.index');
        Route::get('/payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('admin.payments.show');
        Route::post('/payments/{payment}/cancel', [App\Http\Controllers\Admin\PaymentController::class, 'cancel'])->name('admin.payments.cancel');
        Route::get('/payments/methods', [App\Http\Controllers\Admin\PaymentController::class, 'methods'])->name('admin.payments.methods');
        Route::get('/payments/sessions', [App\Http\Controllers\Admin\PaymentController::class, 'sessions'])->name('admin.payments.sessions');
    });
    Route::middleware(['payment.access'])->post('/payments', [AdminPaymentController::class, 'store'])->name('admin.payments.store');

    // view/print payment (admin)
    Route::get('/payment-view/{id}', [PaymentController::class, 'viewPayment'])->name('payments.show.admin');
    Route::get('/payment-receipt/{id}', [PaymentController::class, 'printReceipt'])->name('payments.receipt.admin');

    // Quick payment auth for admin
    Route::post('/payment/quick-auth', function (Request $request) {
        session(['payment_authenticated' => true, 'payment_user_id' => auth()->id()]);
        return response()->json(['success' => true, 'message' => 'Payment access granted']);
    })->name('payment.quick-auth');

    // ── Sessions Management ──────────────────────────────────────────────── 
    Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::post('/sessions', [SessionController::class, 'store'])->name('sessions.store');
    Route::get('/sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');
    Route::put('/sessions/{session}/close', [SessionController::class, 'close'])->name('sessions.close');

    // Legacy session routes (backwards compatibility)
    Route::post('/sessions-legacy/open', [SessionController::class, 'open'])->name('sessions.open');
    Route::post('/sessions-legacy/{session}/close', [SessionController::class, 'close'])->name('sessions.close.legacy');
    Route::get('/sessions-legacy', [SessionController::class, 'index'])->name('sessions.index.legacy');
    Route::get('/sessions-legacy/{session}', [SessionController::class, 'show'])->name('sessions.show.legacy');

    // ── Amako Credits / Wallet (admin, with wallet.auth) ──────────────────── 
    Route::prefix('amako-credits')->name('wallet.')->group(function () {
        // Login does NOT require wallet.auth
        Route::get('/topup/login', [WalletTopUpController::class, 'showLogin'])->name('topup.login');
        Route::post('/topup/login', [WalletTopUpController::class, 'login'])->name('topup.login.submit');
        Route::get('/topup/logout', [WalletTopUpController::class, 'logout'])->name('topup.logout');
    });

    Route::middleware(['wallet.auth'])->prefix('amako-credits')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/qr-generator', [WalletController::class, 'qrGenerator'])->name('qr-generator');
        Route::post('/generate-qr', [WalletController::class, 'generateQr'])->name('generate-qr');
        Route::get('/manage', [WalletController::class, 'manage'])->name('manage');
        Route::get('/export', [WalletController::class, 'export'])->name('export');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
        Route::get('/transactions/{user}', [WalletController::class, 'getTransactions'])->name('user-transactions');
        Route::get('/search', [WalletController::class, 'search'])->name('search');
        Route::get('/scan', [WalletController::class, 'scan'])->name('scan');
        Route::post('/process-code', [WalletController::class, 'processCode'])->name('process-code');
        Route::post('/top-up', [WalletController::class, 'topUp'])->name('top-up');
        Route::get('/topup', [WalletTopUpController::class, 'showTopUpForm'])->name('topup.form');
        Route::post('/topup/process', [WalletTopUpController::class, 'processTopUp'])->name('topup.process');
        Route::post('/topup/generate-qr', [WalletTopUpController::class, 'generateQR'])->name('topup.generate-qr');
    });

    // ── Offers Management ────────────────────────────────────────────────── 
    Route::resource('offers', App\Http\Controllers\Admin\OfferController::class);

    // ── AI Offers ────────────────────────────────────────────────────────── 
    Route::get('/ai-offers', [App\Http\Controllers\Admin\AIOfferController::class, 'index'])->name('ai-offers.index');
    Route::post('/ai-offers/generate', [App\Http\Controllers\Admin\AIOfferController::class, 'generate'])->name('ai-offers.generate');
    Route::post('/ai-offers/personalized', [App\Http\Controllers\Admin\AIOfferController::class, 'generatePersonalized'])->name('ai-offers.personalized');
    Route::get('/ai-offers/{offer}', [App\Http\Controllers\Admin\AIOfferController::class, 'show'])->name('ai-offers.show');
    Route::get('/ai-offers/{offer}/edit', [App\Http\Controllers\Admin\AIOfferController::class, 'edit'])->name('ai-offers.edit');
    Route::put('/ai-offers/{offer}', [App\Http\Controllers\Admin\AIOfferController::class, 'update'])->name('ai-offers.update');
    Route::delete('/ai-offers/{offer}', [App\Http\Controllers\Admin\AIOfferController::class, 'destroy'])->name('ai-offers.destroy');
    Route::post('/ai-offers/{offer}/toggle-status', [App\Http\Controllers\Admin\AIOfferController::class, 'toggleStatus'])->name('ai-offers.toggle-status');
    Route::get('/ai-offers/analytics/overview', [App\Http\Controllers\Admin\AIOfferController::class, 'analytics'])->name('ai-offers.analytics');
    Route::get('/ai-offers/users/search', [App\Http\Controllers\Admin\AIOfferController::class, 'getUsers'])->name('ai-offers.users');

    // ── Bulk Packages ────────────────────────────────────────────────────── 
    Route::resource('bulk-packages', App\Http\Controllers\Admin\BulkPackageController::class);
    Route::post('bulk-packages/{bulkPackage}/toggle-status', [App\Http\Controllers\Admin\BulkPackageController::class, 'toggleStatus'])->name('bulk-packages.toggle-status');

    // ── Rules Builder ────────────────────────────────────────────────────── 
    Route::get('/rules', [RuleController::class, 'index'])->name('rules.index');
    Route::get('/rules/create', [RuleController::class, 'create'])->name('rules.create');
    Route::post('/rules', [RuleController::class, 'store'])->name('rules.store');
    Route::get('/rules/{rule}/edit', [RuleController::class, 'edit'])->name('rules.edit');
    Route::put('/rules/{rule}', [RuleController::class, 'update'])->name('rules.update');
    Route::delete('/rules/{rule}', [RuleController::class, 'destroy'])->name('rules.destroy');
    Route::patch('/rules/{rule}/toggle', [RuleController::class, 'toggle'])->name('rules.toggle');

    // ── Badges ───────────────────────────────────────────────────────────── 
    Route::get('badges', [UserBadgeController::class, 'index'])->name('badges.index');
    Route::get('badges/{user}', [UserBadgeController::class, 'show'])->name('badges.show');

    // ── Integrations (Mailchimp, Twilio) ─────────────────────────────────── 
    Route::prefix('integrations')->group(function () {
        Route::get('/mailchimp/lists', [IntegrationController::class, 'getMailchimpLists'])->name('integrations.mailchimp.lists');
        Route::post('/mailchimp/sync', [IntegrationController::class, 'syncWithMailchimp'])->name('integrations.mailchimp.sync');
        Route::get('/twilio/groups', [IntegrationController::class, 'getTwilioGroups'])->name('integrations.twilio.groups');
        Route::post('/twilio/sync', [IntegrationController::class, 'syncWithTwilio'])->name('integrations.twilio.sync');
    });

    // ── Tables (dine-in table management) ──────────────────────────────────
    Route::resource('tables', App\Http\Controllers\Admin\TableController::class);
    Route::post('tables/{table}/toggle', [App\Http\Controllers\Admin\TableController::class, 'toggle'])->name('tables.toggle');

    // ── AI Popup ───────────────────────────────────────────────────────────
    Route::get('/ai-popup', fn() => view('admin.ai-popup.index'))->name('ai-popup.index');

    // ── Display Ads ────────────────────────────────────────────────────────
    Route::post('display-ads/reorder', [App\Http\Controllers\Admin\DisplayAdController::class, 'reorder'])->name('display-ads.reorder');
    Route::post('display-ads/{displayAd}/toggle', [App\Http\Controllers\Admin\DisplayAdController::class, 'toggleActive'])->name('display-ads.toggle');
    Route::resource('display-ads', App\Http\Controllers\Admin\DisplayAdController::class);

    // ── API (payment manager, admin) ─────────────────────────────────────── 
    Route::get('/api/payments', [App\Http\Controllers\Api\PaymentController::class, 'index'])->middleware('auth:sanctum');
    Route::get('/api/orders/{id}', [App\Http\Controllers\Api\PaymentController::class, 'getOrder'])->middleware('auth:sanctum');
    Route::post('/api/payments', [App\Http\Controllers\Api\PaymentController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/api/cash-drawer', [App\Http\Controllers\Api\PaymentController::class, 'getCashDrawer'])->middleware('auth:sanctum');
    Route::get('/api/cash-drawer/balance', [App\Http\Controllers\Api\PaymentController::class, 'getCashDrawerBalance'])->middleware('auth:sanctum');
    Route::post('/api/cash-drawer', [App\Http\Controllers\Api\PaymentController::class, 'updateCashDrawer'])->middleware('auth:sanctum');
});
