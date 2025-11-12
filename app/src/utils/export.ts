/**
 * Export Utilities
 * Functions for exporting data to various formats
 */

/**
 * Export data to CSV format
 */
export function exportToCSV(data: any[], filename: string = 'export') {
  if (!data || data.length === 0) {
    console.warn('No data to export');
    return;
  }

  // Get headers from first object
  const headers = Object.keys(data[0]);
  
  // Create CSV content
  const csvContent = [
    // Headers row
    headers.join(','),
    // Data rows
    ...data.map(row => {
      return headers.map(header => {
        const value = row[header];
        
        // Handle different data types
        if (value === null || value === undefined) {
          return '';
        }
        
        // Handle dates
        if (value instanceof Date) {
          return value.toISOString();
        }
        
        // Handle objects/arrays (stringify them)
        if (typeof value === 'object') {
          return JSON.stringify(value).replace(/"/g, '""');
        }
        
        // Handle strings with commas or quotes
        const stringValue = String(value);
        if (stringValue.includes(',') || stringValue.includes('"') || stringValue.includes('\n')) {
          return `"${stringValue.replace(/"/g, '""')}"`;
        }
        
        return stringValue;
      }).join(',');
    })
  ].join('\n');

  // Create blob and download
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  
  link.setAttribute('href', url);
  link.setAttribute('download', `${filename}_${new Date().getTime()}.csv`);
  link.style.visibility = 'hidden';
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

/**
 * Export data to Excel format (simplified - creates CSV that Excel can open)
 */
export function exportToExcel(data: any[], filename: string = 'export') {
  // For now, use CSV format that Excel can open
  // In production, you'd use a library like SheetJS
  exportToCSV(data, filename);
}

/**
 * Export data to JSON format
 */
export function exportToJSON(data: any[], filename: string = 'export') {
  if (!data || data.length === 0) {
    console.warn('No data to export');
    return;
  }

  const jsonContent = JSON.stringify(data, null, 2);
  
  const blob = new Blob([jsonContent], { type: 'application/json' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  
  link.setAttribute('href', url);
  link.setAttribute('download', `${filename}_${new Date().getTime()}.json`);
  link.style.visibility = 'hidden';
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

/**
 * Export data to PDF format (basic table)
 */
export async function exportToPDF(
  data: any[], 
  filename: string = 'export',
  title: string = 'Data Export'
) {
  // This would require a library like jsPDF
  // For now, we'll create a printable HTML version
  
  if (!data || data.length === 0) {
    console.warn('No data to export');
    return;
  }

  const headers = Object.keys(data[0]);
  
  const htmlContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>${title}</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          padding: 20px;
        }
        h1 {
          color: #333;
          border-bottom: 2px solid #333;
          padding-bottom: 10px;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin-top: 20px;
        }
        th, td {
          border: 1px solid #ddd;
          padding: 8px;
          text-align: left;
        }
        th {
          background-color: #f2f2f2;
          font-weight: bold;
        }
        tr:nth-child(even) {
          background-color: #f9f9f9;
        }
        .footer {
          margin-top: 20px;
          padding-top: 10px;
          border-top: 1px solid #ddd;
          font-size: 12px;
          color: #666;
        }
      </style>
    </head>
    <body>
      <h1>${title}</h1>
      <p>Generated on: ${new Date().toLocaleString()}</p>
      <p>Total Records: ${data.length}</p>
      
      <table>
        <thead>
          <tr>
            ${headers.map(h => `<th>${h}</th>`).join('')}
          </tr>
        </thead>
        <tbody>
          ${data.map(row => `
            <tr>
              ${headers.map(h => `<td>${formatCellValue(row[h])}</td>`).join('')}
            </tr>
          `).join('')}
        </tbody>
      </table>
      
      <div class="footer">
        <p>© ${new Date().getFullYear()} - Data Export</p>
      </div>
    </body>
    </html>
  `;

  const printWindow = window.open('', '_blank');
  if (printWindow) {
    printWindow.document.write(htmlContent);
    printWindow.document.close();
    printWindow.print();
  }
}

/**
 * Format cell value for display
 */
function formatCellValue(value: any): string {
  if (value === null || value === undefined) {
    return '';
  }
  
  if (value instanceof Date) {
    return value.toLocaleDateString();
  }
  
  if (typeof value === 'boolean') {
    return value ? 'Yes' : 'No';
  }
  
  if (typeof value === 'object') {
    return JSON.stringify(value);
  }
  
  return String(value);
}

/**
 * Export table to clipboard
 */
export function copyToClipboard(data: any[]) {
  if (!data || data.length === 0) {
    console.warn('No data to copy');
    return;
  }

  const headers = Object.keys(data[0]);
  
  // Create tab-separated content (works well with Excel/Sheets)
  const content = [
    headers.join('\t'),
    ...data.map(row => 
      headers.map(h => formatCellValue(row[h])).join('\t')
    )
  ].join('\n');

  // Copy to clipboard
  if (navigator.clipboard) {
    navigator.clipboard.writeText(content).then(() => {
      console.log('Data copied to clipboard');
    }).catch(err => {
      console.error('Failed to copy:', err);
      fallbackCopyToClipboard(content);
    });
  } else {
    fallbackCopyToClipboard(content);
  }
}

/**
 * Fallback clipboard copy for older browsers
 */
function fallbackCopyToClipboard(text: string) {
  const textArea = document.createElement('textarea');
  textArea.value = text;
  textArea.style.position = 'fixed';
  textArea.style.top = '0';
  textArea.style.left = '0';
  textArea.style.width = '2em';
  textArea.style.height = '2em';
  textArea.style.padding = '0';
  textArea.style.border = 'none';
  textArea.style.outline = 'none';
  textArea.style.boxShadow = 'none';
  textArea.style.background = 'transparent';
  
  document.body.appendChild(textArea);
  textArea.focus();
  textArea.select();
  
  try {
    document.execCommand('copy');
    console.log('Data copied to clipboard');
  } catch (err) {
    console.error('Failed to copy:', err);
  }
  
  document.body.removeChild(textArea);
}

/**
 * Download file helper
 */
export function downloadFile(content: string, filename: string, mimeType: string) {
  const blob = new Blob([content], { type: mimeType });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  
  link.setAttribute('href', url);
  link.setAttribute('download', filename);
  link.style.visibility = 'hidden';
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  
  // Clean up
  URL.revokeObjectURL(url);
}

/**
 * Export configuration interface
 */
export interface ExportConfig {
  format: 'csv' | 'excel' | 'json' | 'pdf';
  filename?: string;
  columns?: string[];
  includeHeaders?: boolean;
  dateFormat?: string;
}

/**
 * Universal export function
 */
export function exportData(data: any[], config: ExportConfig) {
  const { format, filename = 'export', columns, includeHeaders = true } = config;
  
  // Filter columns if specified
  let exportData = data;
  if (columns && columns.length > 0) {
    exportData = data.map(row => {
      const filteredRow: any = {};
      columns.forEach(col => {
        if (col in row) {
          filteredRow[col] = row[col];
        }
      });
      return filteredRow;
    });
  }
  
  // Export based on format
  switch (format) {
    case 'csv':
      exportToCSV(exportData, filename);
      break;
    case 'excel':
      exportToExcel(exportData, filename);
      break;
    case 'json':
      exportToJSON(exportData, filename);
      break;
    case 'pdf':
      exportToPDF(exportData, filename);
      break;
    default:
      console.error('Unsupported export format:', format);
  }
}

export default {
  exportToCSV,
  exportToExcel,
  exportToJSON,
  exportToPDF,
  copyToClipboard,
  downloadFile,
  exportData
};
