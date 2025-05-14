<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NormalizationController extends Controller
{
    const MAIN_DESCRIPTIONS = [
        'Commercial Totals',
        'Domestic Totals',
        'Non-billable Totals',
        'Govt.Domestic Totals',
        'Govt. Premises Totals',
        'Govt. Quarters Totals',
        'Industrial Totals',
        'Ind. No HC Totals',
    ];

    const REQUIRED_SUBS = ['Connected', 'Nil', 'IST'];
    const TOTAL_COLUMNS = 35;
    const FINANCE_COL_START = 2; // Columns 0-1 are description/cost center

    public function showUploadForm()
    {
        return view('normalize.upload');
    }

    public function processFile(Request $request)
    {
        Log::info('Starting file normalization process');
        
        $request->validate([
            'br_file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $file = $request->file('br_file');
            $originalFileName = $file->getClientOriginalName();
            $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);
            
            Log::info('Received file: ' . $originalFileName . ' (' . $file->getSize() . ' bytes)');
            
            $normalizedPath = $this->normalizeBrFile($file, $fileNameWithoutExtension);
            Log::info('Normalization complete. Output file: ' . $normalizedPath);
            
            if (!Storage::disk('public')->exists(basename($normalizedPath))) {
                Log::error('Generated file not found at path: ' . $normalizedPath);
                throw new \Exception("Generated file not found");
            }

            // Store success message in session
            session()->flash('success', 'File normalized successfully! Your download should begin automatically.');
            
            // Set download filename to original filename + normalized
            $downloadFilename = $fileNameWithoutExtension . '_normalized.xlsx';
            
            // Add a header that will refresh the page after download
            return response()->download(
                $normalizedPath,
                $downloadFilename,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Refresh' => '1;url=' . route('normalize.process')
                ]
            )->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Processing failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Failed to process file: ' . $e->getMessage()]);
        }
    }

    private function normalizeBrFile($file, $originalFileName = null)
    {
        Log::info('Loading Excel file into array');
        $rows = Excel::toArray(null, $file)[0];
        Log::info('Loaded ' . count($rows) . ' rows from Excel file');
        
        $headerRows = array_slice($rows, 0, 6);
        $dataRows = array_slice($rows, 6);
        Log::info('Extracted ' . count($headerRows) . ' header rows and ' . count($dataRows) . ' data rows');

        // Group by Cost Center
        $costCenters = [];
        foreach ($dataRows as $row) {
            if (isset($row[1]) && !empty($row[1])) {
                $costCenters[$row[1]][] = $row;
            }
        }
        Log::info('Identified ' . count($costCenters) . ' unique cost centers');

        $normalizedData = [];

        foreach ($costCenters as $cc => $ccRows) {
            Log::info('Processing cost center: ' . $cc . ' with ' . count($ccRows) . ' rows');
            $ccData = [];
            $mainTotalRows = []; // Store all main description totals for this cost center
            
            foreach (self::MAIN_DESCRIPTIONS as $mainDesc) {
                $fullMainDesc = "$cc $mainDesc"; // Combine cost center with description
                Log::info('  Processing main description: ' . $mainDesc);
                $blockData = $this->processMainDescriptionBlock($ccRows, $fullMainDesc, $mainDesc, $cc);
                Log::info('  Generated ' . count($blockData) . ' rows for ' . $mainDesc);
                
                // Store the main total row (last row in block data)
                if (!empty($blockData)) {
                    $mainTotalRows[] = $blockData[count($blockData) - 1];
                }
                
                $ccData = array_merge($ccData, $blockData);
            }

            // Add Cost Center Total
            $ccTotal = $this->findCostCenterTotal($ccRows);
            if ($ccTotal) {
                Log::info('  Found existing Cost Center Total for ' . $cc);
            } else {
                Log::info('  Creating new Cost Center Total from main description totals');
                $ccTotal = $this->createCcTotalRowFromMainTotals($cc, $mainTotalRows);
            }
            $ccData[] = $ccTotal;
            
            // Add a blank row after each cost center total for better readability
            $blankRow = array_fill(0, self::TOTAL_COLUMNS, '');
            $ccData[] = $blankRow;
            Log::info('  Added blank row after cost center total');

            $normalizedData = array_merge($normalizedData, $ccData);
            Log::info('  Completed processing for cost center: ' . $cc);
        }
        
        // Now process the overall totals section (rows without cost centers)
        Log::info('Processing overall totals section (rows without cost centers)');
        $overallTotalsData = $this->processOverallTotals($dataRows);
        
        // Merge with normalized data
        if (!empty($overallTotalsData)) {
            $normalizedData = array_merge($normalizedData, $overallTotalsData);
            Log::info('Added ' . count($overallTotalsData) . ' overall total rows');
        }

        Log::info('Total normalized data rows: ' . count($normalizedData));
        return $this->exportNormalizedFile($headerRows, $normalizedData, $originalFileName);
    }

    private function processMainDescriptionBlock($ccRows, $fullMainDesc, $baseMainDesc, $cc)
    {
        $mainTotalRow = null;
        $subRows = [];
        $mainRowIndex = -1;
        
        // Log inputs for debugging
        Log::info('    Looking for main desc: "' . $fullMainDesc . '" or "' . $baseMainDesc . '"');
        
        // Format the main description with CC prefix
        $formattedMainDesc = "CC" . $fullMainDesc;
        Log::info('    Formatted main description: "' . $formattedMainDesc . '"');
        
        // First, let's identify all main description rows in this cost center
        $allMainDescRows = [];
        foreach ($ccRows as $index => $row) {
            if (empty($row[0])) continue;
            
            $rowDesc = trim($row[0]);
            // Check if this is likely a main description row (contains "Totals")
            if (stripos($rowDesc, 'Totals') !== false) {
                $allMainDescRows[$index] = $rowDesc;
            }
        }
        
        Log::info('    Found ' . count($allMainDescRows) . ' potential main description rows in this cost center');
        
        // Find existing main total row (check all possible formats)
        foreach ($ccRows as $index => $row) {
            if (empty($row[0])) continue;
            
            // Normalize by trimming and removing any trailing colon
            $rowDesc = rtrim(trim($row[0]), ':');
            $normalizedFullDesc = rtrim(trim($fullMainDesc), ':');
            $normalizedBaseDesc = rtrim(trim($baseMainDesc), ':');
            
            // Also check variant with "CC" prefix
            $ccPrefixDesc = "CC" . $normalizedFullDesc;
            
            Log::info('    Comparing row: "' . $rowDesc . '" with normalized forms "' . $normalizedFullDesc . '", "' . $normalizedBaseDesc . '", and "' . $ccPrefixDesc . '"');
            
            if ($rowDesc === $normalizedFullDesc || 
                $rowDesc === $normalizedBaseDesc || 
                $rowDesc === $ccPrefixDesc) {
                $mainTotalRow = $row;
                // Standardize the main description format with CC prefix
                $mainTotalRow[0] = $formattedMainDesc;
                $mainRowIndex = $index;
                
                Log::info('    Found main total row for "' . $row[0] . '" at index ' . $index);
                break;
            }
        }

        if (!$mainTotalRow) {
            Log::info('    Main total row not found with exact matching - trying flexible matching');
            
            // Try again with more flexible matching
            foreach ($ccRows as $index => $row) {
                if (empty($row[0])) continue;
                
                // More flexible comparison - case insensitive and ignoring prefixes/suffixes
                $rowText = strtolower(trim($row[0]));
                $searchText1 = strtolower(trim($fullMainDesc));
                $searchText2 = strtolower(trim($baseMainDesc));
                $ccCode = strtolower($cc);
                
                // Check multiple patterns:
                // 1. Exact start match
                // 2. With CC prefix
                // 3. Just matching the description part and cost center
                if (strpos($rowText, $searchText1) === 0 || 
                    strpos($rowText, $searchText2) === 0 ||
                    strpos($rowText, "cc" . $searchText1) === 0 ||
                    strpos($rowText, "cc" . $ccCode) === 0 && strpos($rowText, strtolower($baseMainDesc)) !== false) {
                    
                    $mainTotalRow = $row;
                    // Standardize the main description format with CC prefix
                    $mainTotalRow[0] = $formattedMainDesc;
                    $mainRowIndex = $index;
                    
                    Log::info('    Found main total row with flexible matching: "' . $row[0] . '" at index ' . $index);
                    break;
                }
            }
        }

        if (!$mainTotalRow) {
            Log::info('    Main total row not found for "' . $fullMainDesc . '" after all matching attempts');
        } else {
            // We found the main row, now find the safe boundaries for sub-row search
            // by finding the nearest previous main description row
            
            $previousMainIndex = -1;
            $safeStartIndex = 0;
            
            // Find the nearest previous main description row to establish a boundary
            foreach ($allMainDescRows as $index => $desc) {
                if ($index < $mainRowIndex && $index > $previousMainIndex) {
                    $previousMainIndex = $index;
                }
            }
            
            // If we found a previous main description, set safe start to 
            // just after it to avoid taking its sub-rows
            if ($previousMainIndex >= 0) {
                $safeStartIndex = $previousMainIndex + 1;
                Log::info('    Found previous main description at index ' . $previousMainIndex . 
                          ', setting safe search start to ' . $safeStartIndex);
            }
            
            // Now search for sub-rows in the safe zone
            $subRows = $this->findSubRowsInSafeZone($ccRows, $mainRowIndex, $safeStartIndex, $cc);
        }

        // Process sub-rows with financial data preservation
        Log::info('    Processing ' . count($subRows) . ' sub-rows');
        $processedSubs = $this->processSubRows($subRows, $cc, $fullMainDesc);
        Log::info('    Generated ' . count($processedSubs) . ' processed sub-rows');

        // Create main total if missing (with aggregated financial data)
        if (!$mainTotalRow) {
            Log::info('    Creating new main total row for "' . $fullMainDesc . '"');
            $mainTotalRow = $this->createMainTotalRow($formattedMainDesc, $cc, $processedSubs);
        }

        return array_merge($processedSubs, [$mainTotalRow]);
    }

    private function processSubRows($subRows, $cc, $mainDesc)
    {
        $processed = [];
        $foundSubs = [];

        // First pass: normalize existing rows
        foreach ($subRows as $row) {
            // Remove 'Service Level:' and any trailing colons
            $cleanDesc = rtrim(str_replace('Service Level:', '', $row[0]), ':');
            $cleanDesc = trim($cleanDesc);
            
            if (in_array($cleanDesc, self::REQUIRED_SUBS)) {
                $row[0] = $cleanDesc;
                $foundSubs[] = $cleanDesc;
                $processed[] = $row;
                Log::info('      Found existing sub-row: ' . $cleanDesc);
            } else {
                // Try more flexible matching for subs
                foreach (self::REQUIRED_SUBS as $requiredSub) {
                    if (stripos($cleanDesc, $requiredSub) === 0) {
                        $row[0] = $requiredSub; // Standardize name
                        $foundSubs[] = $requiredSub;
                        $processed[] = $row;
                        Log::info('      Found existing sub-row with flexible matching: ' . $cleanDesc . ' -> ' . $requiredSub);
                        break;
                    }
                }
                
                if (!in_array($cleanDesc, $foundSubs)) {
                    Log::info('      Skipping non-matching sub-row: ' . $cleanDesc);
                }
            }
        }

        // Second pass: add missing sub-rows
        foreach (self::REQUIRED_SUBS as $sub) {
            if (!in_array($sub, $foundSubs)) {
                $newRow = array_fill(0, self::TOTAL_COLUMNS, 0);
                $newRow[0] = $sub;
                $newRow[1] = $cc;
                
                Log::info('      Creating missing sub-row: ' . $sub);
                
                // Try to find matching data from original rows
                $dataFound = false;
                foreach ($subRows as $originalRow) {
                    $originalDesc = rtrim(str_replace('Service Level:', '', $originalRow[0]), ':');
                    $originalDesc = trim($originalDesc);
                    
                    // More flexible comparison
                    if (stripos($originalDesc, $sub) === 0) {
                        // Copy all financial columns
                        for ($i = self::FINANCE_COL_START; $i < self::TOTAL_COLUMNS; $i++) {
                            $newRow[$i] = $originalRow[$i] ?? 0;
                        }
                        Log::info('      Copied financial data from original row for: ' . $sub);
                        $dataFound = true;
                        break;
                    }
                }
                
                if (!$dataFound) {
                    Log::info('      No financial data found for sub-row: ' . $sub . ' - using zeros');
                }
                
                $processed[] = $newRow;
            }
        }

        // Sort and ensure exactly 3 rows
        usort($processed, fn($a, $b) => 
            array_search($a[0], self::REQUIRED_SUBS) <=> array_search($b[0], self::REQUIRED_SUBS)
        );
        
        $result = array_slice($processed, 0, 3);
        Log::info('      Returning ' . count($result) . ' processed sub-rows');
        
        return $result;
    }

    private function createMainTotalRow($desc, $cc, $subRows = [])
    {
        $row = array_fill(0, self::TOTAL_COLUMNS, 0);
        $row[0] = $desc; // Already contains CC prefix
        $row[1] = $cc;

        // Sum financial columns from sub-rows
        foreach ($subRows as $subRow) {
            for ($i = self::FINANCE_COL_START; $i < self::TOTAL_COLUMNS; $i++) {
                $row[$i] += $subRow[$i] ?? 0;
            }
        }
        
        if (count($subRows) > 0) {
            Log::info('    Created main total row by summing ' . count($subRows) . ' sub-rows');
        } else {
            Log::warning('    Created empty main total row - no sub-rows to sum');
        }

        return $row;
    }

    private function findCostCenterTotal($ccRows)
    {
        foreach (array_reverse($ccRows) as $row) {
            if (strpos($row[0], 'Cost Center Total') !== false) {
                Log::info('    Found Cost Center Total row: ' . $row[0]);
                return $row;
            }
        }
        return null;
    }

    private function createCcTotalRow($cc)
    {
        $row = array_fill(0, self::TOTAL_COLUMNS, 0);
        // Format as "Cost Center [code] Totals"
        $row[0] = "Cost Center $cc Totals";
        $row[1] = $cc;
        Log::info('    Created new Cost Center Total row: ' . $row[0]);
        return $row;
    }

    private function createCcTotalRowFromMainTotals($cc, $mainTotalRows)
    {
        $row = array_fill(0, self::TOTAL_COLUMNS, 0);
        // Format as "Cost Center [code] Totals"
        $row[0] = "Cost Center $cc Totals";
        $row[1] = $cc;
        
        // Sum financial columns from all main description totals
        foreach ($mainTotalRows as $mainTotalRow) {
            for ($i = self::FINANCE_COL_START; $i < self::TOTAL_COLUMNS; $i++) {
                $row[$i] += ($mainTotalRow[$i] ?? 0);
            }
        }
        
        Log::info('    Created new Cost Center Total row by summing ' . count($mainTotalRows) . ' main description totals');
        
        // Log some values for debugging
        Log::info('    Cost Center Total - Billing: ' . ($row[2] ?? 0) . 
                 ', Receipts: ' . ($row[3] ?? 0) . 
                 ', Outstanding: ' . ($row[6] ?? 0));
        
        return $row;
    }

    private function exportNormalizedFile($headers, $data, $originalFileName = null)
    {
        // Create a filename using the original filename if provided, otherwise use timestamp
        if ($originalFileName) {
            $filename = $originalFileName . '_normalized_' . now()->format('Ymd_His') . '.xlsx';
        } else {
            $filename = 'normalized_br_' . now()->format('Ymd_His') . '.xlsx';
        }
        
        Log::info('Exporting ' . (count($headers) + count($data)) . ' total rows to file: ' . $filename);

        try {
            // Create a custom exporter with formatting support
            $exporter = new class(array_merge($headers, $data)) implements FromArray {
                public function __construct(private array $data) {}
                public function array(): array { return $this->data; }
            };

            // Use store() to save it to disk with standard options
            Excel::store(
                $exporter,
                $filename,
                'public',
                \Maatwebsite\Excel\Excel::XLSX
            );
            
            // Apply formatting to the Excel file after it's created
            $this->applyFormattingToExcel(Storage::disk('public')->path($filename));
            
            $fullPath = Storage::disk('public')->path($filename);
            Log::info('Excel file successfully exported to: ' . $fullPath);
            
            if (Storage::disk('public')->exists($filename)) {
                Log::info('Verified file exists in storage');
            } else {
                Log::error('File verification failed - file does not exist in storage');
            }
            
            return $fullPath;
        } catch (\Exception $e) {
            Log::error('Excel export failed: ' . $e->getMessage());
            Log::error('Excel export stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Apply formatting to the Excel file to highlight totals
     * 
     * @param string $filePath Path to the Excel file
     */
    private function applyFormattingToExcel($filePath)
    {
        try {
            Log::info('Applying formatting to Excel file: ' . $filePath);
            
            // Load the Excel file for formatting
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            
            // Get the highest row number
            $highestRow = $sheet->getHighestRow();
            $columnLetter = $sheet->getHighestColumn();
            
            // Highlight header rows 1-3 with light yellow background (#FFFFCC)
            $headerStyle1 = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'] // Black text
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'FFFFCC'] // Light yellow background
                ]
            ];
            
            // Apply to rows 1-3
            $sheet->getStyle('A1:' . $columnLetter . '3')->applyFromArray($headerStyle1);
            Log::info('Applied light yellow highlighting to header rows 1-3');
            
            // Highlight header rows 5-6 with lavender background (#9999FF)
            $headerStyle2 = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'] // Black text
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '9999FF'] // Lavender background
                ]
            ];
            
            // Apply to rows 5-6
            $sheet->getStyle('A5:' . $columnLetter . '6')->applyFromArray($headerStyle2);
            Log::info('Applied lavender highlighting to header rows 5-6');
            
            // Define style with light cyan/turquoise background (matching the example)
            $totalStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'] // Black text
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '00FFFF'] // Cyan/turquoise background
                ]
            ];
            
            // Iterate through rows to apply formatting
            $rowCount = 0;
            
            for ($row = 7; $row <= $highestRow; $row++) { // Start after header rows
                $cellValue = $sheet->getCell('A' . $row)->getValue();
                
                if (empty($cellValue)) {
                    continue; // Skip empty rows
                }
                
                // Check if this is a main description total row or cost center total row or Age Balance % row
                if ((strpos($cellValue, 'Totals') !== false) || 
                    (strpos($cellValue, 'Cost Center') !== false) ||
                    (strpos($cellValue, 'Cost Centre') !== false) ||
                    (strpos($cellValue, 'Age Balance') !== false)) {
                    $sheet->getStyle('A' . $row . ':' . $columnLetter . $row)->applyFromArray($totalStyle);
                    $rowCount++;
                    Log::info('Applied highlighting to row ' . $row . ': ' . $cellValue);
                }
            }
            
            // Save the formatted file
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($filePath);
            
            Log::info('Applied formatting to ' . $rowCount . ' rows and header rows');
            
        } catch (\Exception $e) {
            Log::error('Failed to apply Excel formatting: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            // Continue without formatting rather than failing the whole process
        }
    }

    /**
     * Find valid sub-rows within a safe boundary zone
     * 
     * @param array $rows Array of data rows
     * @param int $mainIndex Index of the main description row
     * @param int $safeStartIndex Safe starting index to avoid other main descriptions
     * @param string $cc Cost center code
     * @return array Array of sub-rows
     */
    private function findSubRowsInSafeZone($rows, $mainIndex, $safeStartIndex, $cc) 
    {
        $subRows = [];
        $searchStartIndex = max($safeStartIndex, $mainIndex - 10);  // Don't go further than 10 rows back
        
        Log::info('        Looking for sub-rows between index ' . $searchStartIndex . ' and ' . ($mainIndex - 1));
        
        // First, identify all potential sub-rows in the safe zone
        $potentialSubRows = [];
        for ($i = $mainIndex - 1; $i >= $searchStartIndex; $i--) {
            if (!isset($rows[$i]) || empty($rows[$i][0])) continue;
            
            $rowDesc = trim($rows[$i][0]);
            $cleanDesc = rtrim(str_replace('Service Level:', '', $rowDesc), ':');
            $cleanDesc = trim($cleanDesc);
            
            // Check if this is a potential sub-row
            $isValidSubRow = false;
            $matchedType = null;
            
            // Check for direct matches
            if (in_array($cleanDesc, self::REQUIRED_SUBS)) {
                $isValidSubRow = true;
                $matchedType = $cleanDesc;
            } else {
                // Check for partial matches
                foreach (self::REQUIRED_SUBS as $type) {
                    if (stripos($cleanDesc, $type) === 0) {
                        $isValidSubRow = true;
                        $matchedType = $type;
                        break;
                    }
                }
            }
            
            // Check for "Service Level:" prefix
            if (!$isValidSubRow && stripos($rowDesc, 'Service Level:') === 0) {
                $isValidSubRow = true;
                // Try to determine type from Service Level: prefix
                foreach (self::REQUIRED_SUBS as $type) {
                    if (stripos($cleanDesc, $type) !== false) {
                        $matchedType = $type;
                        break;
                    }
                }
            }
            
            // Skip rows that look like main descriptions
            if (!$isValidSubRow && (
                stripos($rowDesc, 'Totals') !== false || 
                stripos($rowDesc, 'CC' . $cc) === 0 ||
                stripos($rowDesc, $cc) === 0
            )) {
                Log::info('        Skipping main description in sub-row search: ' . $rowDesc);
                continue;
            }
            
            if ($isValidSubRow) {
                Log::info('        Found potential sub-row: ' . $rowDesc . 
                         ($matchedType ? ' (type: ' . $matchedType . ')' : ''));
                $potentialSubRows[] = [
                    'index' => $i,
                    'row' => $rows[$i],
                    'type' => $matchedType,
                    'desc' => $cleanDesc,
                    'distance' => $mainIndex - $i  // How far from main description
                ];
            }
        }
        
        // Now select the best sub-rows based on proximity and type
        $selectedTypes = [];
        
        // First pass: Sort by proximity to main description
        usort($potentialSubRows, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        
        // Select one row per required type, favoring those closer to the main description
        foreach (self::REQUIRED_SUBS as $requiredType) {
            // Find the closest row of this type
            $found = false;
            foreach ($potentialSubRows as $subRow) {
                if ($subRow['type'] === $requiredType && !in_array($requiredType, $selectedTypes)) {
                    $subRows[] = $subRow['row'];
                    $selectedTypes[] = $requiredType;
                    $found = true;
                    Log::info('        Selected sub-row: ' . $subRow['desc'] . ' as ' . $requiredType);
                    break;
                }
            }
            
            if (!$found) {
                Log::info('        No matching sub-row found for type: ' . $requiredType);
            }
        }
        
        Log::info('        Found ' . count($subRows) . ' valid sub-rows in safe zone');
        return $subRows;
    }

    /**
     * Process the overall totals section (rows without cost centers)
     * 
     * @param array $dataRows All data rows
     * @return array Processed overall total rows
     */
    private function processOverallTotals($dataRows)
    {
        $overallTotalsData = [];
        $processedMainDescs = [];
        
        Log::info('Starting to process overall totals section');
        
        // First, process all main descriptions that don't have a cost center
        foreach (self::MAIN_DESCRIPTIONS as $mainDesc) {
            $mainDescRows = $this->findOverallMainDescRows($dataRows, $mainDesc);
            
            if (!empty($mainDescRows)) {
                Log::info('  Found overall main description: ' . $mainDesc);
                $processedMainDescs[] = $mainDesc;
                
                // Process connected, nil, IST rows
                $subRows = $this->findOverallSubRows($dataRows, $mainDescRows['index']);
                Log::info('  Found ' . count($subRows) . ' sub-rows for overall ' . $mainDesc);
                
                // If we're missing any required sub-rows, create them
                $foundSubTypes = array_map(function($row) {
                    $desc = trim($row[0]);
                    foreach (self::REQUIRED_SUBS as $subType) {
                        if (stripos($desc, $subType) === 0 || $desc === $subType) {
                            return $subType;
                        }
                    }
                    return null;
                }, $subRows);
                
                // Filter out null values and create missing sub-rows
                $foundSubTypes = array_filter($foundSubTypes);
                
                foreach (self::REQUIRED_SUBS as $requiredSub) {
                    if (!in_array($requiredSub, $foundSubTypes)) {
                        Log::info('    Creating missing sub-row: ' . $requiredSub . ' for ' . $mainDesc);
                        // Create empty row (all zeros instead of blanks)
                        $newRow = array_fill(0, self::TOTAL_COLUMNS, 0);
                        $newRow[0] = $requiredSub;
                        
                        // Important: Set all financial columns to 0 explicitly to avoid using past values
                        for ($i = self::FINANCE_COL_START; $i < self::TOTAL_COLUMNS; $i++) {
                            $newRow[$i] = 0;
                        }
                        
                        $subRows[] = $newRow;
                    }
                }
                
                // Re-sort sub-rows to ensure proper order
                usort($subRows, function($a, $b) {
                    $indexA = $this->getSubRowIndex(trim($a[0]));
                    $indexB = $this->getSubRowIndex(trim($b[0]));
                    return $indexA - $indexB;
                });
                
                // Add sub-rows first
                $overallTotalsData = array_merge($overallTotalsData, $subRows);
                
                // Then add the main description row
                $overallTotalsData[] = $mainDescRows['row'];
                
                Log::info('  Added overall main description ' . $mainDesc . ' with its sub-rows');
            }
        }
        
        // Find and add Cost Center Report Totals using both exact and flexible matching
        $reportTotalRow = null;
        
        // First try exact match
        $reportTotalRow = $this->findOverallTotalRow($dataRows, 'Cost Center Report Totals');
        
        // If not found, try with "Centre" spelling variant
        if (!$reportTotalRow) {
            $reportTotalRow = $this->findOverallTotalRow($dataRows, 'Cost Centre Report Totals');
        }
        
        // If still not found, try with just "Cost Center Totals"
        if (!$reportTotalRow) {
            $reportTotalRow = $this->findOverallTotalRow($dataRows, 'Cost Center Totals');
        }
        
        // If still not found, try more flexible matching
        if (!$reportTotalRow) {
            foreach ($dataRows as $row) {
                // Skip rows with cost centers
                if (!empty($row[1])) continue;
                
                $rowDesc = trim($row[0]);
                
                // Check for various patterns that might indicate a cost center report total
                if (!empty($rowDesc) && 
                    ((stripos($rowDesc, 'Cost Center') !== false && stripos($rowDesc, 'Report') !== false) ||
                     (stripos($rowDesc, 'Cost Centre') !== false && stripos($rowDesc, 'Report') !== false) ||
                     (stripos($rowDesc, 'Cost Center') !== false && stripos($rowDesc, 'Total') !== false) ||
                     (stripos($rowDesc, 'Cost Centre') !== false && stripos($rowDesc, 'Total') !== false) ||
                     $rowDesc === 'CC Report Totals' ||
                     stripos($rowDesc, 'Overall Total') !== false)) {
                    
                    // Create a clean version of the row with colon removed
                    $reportTotalRow = $row;
                    $reportTotalRow[0] = rtrim(trim($row[0]), ':');
                    Log::info('  Added Cost Center Report Totals row (flexible match): ' . $reportTotalRow[0]);
                    break;
                }
            }
        }
        
        // Add the report total row if found
        if ($reportTotalRow) {
            $overallTotalsData[] = $reportTotalRow;
            Log::info('  Added Cost Center Report Totals row');
        } else {
            // If we still haven't found it, create a placeholder row
            Log::info('  Creating placeholder Cost Center Report Totals row');
            $placeholderRow = array_fill(0, self::TOTAL_COLUMNS, '');
            $placeholderRow[0] = 'Cost Centre Report Totals';
            $overallTotalsData[] = $placeholderRow;
        }
        
        // Add a blank row after Cost Centre Report Totals
        $blankRow = array_fill(0, self::TOTAL_COLUMNS, '');
        $overallTotalsData[] = $blankRow;
        Log::info('  Added blank row after Cost Centre Report Totals');
        
        // Find and add Age Balance %
        $ageBalanceRow = $this->findOverallTotalRow($dataRows, 'Age Balance %');
        if (!$ageBalanceRow) {
            // Try more flexible matching for Age Balance %
            foreach ($dataRows as $row) {
                // Skip rows with cost centers
                if (!empty($row[1])) continue;
                
                if (!empty($row[0]) && 
                    (stripos(trim($row[0]), 'Age') !== false && 
                     stripos(trim($row[0]), 'Balance') !== false)) {
                    // Create a cleaned version of the row with colon removed
                    $ageBalanceRow = $row;
                    $ageBalanceRow[0] = rtrim(trim($row[0]), ':');
                    Log::info('  Added Age Balance % row (flexible match): ' . $ageBalanceRow[0]);
                    break;
                }
            }
        }
        
        if ($ageBalanceRow) {
            $overallTotalsData[] = $ageBalanceRow;
            Log::info('  Added Age Balance % row');
        } else {
            // If not found, create a placeholder
            $placeholderRow = array_fill(0, self::TOTAL_COLUMNS, '');
            $placeholderRow[0] = 'Age Balance %';
            $overallTotalsData[] = $placeholderRow;
            Log::info('  Created placeholder Age Balance % row');
        }
        
        return $overallTotalsData;
    }
    
    /**
     * Find a main description row without a cost center
     * 
     * @param array $dataRows All data rows
     * @param string $mainDesc Main description to look for
     * @return array|null Found row data with index
     */
    private function findOverallMainDescRows($dataRows, $mainDesc)
    {
        foreach ($dataRows as $index => $row) {
            // Skip if column A is empty or column B is not empty (has a cost center)
            if (empty($row[0]) || !empty($row[1])) {
                continue;
            }
            
            $rowDesc = trim($row[0]);
            
            // Check if this is the main description we're looking for
            if (stripos($rowDesc, $mainDesc) !== false) {
                Log::info('    Found overall main description row: "' . $rowDesc . '" at index ' . $index);
                
                // Create a copy of the row and remove any colons from the description
                $cleanedRow = $row;
                $cleanedRow[0] = rtrim(trim($cleanedRow[0]), ':');
                
                return [
                    'row' => $cleanedRow,
                    'index' => $index
                ];
            }
        }
        
        Log::info('    No overall row found for main description: ' . $mainDesc);
        return null;
    }
    
    /**
     * Find sub-rows (Connected, Nil, IST) for an overall main description
     * 
     * @param array $dataRows All data rows
     * @param int $mainDescIndex Index of the main description row
     * @return array Found sub-rows
     */
    private function findOverallSubRows($dataRows, $mainDescIndex)
    {
        $subRows = [];
        $searchRange = 10; // Look up to 10 rows before the main description
        
        Log::info('    Looking for sub-rows up to ' . $searchRange . ' rows before main description');
        
        // Find the nearest previous main description to establish a safe boundary
        $safeStartIndex = max(0, $mainDescIndex - $searchRange);
        
        // Improve the safety by finding the nearest previous main description
        for ($i = $mainDescIndex - 1; $i >= 0; $i--) {
            if (isset($dataRows[$i]) && !empty($dataRows[$i][0])) {
                $desc = trim($dataRows[$i][0]);
                // Check if this is another main description or overall total row
                if (stripos($desc, 'Totals') !== false && 
                    (stripos($desc, 'Commercial') !== false || 
                     stripos($desc, 'Domestic') !== false || 
                     stripos($desc, 'Govt') !== false || 
                     stripos($desc, 'Industrial') !== false ||
                     stripos($desc, 'Non-billable') !== false ||
                     stripos($desc, 'Cost Center') !== false ||
                     stripos($desc, 'Cost Centre') !== false ||
                     stripos($desc, 'Report Totals') !== false)) {
                    
                    // Found a boundary, set safe start to just after this row
                    $safeStartIndex = $i + 1;
                    Log::info('    Found previous main description "' . $desc . '" at index ' . $i . 
                             ', setting safe start to ' . $safeStartIndex);
                    break;
                }
            }
        }
        
        // Search backwards from the main description row within the safe zone
        for ($i = $mainDescIndex - 1; $i >= $safeStartIndex; $i--) {
            if (!isset($dataRows[$i]) || empty($dataRows[$i][0])) {
                continue;
            }
            
            $rowDesc = trim($dataRows[$i][0]);
            
            // Handle "Service Level:" prefix
            $cleanDesc = $rowDesc;
            if (stripos($cleanDesc, 'Service Level:') === 0) {
                $cleanDesc = trim(substr($cleanDesc, strlen('Service Level:')));
                Log::info('    Found row with Service Level prefix: "' . $rowDesc . '" -> "' . $cleanDesc . '"');
            }
            $cleanDesc = rtrim($cleanDesc, ':'); // Remove any trailing colon
            $cleanDesc = trim($cleanDesc);
            
            // Skip rows that have a cost center in column B
            if (!empty($dataRows[$i][1])) {
                Log::info('    Skipping row with cost center: "' . $rowDesc . '"');
                continue;
            }
            
            // Check if this is one of our sub-types (Connected, Nil, IST)
            $isMatch = false;
            foreach (self::REQUIRED_SUBS as $subType) {
                // Check exact match
                if ($cleanDesc === $subType) {
                    // Create a standardized row with the clean description
                    $standardizedRow = $dataRows[$i];
                    $standardizedRow[0] = $subType; // Use standardized name
                    $subRows[] = $standardizedRow;
                    Log::info('    Found exact match sub-row: "' . $rowDesc . '" -> "' . $subType . '" at index ' . $i);
                    $isMatch = true;
                    break;
                }
                
                // Check starts with match
                if (stripos($cleanDesc, $subType) === 0) {
                    // Create a standardized row with the clean description
                    $standardizedRow = $dataRows[$i];
                    $standardizedRow[0] = $subType; // Use standardized name
                    $subRows[] = $standardizedRow;
                    Log::info('    Found partial match sub-row: "' . $rowDesc . '" -> "' . $subType . '" at index ' . $i);
                    $isMatch = true;
                    break;
                }
            }
            
            // If not matched but contains one of the required sub types, try to match
            if (!$isMatch) {
                foreach (self::REQUIRED_SUBS as $subType) {
                    if (stripos($cleanDesc, $subType) !== false) {
                        // Create a standardized row with the clean description
                        $standardizedRow = $dataRows[$i];
                        $standardizedRow[0] = $subType; // Use standardized name
                        $subRows[] = $standardizedRow;
                        Log::info('    Found contains match sub-row: "' . $rowDesc . '" -> "' . $subType . '" at index ' . $i);
                        $isMatch = true;
                        break;
                    }
                }
            }
        }
        
        // Sort sub-rows in the correct order (Connected, Nil, IST)
        usort($subRows, function($a, $b) {
            $indexA = array_search(trim($a[0]), self::REQUIRED_SUBS);
            $indexB = array_search(trim($b[0]), self::REQUIRED_SUBS);
            
            // With our standardization above, this should always find the index
            if ($indexA === false || $indexB === false) {
                // Fallback to string comparison if somehow not found
                return strcmp(trim($a[0]), trim($b[0]));
            }
            
            return $indexA - $indexB;
        });
        
        // Remove duplicates (keep the first occurrence of each sub type)
        $uniqueSubRows = [];
        $foundTypes = [];
        
        foreach ($subRows as $row) {
            $type = trim($row[0]);
            if (!in_array($type, $foundTypes)) {
                $uniqueSubRows[] = $row;
                $foundTypes[] = $type;
            }
        }
        
        Log::info('    Found ' . count($uniqueSubRows) . ' unique sub-rows from ' . count($subRows) . ' total matches');
        return $uniqueSubRows;
    }
    
    /**
     * Find a specific overall total row by name
     * 
     * @param array $dataRows All data rows
     * @param string $rowName Name to look for (e.g., "Cost Center Report Totals")
     * @return array|null Found row
     */
    private function findOverallTotalRow($dataRows, $rowName)
    {
        foreach ($dataRows as $row) {
            // Check column A for the row name
            if (!empty($row[0])) {
                $rowDesc = rtrim(trim($row[0]), ':');
                
                if ($rowDesc === $rowName) {
                    Log::info('    Found overall row: "' . $rowName . '"');
                    
                    // Create a copy of the row and ensure description has no colon
                    $cleanedRow = $row;
                    $cleanedRow[0] = $rowDesc;
                    
                    return $cleanedRow;
                }
            }
        }
        
        Log::info('    No row found for: ' . $rowName);
        return null;
    }

    /**
     * Helper function to get the index of a sub-row type
     * 
     * @param string $rowDesc The description of the row
     * @return int The index in REQUIRED_SUBS or a high number if not found
     */
    private function getSubRowIndex($rowDesc)
    {
        // Direct match
        $index = array_search($rowDesc, self::REQUIRED_SUBS);
        if ($index !== false) {
            return $index;
        }
        
        // Starts with match
        foreach (self::REQUIRED_SUBS as $idx => $type) {
            if (stripos($rowDesc, $type) === 0) {
                return $idx;
            }
        }
        
        // Contains match
        foreach (self::REQUIRED_SUBS as $idx => $type) {
            if (stripos($rowDesc, $type) !== false) {
                return $idx;
            }
        }
        
        // Not found, return a high index
        return 999;
    }
}