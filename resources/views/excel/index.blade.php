<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IWK Finance Comparison Tool</title>
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
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: rgba(52, 152, 219, 0.05);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
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
        
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 0;
        }
        
        .table th {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--dark-color);
            font-weight: 600;
            border-top: none;
            padding: 12px 15px;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .table td {
            vertical-align: middle;
            padding: 12px 15px;
        }
        
        .table tbody tr {
            transition: background-color 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .btn-icon {
            border-radius: 50%;
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.25rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
            border-color: #27ae60;
        }
        
        .file-badge {
            font-size: 0.85rem;
            padding: 0.25rem 0.75rem;
            border-radius: 30px;
        }
        
        .badge-file1 {
            background-color: rgba(52, 152, 219, 0.15);
            color: #2980b9;
        }
        
        .badge-file2 {
            background-color: rgba(46, 204, 113, 0.15);
            color: #27ae60;
        }
        
        .comparison-card {
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .comparison-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .comparison-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--primary-color);
        }
        
        .comparison-files {
            display: flex;
            gap: 1rem;
            margin-top: 0.75rem;
        }
        
        .file-item {
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            background-color: rgba(0,0,0,0.03);
            flex: 1;
            position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
            transition: background-color 0.2s;
        }
        
        .file-item:hover {
            background-color: rgba(0,0,0,0.05);
        }
        
        .file-item-badge {
            position: absolute;
            top: 0;
            right: 0;
            transform: translate(50%, -50%);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
        }
        
        .file-item-badge-1 {
            background-color: var(--primary-color);
        }
        
        .file-item-badge-2 {
            background-color: var(--success-color);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .date-badge {
            background-color: rgba(0,0,0,0.05);
            color: #555;
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        
        .compare-btn {
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .compare-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        
        .btn-download {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        
        .match-rate-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 120px;
        }
        
        .match-rate-bar {
            flex: 1;
            height: 6px;
            background-color: #eee;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .match-rate-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        
        .match-rate-text {
            font-weight: 600;
            font-size: 0.825rem;
            white-space: nowrap;
        }
        
        .status-badge-success {
            background-color: rgba(46, 204, 113, 0.15);
            color: #27ae60;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }
        
        .status-badge-danger {
            background-color: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .status-badge-warning {
            background-color: rgba(243, 156, 18, 0.15);
            color: #f39c12;
            border: 1px solid rgba(243, 156, 18, 0.3);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            border-color: #c0392b;
            color: white;
        }
        
        .btn-group-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .btn-delete {
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }
        
        .delete-modal .modal-content {
            border-radius: 10px;
        }
        
        .delete-modal .modal-header {
            border-bottom: 1px solid #eee;
            padding: 1rem 1.25rem;
        }
        
        .delete-modal .modal-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--dark-color);
        }
        
        .delete-modal .modal-body {
            padding: 1.25rem;
        }
        
        .delete-modal .modal-body p {
            margin-bottom: 0;
            color: #555;
            font-size: 0.925rem;
        }
        
        .delete-modal .modal-body .comparison-name {
            font-weight: 600;
            color: var(--dark-color);
            word-break: break-all;
            overflow-wrap: break-word;
        }
        
        .delete-modal .modal-footer {
            border-top: 1px solid #eee;
            padding: 0.75rem 1.25rem;
        }
        
        .delete-modal .btn-confirm-delete {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .delete-modal .btn-confirm-delete:hover {
            background-color: #c0392b;
            border-color: #c0392b;
            color: white;
        }
        
        .delete-modal .btn-confirm-delete:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        /* Pagination Styles */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(0,0,0,0.05);
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .pagination-info {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .pagination-controls .btn-page {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background-color: #fff;
            color: #495057;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .pagination-controls .btn-page:hover:not(.active):not(:disabled) {
            background-color: rgba(52, 152, 219, 0.1);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .pagination-controls .btn-page.active {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }
        
        .pagination-controls .btn-page:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-controls .btn-page.btn-nav {
            width: auto;
            padding: 0 12px;
            font-size: 0.8rem;
        }
        
        .pagination-controls .page-ellipsis {
            width: 36px;
            text-align: center;
            color: #6c757d;
            font-weight: 500;
            user-select: none;
        }
        
        .per-page-select {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .per-page-select select {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 0.875rem;
            color: #495057;
            background-color: #fff;
            cursor: pointer;
            outline: none;
            transition: border-color 0.2s;
        }
        
        .per-page-select select:focus {
            border-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('excel.upload.form') }}">IWK Finance Comparison Tool</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('excel.upload.form') }}">
                            <i class="fas fa-upload me-1"></i> Upload New Files
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" id="main-content">
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            </div>
        @endif
        
        <h2 class="section-title">Result List</h2>
        
        @if($comparisonPairs->count() > 0)
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Comparison Name</th>
                                    <th>File 1</th>
                                    <th>File 2</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Match Rate</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($comparisonPairs as $pair)
                                    <tr>
                                        <td>{{ $pair['comparison_name'] }}</td>
                                        <td class="text-truncate" style="max-width: 250px;" title="{{ $pair['file1']->file_name }}">
                                            <span class="badge bg-primary text-white me-1 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.7rem;">1</span>
                                            {{ $pair['file1']->file_name }}
                                        </td>
                                        <td class="text-truncate" style="max-width: 250px;" title="{{ $pair['file2']->file_name }}">
                                            <span class="badge bg-success text-white me-1 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.7rem;">2</span>
                                            {{ $pair['file2']->file_name }}
                                        </td>
                                        <td>{{ $pair['created_at']->format('M d, Y') }}</td>
                                        <td>
                                            @if($pair['has_changes'])
                                                <span class="status-badge status-badge-danger">Value Changes</span>
                                            @else
                                                <span class="status-badge status-badge-success">No Changes</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="match-rate-wrapper" title="{{ $pair['matching_fields'] }} of {{ $pair['total_fields'] }} fields match">
                                                <div class="match-rate-bar">
                                                    <div class="match-rate-fill" style="width: {{ $pair['match_percent'] }}%; background-color: {{ $pair['match_percent'] == 100 ? '#2ecc71' : ($pair['match_percent'] >= 90 ? '#f39c12' : '#e74c3c') }};"></div>
                                                </div>
                                                <span class="match-rate-text" style="color: {{ $pair['match_percent'] == 100 ? '#27ae60' : ($pair['match_percent'] >= 90 ? '#e67e22' : '#e74c3c') }};">{{ $pair['match_percent'] }}%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group-actions">
                                                <a href="{{ route('excel.compare', $pair['comparison_name']) }}" 
                                                   class="btn btn-primary btn-download"
                                                   data-bs-toggle="tooltip" 
                                                   title="Download comparison">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-danger btn-delete" 
                                                        data-comparison-name="{{ $pair['comparison_name'] }}"
                                                        data-bs-toggle="tooltip" 
                                                        title="Delete this comparison">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-wrapper" id="paginationWrapper">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="pagination-info" id="paginationInfo"></div>
                            <div class="per-page-select">
                                <label for="perPageSelect">Rows per page:</label>
                                <select id="perPageSelect">
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="all">All</option>
                                </select>
                            </div>
                        </div>
                        <div class="pagination-controls" id="paginationControls"></div>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-info-circle text-muted fa-3x mb-3"></i>
                    <h5>No Comparisons Available</h5>
                    <p class="text-muted mb-3">You haven't uploaded any comparison files yet.</p>
                    <a href="{{ route('excel.upload.form') }}" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Upload Files
                    </a>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade delete-modal" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Delete Comparison</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <span id="modalComparisonName" class="comparison-name"></span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-confirm-delete" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Store current delete context
            var deleteContext = {
                button: null,
                row: null,
                comparisonName: null,
                originalHtml: null
            };
            
            // Handle download button clicks - show loading spinner until download finishes
            $(document).on('click', '.btn-download', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var originalHtml = $button.html();
                var downloadUrl = $button.attr('href');
                
                // Show spinner
                $button.html('<i class="fas fa-spinner fa-spin"></i>');
                $button.addClass('disabled');
                
                // Fetch the file as a blob so we know when the download is complete
                fetch(downloadUrl)
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Download failed');
                        }
                        // Extract filename from Content-Disposition header
                        var disposition = response.headers.get('Content-Disposition');
                        var filename = 'comparison.xlsx';
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            var matches = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                            if (matches && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }
                        return response.blob().then(function(blob) {
                            return { blob: blob, filename: filename };
                        });
                    })
                    .then(function(result) {
                        // Create a temporary link to trigger the download
                        var url = window.URL.createObjectURL(result.blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = result.filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        a.remove();
                        
                        // Restore button
                        $button.html(originalHtml);
                        $button.removeClass('disabled');
                    })
                    .catch(function(error) {
                        // Restore button and show error
                        $button.html(originalHtml);
                        $button.removeClass('disabled');
                        
                        var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            '<i class="fas fa-exclamation-circle me-2"></i>Download failed. Please try again.' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>';
                        $('#main-content > .alert').remove();
                        $('#main-content').prepend(alertHtml);
                    });
            });
            
            // Handle delete button clicks (use event delegation for pagination compatibility)
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var $row = $button.closest('tr');
                var comparisonName = $button.data('comparison-name');
                
                // Store context
                deleteContext.button = $button;
                deleteContext.row = $row;
                deleteContext.comparisonName = comparisonName;
                deleteContext.originalHtml = $button.html();
                
                // Populate modal
                $('#modalComparisonName').text(comparisonName);
                
                // Reset confirm button
                var $confirmBtn = $('#confirmDeleteBtn');
                $confirmBtn.prop('disabled', false);
                $confirmBtn.html('Delete');
                
                // Show modal
                var deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                deleteModal.show();
            });
            
            // Handle confirm delete button click
            $('#confirmDeleteBtn').on('click', function() {
                var $confirmBtn = $(this);
                var $button = deleteContext.button;
                var $row = deleteContext.row;
                var comparisonName = deleteContext.comparisonName;
                
                // Disable confirm button and show loading state
                $confirmBtn.prop('disabled', true);
                $confirmBtn.html('<i class="fas fa-spinner fa-spin"></i>');
                
                // Also disable the original delete button
                if ($button) {
                    $button.prop('disabled', true);
                    $button.html('<i class="fas fa-spinner fa-spin"></i>');
                }
                
                // Send DELETE request
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: '/delete/' + encodeURIComponent(comparisonName),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            // Hide modal
                            var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                            deleteModal.hide();
                            
                            // Show success message
                            var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                '<i class="fas fa-check-circle me-2"></i>Comparison "' + comparisonName + '" deleted successfully.' +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>';
                            
                            // Remove existing alerts and add new one
                            $('#main-content > .alert').remove();
                            $('#main-content').prepend(alertHtml);
                            
                            // Fade out and remove the row
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Check if table is empty
                                if ($('tbody tr').length === 0) {
                                    location.reload();
                                } else {
                                    // Refresh pagination after row removal
                                    updatePagination();
                                }
                            });
                        } else {
                            showError(response.message || 'Failed to delete comparison');
                            $confirmBtn.prop('disabled', false);
                            $confirmBtn.html('Delete');
                            if ($button) {
                                $button.prop('disabled', false);
                                $button.html(deleteContext.originalHtml);
                            }
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'An error occurred while deleting the comparison.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMsg = 'Comparison not found.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server error. Please try again.';
                        }
                        
                        showError(errorMsg);
                        $confirmBtn.prop('disabled', false);
                        $confirmBtn.html('Delete');
                        if ($button) {
                            $button.prop('disabled', false);
                            $button.html(deleteContext.originalHtml);
                        }
                    }
                });
            });
            
            // Reset modal when closed
            $('#deleteConfirmModal').on('hidden.bs.modal', function () {
                var $confirmBtn = $('#confirmDeleteBtn');
                $confirmBtn.prop('disabled', false);
                $confirmBtn.html('Delete');
                
                // Reset delete context
                if (deleteContext.button) {
                    deleteContext.button.prop('disabled', false);
                    deleteContext.button.html(deleteContext.originalHtml);
                }
            });
            
            // ===== Pagination Logic =====
            var currentPage = 1;
            var rowsPerPage = 10;
            var $tableBody = $('table.table tbody');
            var $allRows = $tableBody.find('tr');
            var totalRows = $allRows.length;
            
            function getTotalPages() {
                if (rowsPerPage === 'all') return 1;
                return Math.ceil(totalRows / rowsPerPage);
            }
            
            function updatePagination() {
                // Recount rows (some may have been deleted)
                $allRows = $tableBody.find('tr');
                totalRows = $allRows.length;
                
                if (totalRows === 0) {
                    $('#paginationWrapper').hide();
                    return;
                }
                
                var totalPages = getTotalPages();
                
                // Ensure currentPage is valid
                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;
                
                // Show/hide rows
                $allRows.each(function(index) {
                    if (rowsPerPage === 'all') {
                        $(this).show();
                    } else {
                        var start = (currentPage - 1) * rowsPerPage;
                        var end = start + rowsPerPage;
                        if (index >= start && index < end) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    }
                });
                
                // Update info text
                if (rowsPerPage === 'all') {
                    $('#paginationInfo').text('Showing all ' + totalRows + ' entries');
                } else {
                    var start = (currentPage - 1) * rowsPerPage + 1;
                    var end = Math.min(currentPage * rowsPerPage, totalRows);
                    $('#paginationInfo').text('Showing ' + start + ' to ' + end + ' of ' + totalRows + ' entries');
                }
                
                // Build pagination controls
                var $controls = $('#paginationControls');
                $controls.empty();
                
                if (totalPages <= 1) {
                    return;
                }
                
                // Previous button
                var $prev = $('<button class="btn-page btn-nav" ' + (currentPage === 1 ? 'disabled' : '') + '><i class="fas fa-chevron-left"></i></button>');
                $prev.on('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        updatePagination();
                    }
                });
                $controls.append($prev);
                
                // Page numbers with ellipsis
                var pages = generatePageNumbers(currentPage, totalPages);
                pages.forEach(function(page) {
                    if (page === '...') {
                        $controls.append('<span class="page-ellipsis">...</span>');
                    } else {
                        var $btn = $('<button class="btn-page ' + (page === currentPage ? 'active' : '') + '">' + page + '</button>');
                        $btn.on('click', function() {
                            currentPage = page;
                            updatePagination();
                        });
                        $controls.append($btn);
                    }
                });
                
                // Next button
                var $next = $('<button class="btn-page btn-nav" ' + (currentPage === totalPages ? 'disabled' : '') + '><i class="fas fa-chevron-right"></i></button>');
                $next.on('click', function() {
                    if (currentPage < totalPages) {
                        currentPage++;
                        updatePagination();
                    }
                });
                $controls.append($next);
            }
            
            function generatePageNumbers(current, total) {
                var pages = [];
                if (total <= 7) {
                    for (var i = 1; i <= total; i++) pages.push(i);
                    return pages;
                }
                
                // Always show first page
                pages.push(1);
                
                if (current > 3) {
                    pages.push('...');
                }
                
                // Pages around current
                var startPage = Math.max(2, current - 1);
                var endPage = Math.min(total - 1, current + 1);
                
                for (var i = startPage; i <= endPage; i++) {
                    pages.push(i);
                }
                
                if (current < total - 2) {
                    pages.push('...');
                }
                
                // Always show last page
                pages.push(total);
                
                return pages;
            }
            
            // Per-page selector change
            $('#perPageSelect').on('change', function() {
                var val = $(this).val();
                rowsPerPage = val === 'all' ? 'all' : parseInt(val);
                currentPage = 1;
                updatePagination();
            });
            
            // Initialize pagination
            if (totalRows > 0) {
                updatePagination();
            }
            
            function showError(message) {
                var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>' + message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                
                $('#main-content > .alert').remove();
                $('#main-content').prepend(alertHtml);
            }
        });
    </script>
</body>
</html> 