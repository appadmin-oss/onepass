/**
 * Vanguard OnePass™ - Backend Logic
 * 
 * CORE FUNCTIONALITY:
 * - doGet: Serves the Web App
 * - doPost: Handles QR Scans from external devices
 * - API: Handles frontend requests via google.script.run
 */

// --- CONFIGURATION ---
const SPREADSHEET_ID = 'YOUR_SPREADSHEET_ID_HERE'; // User to replace
const SHEET_NAMES = {
  DB: 'Central DB',
  CONFIG: 'Config',
  LEDGER: 'Transactions',
  LOGS: 'Access Logs'
};

// --- WEB APP SERVING ---

function doGet(e) {
  // Determine if we serve mobile/admin/reception specific view or the main SPA
  // For SPA, we serve index.html
  return HtmlService.createTemplateFromFile('index')
    .evaluate()
    .setTitle('Vanguard OnePass')
    .addMetaTag('viewport', 'width=device-width, initial-scale=1')
    .setXFrameOptionsMode(HtmlService.XFrameOptionsMode.ALLOWALL);
}

function include(filename) {
  return HtmlService.createHtmlOutputFromFile(filename).getContent();
}

// --- CLOUD BRAIN (QR SCANNER ENDPOINT) ---

function doPost(e) {
  // Handles POST requests from QR Scanner app
  // Expects JSON payload: { "id": "VG001" }
  
  let result = { allowed: false, message: "System Error" };
  
  try {
    const postData = JSON.parse(e.postData.contents);
    const memberId = postData.id;
    
    if (!memberId) throw new Error("No ID provided");
    
    result = processScan(memberId);
    
  } catch (error) {
    result.message = error.message;
    console.error("doPost Error", error);
  }
  
  return ContentService.createTextOutput(JSON.stringify(result))
    .setMimeType(ContentService.MimeType.JSON);
}

// --- CORE LOGIC ---

function processScan(memberId) {
  const db = getDB();
  const member = db.find(m => m.id === memberId);
  const logSheet = getSheet(SHEET_NAMES.LOGS);
  
  if (!member) {
    logAccess(memberId, 'SCAN', 'DENIED', 'Unknown ID');
    return { allowed: false, message: "Member Not Found" };
  }
  
  // Status Checks
  if (member.status === 'Suspended' || member.status === 'Blocked') {
    logAccess(memberId, 'SCAN', 'DENIED', member.status);
    return { 
      allowed: false, 
      message: `Access Denied: ${member.status}`,
      member: sanitizeMember(member)
    };
  }
  
  // Late Check
  const now = new Date();
  const config = getConfig();
  const resumptionTime = parseTime(config.resumptionTime || "08:30");
  
  let isLate = false;
  if (now.getHours() > resumptionTime.hours || (now.getHours() === resumptionTime.hours && now.getMinutes() > resumptionTime.minutes)) {
    isLate = true;
    // Apply Fine Logic
    applyLateFine(member, config.lateFineAmount);
  }
  
  logAccess(memberId, 'SCAN', 'GRANTED', isLate ? 'Late Entry' : 'On Time');
  
  return {
    allowed: true,
    name: member.name,
    role: member.role,
    photo: member.photoUrl,
    wallet: member.walletBalance,
    fines: member.outstandingFines,
    status: isLate ? 'Late' : member.status
  };
}

// --- API FOR FRONTEND ---

function loginMember(id, password) {
  const member = getMemberById(id);
  if (!member) return { success: false, error: 'Member not found' };
  
  // In production, use utilities.computeDigest(SHA_256)
  // For MVP/Demo, simple comparison (Assume password stored in hidden column or separate sheet)
  // Here assuming password = "VanguardWallet" for demo
  const validPass = password === "VanguardWallet"; 
  
  if (validPass) {
    return { success: true, member: sanitizeMember(member) };
  }
  return { success: false, error: 'Invalid Password' };
}

function getMemberDetails(id) {
  const m = getMemberById(id);
  return m ? sanitizeMember(m) : null;
}

function getMemberHistory(id) {
  const sheet = getSheet(SHEET_NAMES.LEDGER);
  const data = sheet.getDataRange().getValues();
  const headers = data.shift();
  
  // Simple filter - in production use optimized query
  const txs = data.filter(row => row[1] === id).map(row => ({
    timestamp: row[0],
    memberId: row[1],
    type: row[2],
    amount: row[3],
    description: row[4],
    reference: row[5]
  }));
  
  // Return last 20 reversed
  return txs.reverse().slice(0, 20);
}

function getAllMembers() {
  return getDB().map(sanitizeMember);
}

function getSystemStats() {
  const members = getDB();
  const totalWallet = members.reduce((sum, m) => sum + (m.walletBalance || 0), 0);
  const totalFines = members.reduce((sum, m) => sum + (m.outstandingFines || 0), 0);
  
  // Count logs for today
  const logSheet = getSheet(SHEET_NAMES.LOGS);
  // This is expensive in GAS, optimize in prod
  const activeToday = 0; // Placeholder for logic: count unique IDs in LOGS where date == today
  
  return {
    totalMembers: members.length,
    activeToday: 42, // Mock for speed
    totalWallet: totalWallet,
    totalFines: totalFines
  };
}

// --- HELPERS ---

function getSheet(name) {
  const ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  let sheet = ss.getSheetByName(name);
  if (!sheet) {
    sheet = ss.insertSheet(name);
    // Initialize headers if new
    if (name === SHEET_NAMES.DB) sheet.appendRow(['Member ID', 'Full Name', 'Role', 'Status', 'Photo URL', 'Wallet Balance', 'Outstanding Fines', 'Reward Points']);
    if (name === SHEET_NAMES.LEDGER) sheet.appendRow(['Timestamp', 'Member ID', 'Type', 'Amount', 'Description', 'Reference']);
    if (name === SHEET_NAMES.LOGS) sheet.appendRow(['Timestamp', 'Member ID', 'Action', 'Status', 'Notes']);
  }
  return sheet;
}

function getDB() {
  const sheet = getSheet(SHEET_NAMES.DB);
  const data = sheet.getDataRange().getValues();
  const headers = data.shift();
  return data.map(row => ({
    id: row[0],
    name: row[1],
    role: row[2],
    status: row[3],
    photoUrl: row[4],
    walletBalance: row[5],
    outstandingFines: row[6],
    rewardPoints: row[7]
  }));
}

function getMemberById(id) {
  return getDB().find(m => m.id === id);
}

function sanitizeMember(m) {
  // Remove sensitive data if any
  return m;
}

function logAccess(id, action, status, notes) {
  const sheet = getSheet(SHEET_NAMES.LOGS);
  sheet.appendRow([new Date(), id, action, status, notes]);
}

function applyLateFine(member, amount) {
  if (!amount || amount <= 0) return;
  const sheet = getSheet(SHEET_NAMES.LEDGER);
  sheet.appendRow([new Date(), member.id, 'Fine', -amount, 'Late Arrival Fine', 'AUTO']);
  
  // Update DB cache/sheet immediately? 
  // For MVP, we rely on Ledger to calculate balance, but DB has a 'Outstanding Fines' cache column.
  // We need to update that column.
  updateMemberField(member.id, 6, (member.outstandingFines || 0) + amount); // 6 is Fines column index (0-based)
}

function updateMemberField(id, colIndex, value) {
  const sheet = getSheet(SHEET_NAMES.DB);
  const data = sheet.getDataRange().getValues();
  for (let i = 1; i < data.length; i++) {
    if (data[i][0] === id) {
      sheet.getRange(i + 1, colIndex + 1).setValue(value);
      break;
    }
  }
}

function getConfig() {
  return {
    resumptionTime: "08:30",
    lateFineAmount: 5000
  };
}

function parseTime(timeStr) {
  const [h, m] = timeStr.split(':').map(Number);
  return { hours: h, minutes: m };
}
