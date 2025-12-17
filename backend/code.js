/**
 * Vanguard OnePass™ - Backend Logic (Google Apps Script)
 * 
 * DEPLOYMENT INSTRUCTIONS:
 * 1. Create a new Google Sheet.
 * 2. Extensions > Apps Script.
 * 3. Paste this code into Code.gs.
 * 4. Deploy > New Deployment > Type: Web App.
 * 5. Execute as: Me (your account).
 * 6. Who has access: Anyone.
 * 7. Copy the Web App URL and paste it into the Admin Dashboard > Integration tab.
 */

// --- CONFIGURATION ---
const SHEET_NAMES = {
  DB: 'Central DB',
  CONFIG: 'Config',
  LEDGER: 'Transactions',
  LOGS: 'Access Logs'
};

const ORG_ID_DEFAULT = 'ORG_CACENTRE_001';

// --- API GATEWAY ---

function doGet(e) {
  // Handle CORS and Response formatting
  const lock = LockService.getScriptLock();
  lock.tryLock(10000);

  try {
    const action = e.parameter.action;
    
    // Health Check
    if (!action) {
      return response({ status: 'Online', version: 'v4.1.0', mode: 'CACENTRE' });
    }

    let result = { success: false, message: 'Unknown Action' };

    switch (action) {
      case 'getMembers':
        result = { success: true, data: getAllMembers() };
        break;
      case 'sync':
        compileCentralDB();
        result = { success: true, message: 'Central DB Compiled Successfully' };
        break;
      case 'sendEmail':
        const sent = sendEmailInternal(e.parameter.to, e.parameter.subject, e.parameter.body);
        result = { success: sent, message: sent ? 'Email Sent' : 'Email Failed' };
        break;
      case 'getStats':
        result = { success: true, data: getSystemStats() };
        break;
      default:
        result = { success: false, message: 'Invalid Action Endpoint' };
    }
    
    return response(result);
    
  } catch (error) {
    return response({ success: false, error: error.toString() });
  } finally {
    lock.releaseLock();
  }
}

function doPost(e) {
  try {
    const postData = JSON.parse(e.postData.contents);
    const action = e.parameter.action || postData.action || 'scan';
    let result = { success: false };

    if (action === 'scan') {
       result = processScan(postData.id);
    } else if (action === 'log_transaction') {
       logTransaction(postData);
       result = { success: true };
    }
    
    return response(result);
  } catch (error) {
    return response({ success: false, error: error.toString() });
  }
}

// --- CORE LOGIC ---

function getAllMembers() {
  const sheet = getSheet(SHEET_NAMES.DB);
  const data = sheet.getDataRange().getValues();
  const headers = data.shift(); // Remove headers
  
  if (!data || data.length === 0) return [];

  // Map Central DB columns to Member Interface
  // Assumes strictly enforced headers: Member ID | Name | Role | Status | Photo URL | Wallet | Fines | Points
  return data.map(row => ({
    id: String(row[0]),
    organizationId: ORG_ID_DEFAULT,
    name: String(row[1]),
    role: String(row[2]),
    status: String(row[3]),
    photoUrl: String(row[4]),
    walletBalance: Number(row[5]) || 0,
    outstandingFines: Number(row[6]) || 0,
    rewardPoints: Number(row[7]) || 0,
    email: String(row[8] || "")
  })).filter(m => m.id && m.id !== '');
}

function compileCentralDB() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let targetSheet = ss.getSheetByName(SHEET_NAMES.DB);
  if (!targetSheet) {
    targetSheet = ss.insertSheet(SHEET_NAMES.DB);
    // Set Header
    targetSheet.getRange(1, 1, 1, 9).setValues([['Member ID', 'Full Name', 'Role', 'Status', 'Photo URL', 'Wallet Balance', 'Outstanding Fines', 'Reward Points', 'Email']]);
    targetSheet.setFrozenRows(1);
  }

  // Sources to compile
  const sources = ['Import-MGT', 'Import-NGV', 'Import-NGG', 'Import-MAM'];
  const compiledData = [];
  const existingMap = getExistingWalletMap(targetSheet);

  sources.forEach(sourceName => {
    const sheet = ss.getSheetByName(sourceName);
    if (!sheet) return;

    const data = sheet.getDataRange().getValues();
    const headers = data.shift();
    if (!data.length) return;

    // Dynamic Column Mapping
    const map = {
      id: headers.indexOf('Member ID'),
      name: headers.indexOf('Full Name'),
      role: headers.indexOf('Role'),
      status: headers.indexOf('Status'),
      photo: headers.indexOf('Image') > -1 ? headers.indexOf('Image') : headers.indexOf('Photo URL'),
      email: headers.indexOf('Email')
    };

    // If critical columns missing, try smart matching or skip
    if (map.id === -1) map.id = 0; 
    if (map.name === -1) map.name = 1;

    data.forEach(row => {
      const id = String(row[map.id]);
      if (!id || id === '') return;

      // Preserve existing wallet/fines/points if member exists
      const existing = existingMap[id] || { wallet: 0, fines: 0, points: 0 };

      compiledData.push([
        id,
        row[map.name],
        map.role > -1 ? row[map.role] : getDefaultRole(sourceName),
        map.status > -1 ? row[map.status] : 'Active',
        map.photo > -1 ? row[map.photo] : '',
        existing.wallet,
        existing.fines,
        existing.points,
        map.email > -1 ? row[map.email] : ''
      ]);
    });
  });

  // Write Back to Central DB
  if (compiledData.length > 0) {
    // Clear content but leave headers
    const lastRow = targetSheet.getLastRow();
    if (lastRow > 1) targetSheet.getRange(2, 1, lastRow - 1, 9).clearContent();
    
    targetSheet.getRange(2, 1, compiledData.length, 9).setValues(compiledData);
  }
  
  return true;
}

// Helper to preserve financial data during sync
function getExistingWalletMap(sheet) {
  const data = sheet.getDataRange().getValues();
  data.shift(); // Remove headers
  const map = {};
  data.forEach(row => {
    if (row[0]) {
      map[String(row[0])] = {
        wallet: row[5],
        fines: row[6],
        points: row[7]
      };
    }
  });
  return map;
}

function getDefaultRole(source) {
  if (source.includes('MGT')) return 'Management';
  if (source.includes('NGV')) return 'Vanguard';
  return 'Member';
}

function sendEmailInternal(to, subject, body) {
  if (!to) return false;
  try {
    MailApp.sendEmail({
      to: to,
      subject: `[CACENTRE] ${subject}`,
      htmlBody: body,
      name: 'Vanguard OnePass System'
    });
    return true;
  } catch (e) {
    console.error('Email Error: ' + e.toString());
    return false;
  }
}

function processScan(id) {
  // Hardware simulation endpoint
  const sheet = getSheet(SHEET_NAMES.LOGS);
  sheet.appendRow([new Date(), id, 'SCAN', 'GRANTED', 'Hardware/API']);
  return { allowed: true, message: 'Logged via API' };
}

function logTransaction(data) {
  const sheet = getSheet(SHEET_NAMES.LEDGER);
  sheet.appendRow([new Date(), data.memberId, data.type, data.amount, data.description, 'API']);
}

function getSystemStats() {
  const members = getAllMembers();
  return {
    totalMembers: members.length,
    activeToday: 0 // Calculation requires logs parsing, simplified for now
  };
}

// --- UTILS ---

function getSheet(name) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let sheet = ss.getSheetByName(name);
  if (!sheet) sheet = ss.insertSheet(name);
  return sheet;
}

function response(data) {
  return ContentService.createTextOutput(JSON.stringify(data))
    .setMimeType(ContentService.MimeType.JSON);
}
