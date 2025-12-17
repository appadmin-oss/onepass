/**
 * Vanguard OnePass™ - Data Compiler
 * Merges Import-MGT, Import-NGV, Import-NGG, Import-MAM into Central DB
 */

function compileCentralDB() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const targetSheet = ss.getSheetByName('Central DB') || ss.insertSheet('Central DB');
  
  // Clear old data but keep headers
  const lastRow = targetSheet.getLastRow();
  if (lastRow > 1) {
    targetSheet.getRange(2, 1, lastRow - 1, targetSheet.getLastColumn()).clearContent();
  } else {
    // Set Headers if empty
    targetSheet.getRange(1, 1, 1, 8).setValues([['Member ID', 'Full Name', 'Role', 'Status', 'Photo URL', 'Wallet Balance', 'Outstanding Fines', 'Reward Points']]);
  }
  
  const sources = ['Import-MGT', 'Import-NGV', 'Import-NGG', 'Import-MAM'];
  const allMembers = [];
  
  sources.forEach(sourceName => {
    const sheet = ss.getSheetByName(sourceName);
    if (!sheet) return;
    
    const data = sheet.getDataRange().getValues();
    const headers = data.shift(); // Remove header
    
    // Map columns based on source name (Logic to handle "Different headers" requirement)
    // This is a simplified mapper. In production, use dynamic column finding.
    const map = getColumnMap(sourceName, headers);
    
    data.forEach(row => {
      // Basic validation: Must have ID and Name
      if (!row[map.id] || !row[map.name]) return;
      
      const member = [
        row[map.id],                  // ID
        row[map.name],                // Name
        map.role ? row[map.role] : getDefaultRole(sourceName), // Role
        'Active',                     // Default Status
        map.photo ? row[map.photo] : '', // Photo
        0,                            // Wallet (Start at 0, updated by ledger)
        0,                            // Fines
        0                             // Points
      ];
      
      allMembers.push(member);
    });
  });
  
  // Write to Central DB
  if (allMembers.length > 0) {
    targetSheet.getRange(2, 1, allMembers.length, 8).setValues(allMembers);
  }
  
  Logger.log(`Compiled ${allMembers.length} members.`);
}

function getColumnMap(source, headers) {
  // Finds indices of required columns. 
  // Simplistic implementation: assumes standard names or specific positions if names vary too wildly.
  return {
    id: headers.indexOf('Member ID') > -1 ? headers.indexOf('Member ID') : 0,
    name: headers.indexOf('Full Name') > -1 ? headers.indexOf('Full Name') : 1,
    role: headers.indexOf('Role'), // Might be -1
    photo: headers.indexOf('Image') // NGG specific
  };
}

function getDefaultRole(source) {
  if (source.includes('MGT')) return 'Management';
  if (source.includes('NGV')) return 'Vanguard';
  return 'Member';
}
