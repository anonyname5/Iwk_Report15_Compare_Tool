<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $comparison['comparison_name'] }} - Comparison Results | IWK Finance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .navbar {
            background-color: var(--dark-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .comparison-header {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .comparison-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .section-title {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .summary-card {
            text-align: center;
            padding: 1.5rem;
        }
        
        .summary-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .summary-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .summary-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            background-color: rgba(52, 152, 219, 0.1);
            font-weight: 600;
        }
        
        .table tr.expanded {
            background-color: rgba(0,0,0,0.02);
        }
        
        .file-info {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .file-item {
            flex: 1;
            min-width: 200px;
            background-color: #fff;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .file-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 30px;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }
        
        .file-badge-1 {
            background-color: rgba(52, 152, 219, 0.15);
            color: #2980b9;
        }
        
        .file-badge-2 {
            background-color: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }
        
        .toggle-details {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--primary-color);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .toggle-details:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .detail-row {
            background-color: rgba(0,0,0,0.02);
            border-top: 1px dashed #ddd;
        }
        
        .difference-item {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            background-color: white;
            border-left: 3px solid #ddd;
        }
        
        .difference-item.increased {
            border-left-color: var(--success-color);
        }
        
        .difference-item.decreased {
            border-left-color: var(--danger-color);
        }
        
        .difference-value {
            font-weight: 600;
        }
        
        .percentage-badge {
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .percentage-positive {
            background-color: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }
        
        .percentage-negative {
            background-color: rgba(231, 76, 60, 0.15);
            color: #c0392b;
        }
        
        .main-description-row {
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        
        .main-description-row.has-differences {
            border-left-color: var(--warning-color);
        }
        
        .main-description-row:hover {
            background-color: rgba(0,0,0,0.02);
        }
        
        .cost-center-header {
            background-color: rgba(52, 152, 219, 0.05);
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .badge-exists {
            background-color: rgba(46, 204, 113, 0.15);
            color: var(--success-color);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .badge-missing {
            background-color: rgba(231, 76, 60, 0.15);
            color: var(--danger-color);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .cost-center-card {
            margin-bottom: 2rem;
        }
        
        .cost-center-card .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            color: var(--primary-color);
            margin-bottom: 1rem;
            transition: all 0.2s;
        }
        
        .back-link:hover {
            transform: translateX(-3px);
            color: #2980b9;
        }
        
        .empty-message {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .empty-message i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        
        .search-input {
            border-radius: 30px;
            padding: 0.5rem 1.25rem;
            border: 1px solid #dee2e6;
        }
        
        .search-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
            border-color: var(--primary-color);
        }
        
        /* Formatting monetary values */
        .money-value {
            font-family: 'Courier New', monospace;
            text-align: right;
            font-weight: 500;
        }
        
        .money-value.positive {
            color: var(--success-color);
        }
        
        .money-value.negative {
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">IWK Finance Comparison Tool</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('excel.upload.form') }}">
                            <i class="fas fa-upload me-1"></i> Upload Files
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('excel.index') }}">
                            <i class="fas fa-list me-1"></i> File List
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <a href="{{ route('excel.index') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        
        <div class="comparison-header">
            <h1 class="comparison-title">
                <i class="fas fa-chart-bar me-2 text-primary"></i>{{ $comparison['comparison_name'] }}
            </h1>
            <p class="text-muted mb-4">Comparison between two financial reports</p>
            
            <div class="file-info">
                <div class="file-item">
                    <span class="file-badge file-badge-1">File 1</span>
                    <h5 class="mb-1">{{ $comparison['file1']['name'] }}</h5>
                    <p class="small text-muted mb-0">
                        <i class="far fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::parse($comparison['file1']['created_at'])->format('M d, Y - H:i') }}
                    </p>
                </div>
                <div class="file-item">
                    <span class="file-badge file-badge-2">File 2</span>
                    <h5 class="mb-1">{{ $comparison['file2']['name'] }}</h5>
                    <p class="small text-muted mb-0">
                        <i class="far fa-calendar-alt me-1"></i>
                        {{ \Carbon\Carbon::parse($comparison['file2']['created_at'])->format('M d, Y - H:i') }}
                    </p>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                <a href="{{ route('excel.export.original', $comparison['comparison_name']) }}" class="btn btn-warning me-2">
                    <i class="fas fa-file-excel me-2"></i> Export in Original Format
                </a>
                <a href="{{ route('excel.export', $comparison['comparison_name']) }}" class="btn btn-success">
                    <i class="fas fa-file-excel me-2"></i> Export Summary
                </a>
            </div>
        </div>
        
        <h3 class="section-title">Summary</h3>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card summary-card">
                    <div class="summary-icon text-primary">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div class="summary-number">{{ $comparison['summary']['total_cost_centers'] }}</div>
                    <div class="summary-label">Total Cost Centers</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card summary-card">
                    <div class="summary-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="summary-number">{{ $comparison['summary']['matched_cost_centers'] }}</div>
                    <div class="summary-label">Matched Cost Centers</div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card summary-card">
                    <div class="summary-icon text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="summary-number">{{ $comparison['summary']['with_differences'] }}</div>
                    <div class="summary-label">With Differences</div>
                </div>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-md-6 mb-3">
                <div class="card summary-card">
                    <div class="summary-icon" style="color: #3498db;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="summary-number">{{ $comparison['summary']['only_in_file1'] }}</div>
                    <div class="summary-label">Only in File 1</div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card summary-card">
                    <div class="summary-icon" style="color: #2ecc71;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="summary-number">{{ $comparison['summary']['only_in_file2'] }}</div>
                    <div class="summary-label">Only in File 2</div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
            <h3 class="section-title mb-0">Cost Centers Comparison</h3>
            <div>
                <input type="text" id="searchInput" class="form-control search-input" placeholder="Search cost centers...">
            </div>
        </div>
        
        @if(count($comparison['cost_centers']) > 0)
            @foreach($comparison['cost_centers'] as $costCenter)
                <div class="card cost-center-card cost-center-item">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-sitemap me-2 text-primary"></i>
                            Cost Center: <span class="cost-center-code">{{ $costCenter['code'] }}</span>
                        </h5>
                        <div>
                            @if($costCenter['exists_in_file1'])
                                <span class="badge-exists me-2">
                                    <i class="fas fa-check-circle me-1"></i>File 1
                                </span>
                            @else
                                <span class="badge-missing me-2">
                                    <i class="fas fa-times-circle me-1"></i>Missing in File 1
                                </span>
                            @endif
                            
                            @if($costCenter['exists_in_file2'])
                                <span class="badge-exists">
                                    <i class="fas fa-check-circle me-1"></i>File 2
                                </span>
                            @else
                                <span class="badge-missing">
                                    <i class="fas fa-times-circle me-1"></i>Missing in File 2
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @if($costCenter['exists_in_file1'] && $costCenter['exists_in_file2'])
                            @if(count($costCenter['main_descriptions']) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Differences</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($costCenter['main_descriptions'] as $mainDesc)
                                                <tr class="main-description-row {{ $mainDesc['has_differences'] ? 'has-differences' : '' }}">
                                                    <td>{{ $mainDesc['name'] }}</td>
                                                    <td>
                                                        @if($mainDesc['exists_in_file1'] && $mainDesc['exists_in_file2'])
                                                            @if($mainDesc['has_differences'])
                                                                <span class="badge bg-warning text-dark">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                                    Differences Found
                                                                </span>
                                                            @else
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-check-circle me-1"></i>
                                                                    Identical
                                                                </span>
                                                            @endif
                                                        @elseif($mainDesc['exists_in_file1'])
                                                            <span class="badge bg-primary">
                                                                <i class="fas fa-file me-1"></i>
                                                                Only in File 1
                                                            </span>
                                                        @elseif($mainDesc['exists_in_file2'])
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-file me-1"></i>
                                                                Only in File 2
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($mainDesc['has_differences'])
                                                            <span class="badge bg-danger">
                                                                {{ count($mainDesc['differences']) }} difference(s)
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($mainDesc['exists_in_file1'] && $mainDesc['exists_in_file2'])
                                                            <button class="toggle-details" data-bs-toggle="collapse" data-bs-target="#details-{{ $costCenter['code'] }}-{{ str_replace(' ', '-', $mainDesc['name']) }}">
                                                                <i class="fas fa-plus-circle me-1"></i>
                                                                View Details
                                                            </button>
                                                        @else
                                                            <span class="text-muted">Not Comparable</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                
                                                @if($mainDesc['exists_in_file1'] && $mainDesc['exists_in_file2'])
                                                    <tr class="detail-row">
                                                        <td colspan="4" class="p-0">
                                                            <div class="collapse" id="details-{{ $costCenter['code'] }}-{{ str_replace(' ', '-', $mainDesc['name']) }}">
                                                                <div class="p-3">
                                                                    @if($mainDesc['has_differences'])
                                                                        <h6 class="mb-3">Differences Found:</h6>
                                                                        
                                                                        @foreach($mainDesc['differences'] as $diff)
                                                                            <div class="difference-item {{ $diff['difference'] > 0 ? 'increased' : 'decreased' }}">
                                                                                <div class="row">
                                                                                    <div class="col-md-4">
                                                                                        <strong>{{ $diff['display_name'] }}</strong>
                                                                                    </div>
                                                                                    <div class="col-md-8">
                                                                                        <div class="row">
                                                                                            <div class="col-md-4">
                                                                                                <div class="small text-muted mb-1">File 1 Value:</div>
                                                                                                <div class="money-value">
                                                                                                    {{ number_format($diff['file1_value'], 2) }}
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <div class="small text-muted mb-1">File 2 Value:</div>
                                                                                                <div class="money-value">
                                                                                                    {{ number_format($diff['file2_value'], 2) }}
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <div class="small text-muted mb-1">Difference:</div>
                                                                                                <div class="money-value {{ $diff['difference'] > 0 ? 'positive' : 'negative' }}">
                                                                                                    {{ number_format($diff['difference'], 2) }}
                                                                                                    @if(isset($diff['percentage_change']))
                                                                                                        <span class="percentage-badge {{ $diff['difference'] > 0 ? 'percentage-positive' : 'percentage-negative' }}">
                                                                                                            {{ $diff['difference'] > 0 ? '+' : '' }}{{ number_format($diff['percentage_change'], 2) }}%
                                                                                                        </span>
                                                                                                    @endif
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                        
                                                                        @if(isset($mainDesc['description_types_differences']))
                                                                            <h6 class="mt-4 mb-3">Description Type Differences:</h6>
                                                                            
                                                                            @foreach($mainDesc['description_types_differences'] as $descType => $differences)
                                                                                <div class="desc-type-difference mb-3">
                                                                                    <h6 class="mb-2">{{ $descType }}</h6>
                                                                                    
                                                                                    @foreach($differences as $diff)
                                                                                        <div class="difference-item {{ $diff['difference'] > 0 ? 'increased' : 'decreased' }}">
                                                                                            <div class="row">
                                                                                                <div class="col-md-4">
                                                                                                    <strong>{{ $diff['display_name'] }}</strong>
                                                                                                </div>
                                                                                                <div class="col-md-8">
                                                                                                    <div class="row">
                                                                                                        <div class="col-md-4">
                                                                                                            <div class="small text-muted mb-1">File 1 Value:</div>
                                                                                                            <div class="money-value">
                                                                                                                {{ number_format($diff['file1_value'], 2) }}
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="col-md-4">
                                                                                                            <div class="small text-muted mb-1">File 2 Value:</div>
                                                                                                            <div class="money-value">
                                                                                                                {{ number_format($diff['file2_value'], 2) }}
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="col-md-4">
                                                                                                            <div class="small text-muted mb-1">Difference:</div>
                                                                                                            <div class="money-value {{ $diff['difference'] > 0 ? 'positive' : 'negative' }}">
                                                                                                                {{ number_format($diff['difference'], 2) }}
                                                                                                                @if(isset($diff['percentage_change']))
                                                                                                                    <span class="percentage-badge {{ $diff['difference'] > 0 ? 'percentage-positive' : 'percentage-negative' }}">
                                                                                                                        {{ $diff['difference'] > 0 ? '+' : '' }}{{ number_format($diff['percentage_change'], 2) }}%
                                                                                                                    </span>
                                                                                                                @endif
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            @endforeach
                                                                        @endif
                                                                    @else
                                                                        <div class="text-center text-muted">
                                                                            <i class="fas fa-check-circle me-1"></i>
                                                                            No differences found in the financial data.
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-message">
                                    <i class="fas fa-info-circle"></i>
                                    <p>No main descriptions found for this cost center.</p>
                                </div>
                            @endif
                        @else
                            <div class="empty-message">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>This cost center is not present in both files, so comparison is not available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="card">
                <div class="card-body empty-message">
                    <i class="fas fa-folder-open"></i>
                    <h5>No Cost Centers Found</h5>
                    <p>The compared files don't contain any cost centers to analyze.</p>
                </div>
            </div>
        @endif
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle toggles for details
            const toggleButtons = document.querySelectorAll('.toggle-details');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (icon.classList.contains('fa-plus-circle')) {
                        icon.classList.remove('fa-plus-circle');
                        icon.classList.add('fa-minus-circle');
                        this.closest('tr').classList.add('expanded');
                    } else {
                        icon.classList.remove('fa-minus-circle');
                        icon.classList.add('fa-plus-circle');
                        this.closest('tr').classList.remove('expanded');
                    }
                });
            });
            
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const costCenterItems = document.querySelectorAll('.cost-center-item');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                costCenterItems.forEach(item => {
                    const code = item.querySelector('.cost-center-code').textContent.toLowerCase();
                    
                    if (searchTerm === '' || code.includes(searchTerm)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html> 