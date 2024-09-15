
<?php
class CustomPDF extends TFPDF {
    // Declare a variable to store the alias for total pages
    private $totalPagesAlias = '{nb}';
    public function getAvailableSpace() {
        return $this->GetPageHeight() - $this->GetY() - $this->getBottomMargin();
    }
    public function getBottomMargin() {
        return $this->bMargin;
    }
    public function __construct($invoice, $qrCodePath = null)
    {
        parent::__construct();
        $this->invoice = $invoice;
        $this->qrCodePath = $qrCodePath; // Path to the QR code image, which may be null
    }

    public function setBottomMargin($margin) {
        $this->bMargin = $margin;
    }

    // Page header
    function Header() {
        // Your header code here
    }

    // Page footer
    function Footer() {
        // Ensure footer content is correctly placed
        $this->SetY(-25); // Increase position from the bottom to allow more space
        $this->SetFont('DejaVuSansMono', '', 8);
    
        // Define column widths and spacing
        $colWidth = 50; // Width of each column
        $lineHeight = 5; // Line height
        $spacing = 4; // Space between columns
        $startX = 10; // Starting X position
        $startY = $this->GetY(); // Starting Y position

        // Text content with a custom delimiter (|) to separate columns
        $text = "Mentor Kfz Handel & Service, Mannheimer Straße 8, 67105 Schifferstadt, Deutschland\nUSt-IdNr.: DE368172797|Handy:015788443090\nE-Mail: mentorkfz@web.de|Zahlungsempfänger: Mentor Sejdiu Bankname: Sparkasse Vorderpfalz\nIBAN: DE45 5455 0010 0194 2195 80 SWIFT/BIC: LUHSDE6AXXX";
    
        // Split the text into columns using the delimiter
        $columns = explode('|', $text);
    
        // Ensure we have four columns or adjust accordingly
        if (count($columns) < 3) {
            $columns = array_pad($columns, 3, ''); // Pad with empty strings if fewer columns
        }
        
        // Add an empty column for the QR code
        $columns[] = ''; // Placeholder for the fourth column (QR code)
    
        // Extract text for each column
        $col1Text = isset($columns[0]) ? $columns[0] : '';
        $col2Text = isset($columns[1]) ? $columns[1] : '';
        $col3Text = isset($columns[2]) ? $columns[2] : '';
        $col4Text = isset($columns[3]) ? $columns[3] : ''; // Placeholder for QR code column
    
        // Print the footer content for the last page
        if ($this->PageNo() != $this->totalPagesAlias) {
            return;
        }
    
        // Set Y position and add columns
        $this->SetY($startY); // Reset Y to initial position
        $this->SetXY($startX, $startY);
        $this->MultiCell($colWidth, $lineHeight, $col1Text, 0, 'L');
    
        $this->SetXY($startX + $colWidth + $spacing, $startY);
        $this->MultiCell($colWidth, $lineHeight, $col2Text, 0, 'L');
    
        $this->SetXY($startX + 2 * ($colWidth + $spacing), $startY);
        $this->MultiCell(($colWidth + 10), $lineHeight, $col3Text, 0, 'L');
    
        $this->SetXY($startX + 3 * ($colWidth + $spacing), $startY);
        $this->MultiCell($colWidth, $lineHeight, $col4Text, 0, 'L'); // Placeholder for QR code column
    
       if ($this->invoice['payment_form'] == 'Rechnung') {
            if (!empty($this->qrCodePath) && file_exists($this->qrCodePath)) {
                $xPosition = $startX + 3 * ($colWidth + $spacing) + 8 ;  // Adjust X position for the QR code
                $yPosition = $startY; // Align with the top of the text
                $imageWidth = 20;  // QR code image width
                $imageHeight = 20; // QR code image height

                // Insert the QR code image at the desired position
                $this->Image($this->qrCodePath, $xPosition, $yPosition, $imageWidth, $imageHeight);
            }
        }
    }

    // Override the AddPage method to include the $rotation parameter
    function AddPage($orientation = '', $size = '', $rotation = 0) {
        parent::AddPage($orientation, $size, $rotation);
        // Set alias for total pages
        $this->totalPagesAlias = $this->AliasNbPages();
    }

    function Close() {
        // Set the total pages alias for final replacement
        $this->totalPagesAlias = $this->PageNo();
        parent::Close();
    }
}
?>