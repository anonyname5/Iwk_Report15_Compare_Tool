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
            padding: 6px 15px;
            border-radius: 50px;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
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
        
        .delete-modal .modal-header {
            background-color: var(--danger-color);
            color: white;
            border-bottom: none;
        }
        
        .delete-modal .modal-header .btn-close {
            filter: invert(1);
        }
        
        .delete-modal .modal-body {
            padding: 2rem;
        }
        
        .delete-modal .modal-icon {
            font-size: 4rem;
            color: var(--danger-color);
            margin-bottom: 1rem;
        }
        
        .delete-modal .modal-title {
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .delete-modal .comparison-details {
            background-color: rgba(0, 0, 0, 0.03);
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }
        
        .delete-modal .comparison-details strong {
            color: var(--dark-color);
        }
        
        .delete-modal .warning-text {
            color: var(--danger-color);
            font-weight: 500;
            margin-top: 1rem;
        }
        
        .delete-modal .modal-footer {
            border-top: none;
            padding: 1.5rem 2rem;
        }
        
        .delete-modal .btn-cancel {
            background-color: #95a5a6;
            border-color: #95a5a6;
            color: white;
        }
        
        .delete-modal .btn-cancel:hover {
            background-color: #7f8c8d;
            border-color: #7f8c8d;
            color: white;
        }
        
        .delete-modal .btn-confirm-delete {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            color: white;
            font-weight: 600;
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

    <div class="container">
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
        
        <h2 class="section-title">File Comparisons</h2>
        
        @if($comparisonPairs->count() > 0)
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Comparison Name</th>
                                    <th>BRAIN File</th>
                                    <th>BS File</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($comparisonPairs as $pair)
                                    <tr>
                                        <td>{{ $pair['comparison_name'] }}</td>
                                        <td class="text-truncate" style="max-width: 250px;" title="{{ $pair['file1']->file_name }}">
                                            <span class="badge bg-primary text-white me-1">1</span>
                                            {{ $pair['file1']->file_name }}
                                        </td>
                                        <td class="text-truncate" style="max-width: 250px;" title="{{ $pair['file2']->file_name }}">
                                            <span class="badge bg-success text-white me-1">2</span>
                                            {{ $pair['file2']->file_name }}
                                        </td>
                                        <td>{{ $pair['created_at']->format('M d, Y') }}</td>
                                        <td>
                                            @if($pair['has_changes'])
                                                @if($pair['missing_cost_centers'])
                                                    <span class="status-badge status-badge-warning">Structure Changes</span>
                                                @else
                                                    <span class="status-badge status-badge-danger">Value Changes</span>
                                                @endif
                                            @else
                                                <span class="status-badge status-badge-success">No Changes</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group-actions">
                                                <a href="{{ route('excel.compare', $pair['comparison_name']) }}" class="btn btn-primary btn-download">
                                                    <i class="fas fa-download me-1"></i> Download
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="modal-icon">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <h5 class="modal-title mb-3">Are you sure you want to delete this comparison?</h5>
                    <p class="text-muted mb-3">This action will permanently delete the comparison and cannot be undone.</p>
                    
                    <div class="comparison-details text-start">
                        <div class="mb-2">
                            <strong>Comparison Name:</strong>
                            <span id="modalComparisonName" class="ms-2"></span>
                        </div>
                        <div class="mb-2">
                            <strong>Files to be deleted:</strong>
                            <ul class="mb-0 mt-2">
                                <li id="modalFile1" class="text-muted"></li>
                                <li id="modalFile2" class="text-muted"></li>
                            </ul>
                        </div>
                    </div>
                    
                    <p class="warning-text">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        This action cannot be undone!
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-confirm-delete" id="confirmDeleteBtn">
                        <i class="fas fa-trash-alt me-1"></i> Delete Comparison
                    </button>
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
            
            // Handle delete button clicks
            $('.btn-delete').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var $row = $button.closest('tr');
                var comparisonName = $button.data('comparison-name');
                
                // Get file names from the row
                var file1Name = $row.find('td:eq(1)').text().trim().replace(/^\d+\s*/, '');
                var file2Name = $row.find('td:eq(2)').text().trim().replace(/^\d+\s*/, '');
                
                // Store context
                deleteContext.button = $button;
                deleteContext.row = $row;
                deleteContext.comparisonName = comparisonName;
                deleteContext.originalHtml = $button.html();
                
                // Populate modal with data
                $('#modalComparisonName').text(comparisonName);
                $('#modalFile1').html('<i class="fas fa-file-excel text-primary me-1"></i>' + file1Name);
                $('#modalFile2').html('<i class="fas fa-file-excel text-success me-1"></i>' + file2Name);
                
                // Reset confirm button
                var $confirmBtn = $('#confirmDeleteBtn');
                $confirmBtn.prop('disabled', false);
                $confirmBtn.html('<i class="fas fa-trash-alt me-1"></i> Delete Comparison');
                
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
                $confirmBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');
                
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
                            $('.alert').remove();
                            $('.container').prepend(alertHtml);
                            
                            // Fade out and remove the row
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Check if table is empty
                                if ($('tbody tr').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            showError(response.message || 'Failed to delete comparison');
                            $confirmBtn.prop('disabled', false);
                            $confirmBtn.html('<i class="fas fa-trash-alt me-1"></i> Delete Comparison');
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
                        $confirmBtn.html('<i class="fas fa-trash-alt me-1"></i> Delete Comparison');
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
                $confirmBtn.html('<i class="fas fa-trash-alt me-1"></i> Delete Comparison');
                
                // Reset delete context
                if (deleteContext.button) {
                    deleteContext.button.prop('disabled', false);
                    deleteContext.button.html(deleteContext.originalHtml);
                }
            });
            
            function showError(message) {
                var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>' + message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
                
                $('.alert').remove();
                $('.container').prepend(alertHtml);
            }
        });
    </script>
</body>
</html> 