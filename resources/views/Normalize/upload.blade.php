<!DOCTYPE html>
<html>
<head>
    <title>BRAIN File Normalization</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --success-color: #2ecc71;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            color: var(--dark-color);
            padding: 0;
            margin: 0;
        }
        
        .navbar {
            background-color: var(--dark-color);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .main-container {
            padding: 2rem;
            min-height: calc(100vh - 150px);
        }
        
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.5rem;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 1.8rem;
        }
        
        .upload-box { 
            border: 2px dashed #ccc; 
            padding: 2rem; 
            text-align: center;
            border-radius: 10px;
            margin-top: 1.5rem;
            transition: all 0.3s;
        }
        
        .upload-box:hover {
            border-color: var(--primary-color);
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .title-icon {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 6px;
            padding: 0.7rem 1.75rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            border: none;
            border-radius: 6px;
            padding: 0.7rem 1.75rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(127, 140, 141, 0.3);
        }
        
        .footer {
            text-align: center;
            padding: 1.5rem;
            background-color: var(--light-color);
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .cst-option-card {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.08) 0%, rgba(52, 152, 219, 0.03) 100%);
            border: 1px solid rgba(52, 152, 219, 0.15);
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .cst-option-card:hover {
            border-color: rgba(52, 152, 219, 0.3);
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.12) 0%, rgba(52, 152, 219, 0.05) 100%);
        }
        
        .cst-toggle-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }
        
        .cst-toggle-label {
            flex: 1;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 1rem;
            margin: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .cst-toggle-label i {
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .form-check-input[type="checkbox"] {
            width: 3.5rem;
            height: 2rem;
            cursor: pointer;
            border-radius: 2rem;
            background-color: #dee2e6;
            border: 2px solid #adb5bd;
            transition: all 0.3s ease;
        }
        
        .form-check-input[type="checkbox"]:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-input[type="checkbox"]:focus {
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .cst-info-text {
            font-size: 0.875rem;
            color: #6c757d;
            line-height: 1.5;
            margin: 0;
            padding-left: 0.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .cst-info-text i {
            color: var(--primary-color);
            margin-top: 0.2rem;
            font-size: 0.9rem;
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(44, 62, 80, 0.85);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            backdrop-filter: blur(4px);
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-card {
            background: white;
            border-radius: 16px;
            padding: 3rem 3.5rem;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 420px;
            width: 90%;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading-spinner {
            width: 64px;
            height: 64px;
            border: 5px solid #e0e0e0;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .loading-message {
            font-size: 0.95rem;
            color: #7f8c8d;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .loading-progress {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #95a5a6;
        }

        .loading-progress .dot {
            width: 6px;
            height: 6px;
            background: var(--primary-color);
            border-radius: 50%;
            animation: pulse 1.4s ease-in-out infinite;
        }

        .loading-progress .dot:nth-child(2) { animation-delay: 0.2s; }
        .loading-progress .dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes pulse {
            0%, 80%, 100% { opacity: 0.3; transform: scale(0.8); }
            40% { opacity: 1; transform: scale(1.2); }
        }

        /* Success state */
        .loading-card.success .loading-spinner {
            border: 5px solid var(--success-color);
            border-top: 5px solid var(--success-color);
            animation: none;
            position: relative;
        }

        .loading-card.success .loading-spinner::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.8rem;
            color: var(--success-color);
        }

        /* Error state */
        .loading-card.error .loading-spinner {
            border: 5px solid #e74c3c;
            border-top: 5px solid #e74c3c;
            animation: none;
            position: relative;
        }

        .loading-card.error .loading-spinner::after {
            content: '\f00d';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.8rem;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('excel.upload.form') }}">
                <i class="fas fa-chart-line me-2"></i>Report 15 Comparison Tools
            </a>
        </div>
    </nav>

    <div class="main-container container">
        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-cog title-icon"></i>Normalization</h2>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-excel me-2"></i>Upload File
            </div>
            <div class="card-body">
                <div class="upload-box">
                    <form action="{{ route('normalize.process') }}" method="POST" enctype="multipart/form-data" id="normalizeForm">
                        @csrf
                        <div class="cst-option-card">
                            <div class="cst-toggle-wrapper">
                                <input class="form-check-input" type="checkbox" 
                                       id="include_cst" name="include_cst" value="1">
                                <label class="cst-toggle-label" for="include_cst">
                                    <i class="fas fa-list-check"></i>
                                    <span>Include CST (4 Service Levels)</span>
                                </label>
                            </div>
                            <p class="cst-info-text">
                                <i class="fas fa-info-circle"></i>
                                <span>Check this to include CST as the 4th Service Level. Leave unchecked for standard 3 Service Levels (Connected, Nil, IST).</span>
                            </p>
                        </div>
                        <div class="mb-4">
                            <label for="br_file" class="form-label">
                                <i class="fas fa-file-excel me-1"></i>Select File
                            </label>
                            <input class="form-control" type="file" name="br_file" id="br_file" 
                                accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('excel.upload.form') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-home me-1"></i>
                            </a>
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                <i class="fas fa-cog me-1"></i> Process
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-card" id="loadingCard">
            <div class="loading-spinner" id="loadingSpinner"></div>
            <div class="loading-title" id="loadingTitle">Normalizing File</div>
            <div class="loading-message" id="loadingMessage">
                Please wait while your file is being processed...
            </div>
            <div class="loading-progress" id="loadingProgress">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span style="margin-left: 0.25rem;" id="loadingTimer">Elapsed: 0s</span>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>IWK Finance Comparison Tool &copy; {{ date('Y') }}</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let timerInterval = null;
            let elapsedSeconds = 0;

            function showLoading() {
                elapsedSeconds = 0;
                $('#loadingCard').removeClass('success error');
                $('#loadingTitle').text('Normalizing File');
                $('#loadingMessage').text('Please wait while your file is being processed...');
                $('#loadingProgress').show();
                $('#loadingTimer').text('Elapsed: 0s');
                $('#loadingOverlay').addClass('active');
                $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');

                timerInterval = setInterval(function() {
                    elapsedSeconds++;
                    $('#loadingTimer').text('Elapsed: ' + elapsedSeconds + 's');
                }, 1000);
            }

            function hideLoading(state, title, message) {
                clearInterval(timerInterval);

                if (state === 'success') {
                    $('#loadingCard').addClass('success');
                    $('#loadingTitle').text(title || 'Normalization Complete!');
                    $('#loadingMessage').text(message || 'Your file has been downloaded successfully.');
                } else if (state === 'error') {
                    $('#loadingCard').addClass('error');
                    $('#loadingTitle').text(title || 'Processing Failed');
                    $('#loadingMessage').text(message || 'An error occurred while processing the file.');
                }

                $('#loadingProgress').hide();

                // Auto-hide overlay after a delay
                setTimeout(function() {
                    $('#loadingOverlay').removeClass('active');
                    // Reset form and button
                    $('#normalizeForm')[0].reset();
                    $('#submitBtn').prop('disabled', false).html('<i class="fas fa-cog me-1"></i> Normalize');
                }, state === 'success' ? 2000 : 3000);
            }

            // Handle form submission via AJAX
            $('#normalizeForm').on('submit', function(e) {
                e.preventDefault();

                if (!this.checkValidity()) {
                    this.reportValidity();
                    return;
                }

                showLoading();

                var formData = new FormData(this);

                fetch('{{ route("normalize.process") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) {
                    if (!response.ok) {
                        // Try to parse error from JSON response
                        return response.json().then(function(data) {
                            throw new Error(data.error || 'Failed to process file.');
                        }).catch(function(parseErr) {
                            // If not JSON, throw generic error
                            if (parseErr.message && parseErr.message !== 'Failed to process file.') {
                                throw parseErr;
                            }
                            throw new Error('Failed to process file. Please check your file and try again.');
                        });
                    }

                    // Get filename from Content-Disposition header
                    var disposition = response.headers.get('Content-Disposition');
                    var filename = 'normalized_file.xlsx';
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        var filenameMatch = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        if (filenameMatch && filenameMatch[1]) {
                            filename = filenameMatch[1].replace(/['"]/g, '');
                        }
                    }

                    return response.blob().then(function(blob) {
                        return { blob: blob, filename: filename };
                    });
                })
                .then(function(result) {
                    // Create download link and trigger it
                    var url = window.URL.createObjectURL(result.blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = result.filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);

                    hideLoading('success');
                })
                .catch(function(error) {
                    console.error('Normalization error:', error);
                    hideLoading('error', 'Processing Failed', error.message);
                });
            });
        });
    </script>
</body>
</html>