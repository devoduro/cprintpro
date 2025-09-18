<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print: {{ $document->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .document-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e5e5;
        }
        .document-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .document-info {
            color: #666;
            font-size: 14px;
        }
        .document-viewer {
            text-align: center;
            margin: 30px 0;
        }
        .document-frame {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .print-actions {
            text-align: center;
            margin: 30px 0;
            gap: 15px;
            display: flex;
            justify-content: center;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2563eb;
        }
        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        .btn-success:hover {
            background-color: #059669;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: none;
            }
            .print-actions {
                display: none;
            }
            .document-frame {
                height: auto;
                min-height: 80vh;
            }
        }
        .error-message {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="print-container">
      

        <div class="print-actions">
          
            
            <a href="{{ route('documents.show', $document) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Document
            </a>
        </div>

        <div class="document-viewer">
            @php
                $fileExtension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                $isPdf = $fileExtension === 'pdf';
                $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                $isText = in_array($fileExtension, ['txt', 'md']);
            @endphp

            @if($isPdf)
                <iframe src="{{ $document->file_url }}" class="document-frame" type="application/pdf">
                    <div class="error-message">
                        <p>Your browser doesn't support PDF viewing.</p>
                        <a href="{{ $document->file_url }}" target="_blank" class="btn btn-primary">
                            Open PDF in New Tab
                        </a>
                    </div>
                </iframe>
            @elseif($isImage)
                <img src="{{ $document->file_url }}" alt="{{ $document->title }}" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px;">
            @elseif($isText)
                <iframe src="{{ $document->file_url }}" class="document-frame">
                    <div class="error-message">
                        <p>Unable to display text file.</p>
                        <a href="{{ $document->file_url }}" target="_blank" class="btn btn-primary">
                            Open File in New Tab
                        </a>
                    </div>
                </iframe>
            @else
                <div class="error-message">
                    <p><strong>Preview not available for this file type.</strong></p>
                    <p>File Type: {{ strtoupper($fileExtension) }}</p>
                    <a href="{{ $document->file_url }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download"></i>
                        Download and Open
                    </a>
                </div>
            @endif
        </div>

        
    </div>

    <script>
        // Auto-focus for better user experience
        window.addEventListener('load', function() {
            // Optional: Auto-print when page loads (uncomment if desired)
            // setTimeout(() => window.print(), 1000);
        });

        // Handle print button click
        function printDocument() {
            window.print();
        }

        // Handle keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
