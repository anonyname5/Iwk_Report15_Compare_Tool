<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .main-container {
            padding: 2rem;
            min-height: calc(100vh - 70px);
        }
        
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
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
        
        .form-control, .form-select {
            border-radius: 6px;
            padding: 0.7rem 1rem;
            font-size: 1rem;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: none;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
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
        
        .alert {
            border-radius: 6px;
            border: none;
            padding: 1rem 1.5rem;
        }
        
        .footer {
            text-align: center;
            padding: 1.5rem;
            background-color: var(--light-color);
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
            margin-bottom: 1.5rem;
            border: 2px dashed rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
        }
        
        .file-upload:hover {
            border-color: var(--primary-color);
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .file-upload input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: pointer;
            display: block;
        }
        
        .file-upload-icon {
            font-size: 3rem;
            color: #bdc3c7;
            margin-bottom: 1rem;
        }
        
        .file-name {
            margin-top: 1rem;
            font-weight: 500;
            color: var(--primary-color);
            word-break: break-all;
        }
        
        .comparison-section {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2rem 0;
            gap: 1.5rem;
        }
        
        .comparison-divider {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #95a5a6;
        }
        
        .comparison-divider-line {
            width: 2px;
            height: 50px;
            background-color: #ecf0f1;
            margin: 0.5rem 0;
        }
        
        .vs-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ecf0f1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #7f8c8d;
        }
        
        /* Loading spinner animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .spinner {
            border: 4px solid rgba(0,0,0,0.1);
            border-radius: 50%;
            border-top: 4px solid var(--primary-color);
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
        }
        
        /* Card titles */
        .title-icon {
            margin-right: 10px;
            color: var(--primary-color);
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
                <h2><i class="fas fa-file-upload title-icon"></i>Upload Excel Files</h2>
                <p class="text-muted"></p>
            </div>
        </div>
        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <div class="row">
            <div class="col-lg-12">
                <div class="card" id="uploadFormCard">
                    <div class="card-header">
                        <i class="fas fa-upload me-2"></i>Upload Files
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" action="{{ route('excel.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="comparison_name" class="form-label">Comparison Name</label>
                                <input type="text" class="form-control" id="comparison_name" name="comparison_name" 
                                    placeholder="Enter a name for this comparison" required>
                                <div class="form-text"></div>
                            </div>
                            
                            <div class="row comparison-section">
                                <div class="col-md-5">
                                    <div class="file-upload" id="file-upload-1">
                                        <div class="file-upload-icon">
                                            <i class="fas fa-file-excel"></i>
                                        </div>
                                        <h5>Upload BRAIN File</h5>
                                        <p class="text-muted">Drag & drop or click to select</p>
                                        <input type="file" class="upload" id="file_1" name="file_1" accept=".xlsx, .xls" required>
                                        <div class="file-name" id="file-name-1"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-2 d-flex justify-content-center align-items-center">
                                    <div class="comparison-divider">
                                        <div class="comparison-divider-line"></div>
                                        <div class="vs-circle">VS</div>
                                        <div class="comparison-divider-line"></div>
                                    </div>
                                </div>
                                
                                <div class="col-md-5">
                                    <div class="file-upload" id="file-upload-2">
                                        <div class="file-upload-icon">
                                            <i class="fas fa-file-excel"></i>
                                        </div>
                                        <h5>Upload BS File</h5>
                                        <p class="text-muted">Drag & drop or click to select</p>
                                        <input type="file" class="upload" id="file_2" name="file_2" accept=".xlsx, .xls" required>
                                        <div class="file-name" id="file-name-2"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('normalize.process') }}" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-cog me-1"></i> Normalize BRAIN Files
                                </a>
                                <a href="{{ route('excel.index') }}" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-list me-1"></i> View Results
                                </a>
                                <button type="submit" id="submitBtn" class="btn btn-primary">
                                    <i class="fas fa-upload me-1"></i> Upload & Process
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
            // File input change event for first file
            $('#file_1').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $('#file-name-1').text(fileName);
                    $('#file-upload-1').css('border-color', '#2ecc71');
                } else {
                    $('#file-name-1').text('');
                    $('#file-upload-1').css('border-color', 'rgba(0,0,0,0.1)');
                }
            });
            
            // File input change event for second file
            $('#file_2').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $('#file-name-2').text(fileName);
                    $('#file-upload-2').css('border-color', '#2ecc71');
                } else {
                    $('#file-name-2').text('');
                    $('#file-upload-2').css('border-color', 'rgba(0,0,0,0.1)');
                }
            });
            
            // Form submission
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#submitBtn').prop('disabled', true)
                            .html('<span class="spinner"></span> Processing...');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Add success message
                            $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                '<i class="fas fa-check-circle me-2"></i>Files processed successfully! Redirecting...' +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>').insertBefore('#uploadFormCard');
                                
                            // Redirect after a short delay
                            setTimeout(function() {
                                window.location.href = response.redirect || "{{ route('excel.index') }}";
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'An error occurred while processing the files.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        
                        $('.alert-danger').remove();
                        $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' + 
                            '<i class="fas fa-exclamation-circle me-2"></i>' + errorMsg + 
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                            '</div>').insertBefore('#uploadFormCard');
                        
                        $('#submitBtn').prop('disabled', false)
                            .html('<i class="fas fa-upload me-1"></i> Upload & Process');
                    }
                });
            });
        });
    </script>
</body>
</html>
