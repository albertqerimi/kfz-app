<?php
// Get the PDF file path from the query parameter
$temp_pdf_path = $_GET['file'];

// Check if the file exists
if (!file_exists($temp_pdf_path)) {
    die("File not found.");
}

// Display PDF in the browser
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Invoice</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        #pdf-viewer {
            width: 100%;
            height: 90vh;
        }
    </style>
</head>
<body>
    <h1>Invoice Viewer</h1>
    <iframe id="pdf-viewer" src="data:application/pdf;base64,<?php echo base64_encode(file_get_contents($temp_pdf_path)); ?>"></iframe>
    <button onclick="window.print()">Print</button>
</body>
</html>
