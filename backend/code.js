
/**
 * VANGUARD ONEPASS™ - CLOUD CORE v4.4.1
 * (Production Google Apps Script)
 */

const CONFIG = {
  DB_NAME: "Central DB",
  RULES_NAME: "Config",
  LOGS_NAME: "Access Logs",
  SOURCES: ["Import-MGT", "Import-NGV", "Import-NGG", "Import-MAM"]
};

function doGet(e) {
  const lock = LockService.getScriptLock();
  lock.tryLock(30000); 
  try {
    const action = e.parameter.action;
    if (action === 'getMembers') return json({ success: true, data: fetchMembers() });
    if (action === 'syncPreCheck') return json({ success: true, data: fetchMembers() });
    return json({ success: false, error: "Undefined Protocol" });
  } catch (err) {
    return json({ success: false, error: err.toString() });
  } finally {
    lock.releaseLock();
  }
}

function doPost(e) {
  const lock = LockService.getScriptLock();
  lock.tryLock(30000);
  try {
    const data = JSON.parse(e.postData.contents);
    if (data.action === 'syncCommit') return json(commitData(data.data));
    if (data.action === 'uploadPhoto') return json(updatePhoto(data.id, data.photo));
    if (data.action === 'bulkUpdate') return json(handleBulkUpdate(data.ids, data.updates));
    if (data.action === 'updateConfig') return json(updateRules(data.config));
    return json({ success: false, error: "Protocol Mismatch" });
  } catch (err) {
    return json({ success: false, error: err.toString() });
  } finally {
    lock.releaseLock();
  }
}

function fetchMembers() {
  const sheet = getSheet(CONFIG.DB_NAME);
  const rows = sheet.getDataRange().getValues();
  rows.shift();
  return rows.map(r => ({
    id: String(r[0]),
    name: String(r[1]),
    role: String(r[2]),
    status: String(r[3]),
    photoUrl: String(r[4]),
    walletBalance: Number(r[5]) || 0,
    outstandingFines: Number(r[6]) || 0,
    rewardPoints: Number(r[7]) || 0
  }));
}

function commitData(data) {
  const sheet = getSheet(CONFIG.DB_NAME);
  sheet.getRange(2, 1, sheet.getLastRow(), 8).clearContent();
  const rows = data.map(m => [m.id, m.name, m.role, m.status, m.photoUrl, m.walletBalance, m.outstandingFines, m.rewardPoints]);
  sheet.getRange(2, 1, rows.length, 8).setValues(rows);
  return { success: true, message: "Sheet synchronized with Hub Database." };
}

function updatePhoto(id, photo) {
  const sheet = getSheet(CONFIG.DB_NAME);
  const data = sheet.getDataRange().getValues();
  for (let i = 1; i < data.length; i++) {
    if (String(data[i][0]) === String(id)) {
      sheet.getRange(i + 1, 5).setValue(photo);
      return { success: true };
    }
  }
  return { success: false, error: "Identity not found." };
}

function handleBulkUpdate(ids, updates) {
  const sheet = getSheet(CONFIG.DB_NAME);
  const data = sheet.getDataRange().getValues();
  const idSet = new Set(ids);
  for (let i = 1; i < data.length; i++) {
    if (idSet.has(String(data[i][0]))) {
      if (updates.status) sheet.getRange(i+1, 4).setValue(updates.status);
      if (updates.role) sheet.getRange(i+1, 3).setValue(updates.role);
    }
  }
  return { success: true };
}

function updateRules(cfg) {
  const sheet = getSheet(CONFIG.RULES_NAME);
  sheet.clear();
  sheet.appendRow(["Resumption", "LateFine", "SuspendLimit"]);
  sheet.appendRow([cfg.resumptionTime, cfg.lateFineAmount, cfg.autoSuspendThreshold]);
  return { success: true };
}

function getSheet(name) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let s = ss.getSheetByName(name);
  if (!s) {
    s = ss.insertSheet(name);
    if (name === CONFIG.DB_NAME) s.appendRow(["ID", "Name", "Role", "Status", "Photo", "Wallet", "Fines", "Points"]);
  }
  return s;
}

function json(d) {
  return ContentService.createTextOutput(JSON.stringify(d)).setMimeType(ContentService.MimeType.JSON);
}
