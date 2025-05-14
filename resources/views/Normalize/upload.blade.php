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
                        <div class="mb-4">
                            <input class="form-control" type="file" name="br_file" id="br_file" 
                                accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('excel.upload.form') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-home me-1"></i> Back to Home
                            </a>
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                <i class="fas fa-cog me-1"></i> Normalize File
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