/**
 * Vanguard OnePass™ - Backend Logic v4.3 (Production)
 * 
 * ==========================================
 * DEPLOYMENT INSTRUCTIONS
 * ==========================================
 * 1. Open your Google Sheet.
 * 2. Go to Extensions > Apps Script.
 * 3. Paste this entire file into 'Code.gs'.
 * 4. Click 'Deploy' > 'New deployment'.
 * 5. Select type: 'Web app'.
 * 6. Description: 'v1'.
 * 7. Execute as: 'Me' (your account).
 * 8. Who has access: 'Anyone' (Required for the React App to access it without OAuth popup).
 * 9. Copy the 'Web App URL' and paste it into the Admin Dashboard > Integration tab.
 * ==========================================
 */

const CONFIG = {
  SHEET_DB: 'Central DB',
  SHEET_LOGS: 'Access Logs',
  SHEET_LEDGER: 'Transactions',
  ORG_ID: 'ORG_CACENTRE_001',
  SOURCES: ['Import-MGT', 'Import-NGV', 'Import-NGG', 'Import-MAM']
};

function doGet(e) {
  const lock = LockService.getScriptLock();
  lock.tryLock(30000); 

  try {
    const params = e.parameter;
    const action = params.action;

    if (!action) {
      return createJSONResponse({ 
        status: 'Online', 
        system: 'Vanguard OnePass', 
        version: '4.3.0',
        timestamp: new Date().toISOString() 
      });
    }

    let result = { success: false };

    switch (action) {
      case 'getMembers':
        result = { success: true, data: fetchAllMembers() };
        break;
      case 'sync':
        result = { success: true, message: runCompiler() };
        break;
      default:
        result = { success: false, message: 'Invalid GET Action' };
    }
    return createJSONResponse(result);
  } catch (error) {
    return createJSONResponse({ success: false, error: error.toString() });
  } finally {
    lock.releaseLock();
  }
}

function doPost(e) {
  const lock = LockService.getScriptLock();
  lock.tryLock(30000);

  try {
    const data = JSON.parse(e.postData.contents);
    const action = data.action;
    let result = { success: false };

    if (action === 'uploadPhoto') {
        result = handlePhotoUpload(data.id, data.photo);
    } else if (action === 'bulkUpdate') {
        result = handleBulkUpdate(data.ids, data.updates);
    } else if (action === 'scan') {
        result = handleScan(data.id);
    }

    return createJSONResponse(result);
  } catch (error) {
    return createJSONResponse({ success: false, error: error.toString() });
  } finally {
    lock.releaseLock();
  }
}

// --- CORE HANDLERS ---

function handlePhotoUpload(memberId, base64String) {
  const sheet = getSheet(CONFIG.SHEET_DB);
  const data = sheet.getDataRange().getValues();
  // Find member row
  for (let i = 1; i < data.length; i++) {
    if (String(data[i][0]) === String(memberId)) {
        // Col 5 is Photo URL (index 4)
        sheet.getRange(i + 1, 5).setValue(base64String); 
        return { success: true, message: 'Photo Updated' };
    }
  }
  return { success: false, message: 'Member not found' };
}

function handleBulkUpdate(ids, updates) {
  const sheet = getSheet(CONFIG.SHEET_DB);
  const data = sheet.getDataRange().getValues();
  const idSet = new Set(ids);
  
  // Map fields to column indexes
  const colMap = { 'status': 4, 'role': 3 }; // 1-based index: Role=Col 3, Status=Col 4
  
  for (let i = 1; i < data.length; i++) {
     if (idSet.has(String(data[i][0]))) {
         if (updates.status) sheet.getRange(i + 1, colMap.status).setValue(updates.status);
         if (updates.role) sheet.getRange(i + 1, colMap.role).setValue(updates.role);
     }
  }
  return { success: true };
}

function fetchAllMembers() {
  const sheet = getSheet(CONFIG.SHEET_DB);
  const data = sheet.getDataRange().getValues();
  data.shift(); 
  
  return data.map(row => ({
    id: String(row[0]),
    name: String(row[1]),
    role: String(row[2]),
    status: String(row[3]),
    photoUrl: String(row[4]),
    walletBalance: Number(row[5]) || 0,
    outstandingFines: Number(row[6]) || 0,
    rewardPoints: Number(row[7]) || 0
  })).filter(m => m.id);
}

function runCompiler() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let targetSheet = ss.getSheetByName(CONFIG.SHEET_DB);
  
  if (!targetSheet) {
    targetSheet = ss.insertSheet(CONFIG.SHEET_DB);
    targetSheet.appendRow(['Member ID', 'Full Name', 'Role', 'Status', 'Photo URL', 'Wallet Balance', 'Outstanding Fines', 'Reward Points']);
  }

  // 1. Snapshot existing finance
  const existingFinance = {};
  const currentData = targetSheet.getDataRange().getValues();
  currentData.shift(); 
  currentData.forEach(row => {
    if (row[0]) existingFinance[String(row[0])] = { wallet: row[5], fines: row[6], points: row[7] };
  });

  const compiledMembers = [];
  const processedIDs = new Set();

  CONFIG.SOURCES.forEach(sourceName => {
    const sheet = ss.getSheetByName(sourceName);
    if (!sheet) return;

    const rawData = sheet.getDataRange().getValues();
    if (rawData.length < 2) return;
    const headers = rawData.shift();

    const map = {
      id: findColumn(headers, ['Member ID', 'ID', 'Reg No']),
      name: findColumn(headers, ['Full Name', 'Name', 'Student Name']),
      role: findColumn(headers, ['Role', 'Position']),
      status: findColumn(headers, ['Status', 'State'])
    };

    if (map.id === -1 || map.name === -1) return;

    rawData.forEach(row => {
      let id = String(row[map.id] || "").trim();
      if (!id || processedIDs.has(id)) return;

      const finance = existingFinance[id] || { wallet: 0, fines: 0, points: 0 };
      const role = map.role > -1 ? row[map.role] : 'Member';
      const status = map.status > -1 ? row[map.status] : 'Active';

      compiledMembers.push([
        id,
        String(row[map.name] || "").trim(),
        role || 'Member',
        status || 'Active',
        '', // Photo placeholder if not handled by bulk upload
        finance.wallet,
        finance.fines,
        finance.points
      ]);

      processedIDs.add(id);
    });
  });

  if (compiledMembers.length > 0) {
    if (targetSheet.getLastRow() > 1) {
       targetSheet.getRange(2, 1, targetSheet.getLastRow()-1, 8).clearContent();
    }
    targetSheet.getRange(2, 1, compiledMembers.length, 8).setValues(compiledMembers);
    return `Compiled ${compiledMembers.length} members.`;
  }
  return "No data found.";
}

function findColumn(headers, candidates) {
  for (let c of candidates) {
    const idx = headers.findIndex(h => String(h).toLowerCase() === c.toLowerCase());
    if (idx > -1) return idx;
  }
  return -1;
}

function getSheet(name) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let sheet = ss.getSheetByName(name);
  if (!sheet) sheet = ss.insertSheet(name);
  return sheet;
}

function createJSONResponse(data) {
  return ContentService.createTextOutput(JSON.stringify(data))
    .setMimeType(ContentService.MimeType.JSON);
}
