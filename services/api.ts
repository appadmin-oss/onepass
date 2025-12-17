import { Member, Transaction, ScanResult, Status, Role, SystemConfig, AccessLog, WithdrawalRequest, Visitor, Session, JourneyItem, WeeklyReport, DeviceEvent, Organization, SessionName } from '../types';

// --- MOCK DATABASE (Fallback) ---
const CURRENT_ORG: Organization = { id: 'ORG_CACENTRE_001', name: 'CACENTRE', type: 'CACENTRE' };
const MOCK_SESSIONS: Session[] = [
  { id: 'SES_001', name: 'Toni', startDate: '2025-01-01', endDate: '2025-03-31', isActive: false, weekCurrent: 12, weekTotal: 12 },
  { id: 'SES_002', name: 'Medi', startDate: '2025-04-01', endDate: '2025-06-30', isActive: true, weekCurrent: 4, weekTotal: 12 },
];
let MOCK_MEMBERS: Member[] = [
  { id: 'VG001', organizationId: CURRENT_ORG.id, name: 'Sarah Connor', role: Role.MEMBER, status: Status.ACTIVE, photoUrl: 'https://picsum.photos/200', walletBalance: 15000, outstandingFines: 0, rewardPoints: 120 },
  { id: 'ADM01', organizationId: CURRENT_ORG.id, name: 'System Admin', role: Role.ADMIN, status: Status.ACTIVE, photoUrl: 'https://picsum.photos/202', walletBalance: 0, outstandingFines: 0, rewardPoints: 0 }
];
let MOCK_CONFIG: SystemConfig = {
  resumptionTime: "08:30",
  lateFineAmount: 5000,
  autoSuspendThreshold: -10000,
  maintenanceMode: false,
  googleSheetsId: '',
  gasWebAppUrl: '',
  lastSyncTime: 'Never'
};

// --- GAS API UTILITY ---
const callGasApi = async (action: string, params: Record<string, any> = {}) => {
  if (!MOCK_CONFIG.gasWebAppUrl) return null;
  // If sending large data like images, switch to POST
  if (action === 'uploadPhoto' || action === 'bulkUpdate') {
      try {
          const response = await fetch(MOCK_CONFIG.gasWebAppUrl, {
              method: 'POST',
              body: JSON.stringify({ action, ...params })
          });
          return await response.json();
      } catch (e) { return null; }
  }

  const query = new URLSearchParams({ action, ...params }).toString();
  try {
      const response = await fetch(`${MOCK_CONFIG.gasWebAppUrl}?${query}`);
      return await response.json();
  } catch (e) {
      return null;
  }
}

// --- MAIN FUNCTION RUNNER ---
const runGas = async (functionName: string, ...args: any[]): Promise<any> => {
  // Simulate delay
  await new Promise(r => setTimeout(r, 400)); 
    
  switch (functionName) {
    case 'loginMember':
      const [id, pwd] = args;
      if (MOCK_CONFIG.gasWebAppUrl) {
           const res = await callGasApi('getMembers');
           if (res?.success) MOCK_MEMBERS = res.data;
      }
      const mem = MOCK_MEMBERS.find(m => m.id === id);
      if (id === 'ADMIN' && pwd === 'admin') return { success: true, member: MOCK_MEMBERS.find(m => m.id === 'ADM01') };
      if (mem) return { success: true, member: mem };
      return { success: false, error: 'Invalid ID' };

    case 'getAllMembers': 
      if (MOCK_CONFIG.gasWebAppUrl) {
          const res = await callGasApi('getMembers');
          if (res?.success) { MOCK_MEMBERS = res.data; return res.data; }
      }
      return MOCK_MEMBERS;

    case 'updateMember': // Single update (includes photo)
      const [uId, uData] = args;
      if (MOCK_CONFIG.gasWebAppUrl && uData.photoUrl) {
          await callGasApi('uploadPhoto', { id: uId, photo: uData.photoUrl });
      }
      const target = MOCK_MEMBERS.find(m => m.id === uId);
      if (target) Object.assign(target, uData);
      return true;

    case 'adminBulkUpdateMembers':
       const [bIds, bUpdates] = args;
       if (MOCK_CONFIG.gasWebAppUrl) {
           await callGasApi('bulkUpdate', { ids: bIds, updates: bUpdates });
       }
       bIds.forEach((i: string) => {
           const m = MOCK_MEMBERS.find(x => x.id === i);
           if(m) Object.assign(m, bUpdates);
       });
       return true;

    case 'syncWithGoogleSheets':
      if (MOCK_CONFIG.gasWebAppUrl) {
          const res = await callGasApi('sync');
          if (res?.success) {
               MOCK_CONFIG.lastSyncTime = new Date().toISOString();
               const mRes = await callGasApi('getMembers');
               if (mRes?.success) MOCK_MEMBERS = mRes.data;
               return res;
          }
          return { success: false, message: 'Sync failed' };
      }
      return { success: true, message: "Simulated Sync" };
    
    // ... keep existing mocks for brevity ...
    case 'generateHardwareConfig': return { org_id: 'ORG_ONEPASS', api_url: 'https://api.onepass.system', sync: 300 };
    case 'getStats': return { totalMembers: MOCK_MEMBERS.length };
    case 'getConfig': return MOCK_CONFIG;
    case 'updateSystemConfig': MOCK_CONFIG = { ...MOCK_CONFIG, ...args[0] }; return true;
    case 'getSessions': return MOCK_SESSIONS;
    case 'getVisitorLogs': return [];
    case 'getPendingWithdrawals': return [];
    case 'getWeeklyReports': return [];
    case 'getAccessLogs': return [];
    default: return null;
  }
};

export const api = {
  login: (id: string, password: string) => runGas('loginMember', id, password),
  
  // Google Integration
  syncWithGoogleSheets: () => runGas('syncWithGoogleSheets'),
  updateMember: (id: string, updates: Partial<Member>) => runGas('updateMember', id, updates),
  bulkUpdateMembers: (ids: string[], updates: Partial<Member>) => runGas('adminBulkUpdateMembers', ids, updates),
  
  // Standard Getters
  getAllMembers: () => runGas('getAllMembers'),
  getStats: () => runGas('getStats'),
  getConfig: () => runGas('getConfig'),
  updateConfig: (config: Partial<SystemConfig>) => runGas('updateSystemConfig', config),
  
  // Features
  getSessions: () => runGas('getSessions'),
  getWeeklyReports: () => runGas('getWeeklyReports'),
  generateHardwareConfig: () => runGas('generateHardwareConfig'),
  getAccessLogs: () => runGas('getAccessLogs'),
  getPendingWithdrawals: () => runGas('getPendingWithdrawals'),
  processWithdrawal: (id: string, status: string) => runGas('processWithdrawal', id, status),
  createVisitor: (n: string, h: string, p: string) => runGas('createVisitorPass', n, h, p),
  checkoutVisitor: (id: string) => runGas('checkoutVisitor', id),
  getVisitorLogs: () => runGas('getVisitorLogs'),
  
  // Legacy stubs
  processScan: (id: string) => Promise.resolve({ allowed: true, member: MOCK_MEMBERS[0] } as ScanResult),
  processHardwareEvent: (e: any) => Promise.resolve({ allowed: true, member: MOCK_MEMBERS[0] } as ScanResult),
  getHistory: (id: string) => Promise.resolve([] as Transaction[]),
  getJourney: (id: string) => Promise.resolve([] as JourneyItem[]),
  acknowledgeDashboard: (id: string) => Promise.resolve(true),
  requestWithdrawal: (id: string, a: number) => Promise.resolve({ success: true }),
};
