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
                <h2><i class="fas fa-cog title-icon"></i>BRAIN File Normalization</h2>
                <p class="text-muted">Normalize your BRAIN file format for standard reporting</p>
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
                <i class="fas fa-file-excel me-2"></i>Upload BRAIN File
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
                                <i class="fas fa-file-excel me-1"></i>Select BRAIN File
                            </label>
                            <input class="form-control" type="file" name="br_file" id="br_file" 
                                accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('excel.upload.form') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                <i class="fas fa-cog me-1"></i> Normalize
                            </button>
                        </div>
                    </form>
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
            // Handle form submission
            $('#normalizeForm').on('submit', function() {
                if (this.checkValidity()) {
                    // Show processing message
                    $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
                    
                    // Set a timeout to reset the form after the file download has likely completed
                    setTimeout(function() {
                        // Reset form state
                        $('#normalizeForm')[0].reset();
                        $('#submitBtn').prop('disabled', false).html('<i class="fas fa-cog me-1"></i> Normalize File');
                    }, 5000); // 5 seconds should be enough time for most downloads to start
                }
            });
        });
    </script>
</body>
</html>