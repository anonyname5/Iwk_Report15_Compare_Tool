<!DOCTYPE html>
<html>
<head>
    <title>{{ $excel->file_name }} - IWK Finance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        .cost-center { margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .main-description { margin-bottom: 20px; }
        .description-type { margin-bottom: 15px; }
        .financial-data { font-size: 0.9rem; }
        .tab-content { padding-top: 20px; }
        .aging-data { font-size: 0.85rem; }
        .total-row { font-weight: bold; background-color: #f8f9fa; }
        .nav-tabs { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col">
                <h1>{{ $excel->data['report_title'] ?? 'Financial Report' }}</h1>
                <div class="d-flex justify-content-between">
                    <div>
                        <h5>File: {{ $excel->file_name }}</h5>
                        <p>Uploaded: {{ $excel->created_at->format('Y-m-d H:i:s') }}</p>
                        <p>Generated: {{ \Carbon\Carbon::parse($excel->data['generated_at'])->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('excel.upload.form') }}" class="btn btn-primary">Upload New</a>
                        <a href="{{ route('excel.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Stats -->
        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h5>Report Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <div class="alert alert-info">
                                    <strong>Total Cost Centers:</strong> {{ count($excel->data['cost_centers'] ?? []) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Developer Debug Section (remove in production) -->
        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Debug: Raw Financial Data for First Record</h5>
                        <button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#debugData">Toggle</button>
                    </div>
                    <div class="card-body collapse" id="debugData">
                        @if(!empty($excel->data['cost_centers']) && !empty($excel->data['cost_centers'][0]['main_descriptions']) && !empty($excel->data['cost_centers'][0]['main_descriptions'][0]['description_types']))
                            @php
                                $firstCostCenter = $excel->data['cost_centers'][0];
                                $firstMainDesc = $firstCostCenter['main_descriptions'][0];
                                $firstDescType = $firstMainDesc['description_types'][0];
                                $data = $firstDescType['data'] ?? [];
                            @endphp
                            <h6>Cost Center: {{ $firstCostCenter['code'] }}</h6>
                            <h6>Main Description: {{ $firstMainDesc['name'] }}</h6>
                            <h6>Description Type: {{ $firstDescType['type'] }}</h6>
                            <pre class="bg-light p-3 mt-2">{{ json_encode($data, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            <div class="alert alert-warning">No data available for debugging.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="structured-tab" data-bs-toggle="tab" data-bs-target="#structured" type="button" role="tab">
                    Structured View
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="raw-tab" data-bs-toggle="tab" data-bs-target="#raw" type="button" role="tab">
                    Raw JSON
                </button>
            </li>
        </ul>

        <div class="tab-content" id="reportTabsContent">
            <!-- Structured View Tab -->
            <div class="tab-pane fade show active" id="structured" role="tabpanel">
                <div class="row">
                    <div class="col">
                        <div class="accordion" id="costCentersAccordion">
                            @foreach($excel->data['cost_centers'] ?? [] as $index => $costCenter)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                            Cost Center: {{ $costCenter['code'] ?? 'Unknown' }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}">
                                        <div class="accordion-body">
                                            @php
                                                // Handle both associative and indexed array formats for main descriptions
                                                $mainDescriptions = $costCenter['main_descriptions'] ?? [];
                                                
                                                // Special handling for cost center 1W01840 if needed
                                                $isSpecialCase = ($costCenter['code'] ?? '') === '1W01840';
                                            @endphp
                                            
                                            @if(!empty($mainDescriptions))
                                                @foreach($mainDescriptions as $mainDescKey => $mainDesc)
                                                    @php
                                                        // Skip if this is not an array (malformed data)
                                                        if (!is_array($mainDesc)) continue;
                                                        
                                                        // For special case cost center, make additional adjustments if needed
                                                        if ($isSpecialCase) {
                                                            // Any special adjustments for this cost center go here
                                                        }
                                                    @endphp
                                                    <div class="main-description card">
                                                        <div class="card-header bg-light">
                                                            <h5>{{ $mainDesc['name'] ?? $mainDescKey }}</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <!-- Description Types Table -->
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered financial-data">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Type</th>
                                                                            <th>Billing Total</th>
                                                                            <th>Receipts Total</th>
                                                                            <th>CR Balance</th>
                                                                            <th>Accts</th>
                                                                            <th>Outstanding Balance</th>
                                                                            <th>Current Accts</th>
                                                                            <th>Current Balance</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @if(!empty($mainDesc['description_types']) && is_array($mainDesc['description_types']))
                                                                            @foreach($mainDesc['description_types'] as $descType)
                                                                                @if(is_array($descType) && isset($descType['data']))
                                                                                <tr>
                                                                                    <td><strong>{{ $descType['type'] ?? 'Unknown' }}</strong></td>
                                                                                    <td>{{ number_format($descType['data']['billing_total'] ?? 0, 2) }}</td>
                                                                                    <td>{{ number_format($descType['data']['receipts_total'] ?? 0, 2) }}</td>
                                                                                    <td>{{ number_format($descType['data']['crbal_total'] ?? 0, 2) }}</td>
                                                                                    <td>{{ $descType['data']['no_accounts'] ?? 0 }}</td>
                                                                                    <td>{{ number_format($descType['data']['outstanding_balance'] ?? 0, 2) }}</td>
                                                                                    <td>{{ $descType['data']['current_no_accounts'] ?? 0 }}</td>
                                                                                    <td>{{ number_format($descType['data']['current_balance'] ?? 0, 2) }}</td>
                                                                                </tr>
                                                                                @endif
                                                                            @endforeach
                                                                        @else
                                                                            <tr>
                                                                                <td colspan="8" class="text-center">No description types available</td>
                                                                            </tr>
                                                                        @endif
                                                                        
                                                                        <!-- Main Description Totals Row -->
                                                                        @if(!empty($mainDesc['main_total']) && is_array($mainDesc['main_total']))
                                                                            <tr class="total-row">
                                                                                <td><strong>Totals</strong></td>
                                                                                <td>{{ number_format($mainDesc['main_total']['billing_total'] ?? 0, 2) }}</td>
                                                                                <td>{{ number_format($mainDesc['main_total']['receipts_total'] ?? 0, 2) }}</td>
                                                                                <td>{{ number_format($mainDesc['main_total']['crbal_total'] ?? 0, 2) }}</td>
                                                                                <td>{{ $mainDesc['main_total']['no_accounts'] ?? 0 }}</td>
                                                                                <td>{{ number_format($mainDesc['main_total']['outstanding_balance'] ?? 0, 2) }}</td>
                                                                                <td>{{ $mainDesc['main_total']['current_no_accounts'] ?? 0 }}</td>
                                                                                <td>{{ number_format($mainDesc['main_total']['current_balance'] ?? 0, 2) }}</td>
                                                                            </tr>
                                                                        @endif
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            
                                                            <!-- Aging Detail Accordion -->
                                                            <div class="mt-3">
                                                                <h6>Aging Details</h6>
                                                                <div class="accordion" id="agingAccordion{{ $index }}_{{ $loop->index }}">
                                                                    @if(!empty($mainDesc['description_types']) && is_array($mainDesc['description_types']))
                                                                        @foreach($mainDesc['description_types'] as $typeIndex => $descType)
                                                                            @if(is_array($descType) && isset($descType['data']))
                                                                            <div class="accordion-item">
                                                                                <h2 class="accordion-header">
                                                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                                                            data-bs-target="#agingCollapse{{ $index }}_{{ $loop->parent->index }}_{{ $loop->index }}">
                                                                                        {{ $descType['type'] ?? 'Unknown' }} - Aging Breakdown
                                                                                    </button>
                                                                                </h2>
                                                                                <div id="agingCollapse{{ $index }}_{{ $loop->parent->index }}_{{ $loop->index }}" 
                                                                                     class="accordion-collapse collapse">
                                                                                    <div class="accordion-body p-0">
                                                                                        @if(!empty($descType['data']['aging']) && is_array($descType['data']['aging']))
                                                                                            <div class="table-responsive">
                                                                                                <table class="table table-sm table-striped table-bordered m-0 aging-data">
                                                                                                    <thead class="table-light">
                                                                                                        <tr>
                                                                                                            <th>Age Period</th>
                                                                                                            <th>No. of Accounts</th>
                                                                                                            <th>Balance</th>
                                                                                                        </tr>
                                                                                                    </thead>
                                                                                                    <tbody>
                                                                                                        @foreach($descType['data']['aging'] as $period => $agingData)
                                                                                                            @if(is_array($agingData))
                                                                                                            <tr>
                                                                                                                <td>{{ $period }}</td>
                                                                                                                <td>{{ $agingData['no_accounts'] ?? 0 }}</td>
                                                                                                                <td>{{ number_format($agingData['balance'] ?? 0, 2) }}</td>
                                                                                                            </tr>
                                                                                                            @endif
                                                                                                        @endforeach
                                                                                                    </tbody>
                                                                                                </table>
                                                                                            </div>
                                                                                        @else
                                                                                            <div class="p-3">No aging data available</div>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            @endif
                                                                        @endforeach
                                                                    @else
                                                                        <div class="alert alert-warning">No description types available for aging breakdown</div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="alert alert-warning">No main descriptions found for this cost center</div>
                                            @endif
                                            
                                            <!-- Cost Center Totals -->
                                            @if(!empty($costCenter['cost_center_total']) && is_array($costCenter['cost_center_total']))
                                                <div class="card mt-3">
                                                    <div class="card-header bg-secondary text-white">
                                                        <h5 class="mb-0">Cost Center Totals</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-sm">
                                                                <tr>
                                                                    <th>Billing Total</th>
                                                                    <td>{{ number_format($costCenter['cost_center_total']['billing_total'] ?? 0, 2) }}</td>
                                                                    <th>Receipts Total</th>
                                                                    <td>{{ number_format($costCenter['cost_center_total']['receipts_total'] ?? 0, 2) }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Outstanding Balance</th>
                                                                    <td>{{ number_format($costCenter['cost_center_total']['outstanding_balance'] ?? 0, 2) }}</td>
                                                                    <th>No. of Accounts</th>
                                                                    <td>{{ $costCenter['cost_center_total']['no_accounts'] ?? 0 }}</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-warning mt-3">No cost center totals available</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Raw JSON Tab -->
            <div class="tab-pane fade" id="raw" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>Raw JSON Data</h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3" style="max-height: 600px; overflow: auto;">{{ json_encode($excel->data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 