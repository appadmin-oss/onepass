import { Member, Transaction, ScanResult, Status, Role, SystemConfig, AccessLog, WithdrawalRequest, Visitor, Session, JourneyItem, WeeklyReport, DeviceEvent, Organization, SessionName } from '../types';

// --- MOCK DATABASE ---

const CURRENT_ORG: Organization = {
  id: 'ORG_CACENTRE_001',
  name: 'CACENTRE',
  type: 'CACENTRE'
};

const MOCK_SESSIONS: Session[] = [
  { id: 'SES_001', name: 'Toni', startDate: '2025-01-01', endDate: '2025-03-31', isActive: false, weekCurrent: 12, weekTotal: 12 },
  { id: 'SES_002', name: 'Medi', startDate: '2025-04-01', endDate: '2025-06-30', isActive: true, weekCurrent: 4, weekTotal: 12 },
  { id: 'SES_003', name: 'Summer', startDate: '2025-07-01', endDate: '2025-09-30', isActive: false, weekCurrent: 0, weekTotal: 12 },
  { id: 'SES_004', name: 'Domi', startDate: '2025-10-01', endDate: '2025-12-31', isActive: false, weekCurrent: 0, weekTotal: 12 },
];

const MOCK_JOURNEY: JourneyItem[] = [
  { id: 'J_001', sessionId: 'SES_002', title: 'Complete 10 Weekly Attendances', description: 'Mandatory attendance for Medi session', category: 'Attendance', required: true, status: 'In Progress', progress: 40 },
  { id: 'J_002', sessionId: 'SES_002', title: 'Leadership Workshop', description: 'Attend the mid-session workshop', category: 'Development', required: false, status: 'Pending', progress: 0 },
  { id: 'J_003', sessionId: 'SES_002', title: 'Pay Outstanding Fines', description: 'Clear all late fines before week 6', category: 'Participation', required: true, status: 'Completed', progress: 100 },
];

const MOCK_MEMBERS: Member[] = [
  { id: 'VG001', organizationId: CURRENT_ORG.id, name: 'Sarah Connor', role: Role.MEMBER, status: Status.ACTIVE, photoUrl: 'https://picsum.photos/200', walletBalance: 15000, outstandingFines: 0, rewardPoints: 120 },
  { id: 'VG002', organizationId: CURRENT_ORG.id, name: 'John Wick', role: Role.MEMBER, status: Status.LATE, photoUrl: 'https://picsum.photos/201', walletBalance: 2500, outstandingFines: 5000, rewardPoints: 450 },
  { id: 'VG003', organizationId: CURRENT_ORG.id, name: 'Tony Stark', role: Role.MEMBER, status: Status.ACTIVE, photoUrl: 'https://picsum.photos/203', walletBalance: 1000000, outstandingFines: 0, rewardPoints: 9000 },
  { id: 'VG004', organizationId: CURRENT_ORG.id, name: 'Bruce Wayne', role: Role.MEMBER, status: Status.SUSPENDED, photoUrl: 'https://picsum.photos/204', walletBalance: -50000, outstandingFines: 0, rewardPoints: 0 },
  { id: 'ADM01', organizationId: CURRENT_ORG.id, name: 'System Admin', role: Role.ADMIN, status: Status.ACTIVE, photoUrl: 'https://picsum.photos/202', walletBalance: 0, outstandingFines: 0, rewardPoints: 0 }
];

const MOCK_VISITORS: Visitor[] = [
  { 
    id: 'VIS-9999', 
    organizationId: CURRENT_ORG.id,
    name: 'Test Visitor', 
    hostName: 'Sarah Connor', 
    purpose: 'Meeting', 
    checkInTime: new Date(Date.now() - 3600000).toISOString(), 
    expiresAt: new Date(Date.now() + 82800000).toISOString(), 
    status: 'Checked In'
  }
];

const MOCK_TRANSACTIONS: Transaction[] = [
  { id: 'TX1', timestamp: new Date().toISOString(), memberId: 'VG001', type: 'Credit', amount: 20000, description: 'Wallet Top-up' },
  { id: 'TX2', timestamp: new Date(Date.now() - 86400000).toISOString(), memberId: 'VG001', type: 'Debit', amount: -5000, description: 'Lunch' },
  { id: 'TX3', timestamp: new Date(Date.now() - 172800000).toISOString(), memberId: 'VG002', type: 'Fine', amount: -5000, description: 'Late Arrival' },
];

let MOCK_CONFIG: SystemConfig = {
  resumptionTime: "08:30",
  lateFineAmount: 5000,
  autoSuspendThreshold: -10000,
  maintenanceMode: false,
  currentSessionId: 'SES_002'
};

const MOCK_LOGS: AccessLog[] = [
  { id: 'L1', timestamp: new Date().toISOString(), memberId: 'VG001', action: 'SCAN', status: 'GRANTED', notes: 'On Time', deviceId: 'DEV_01' },
];

let MOCK_WITHDRAWALS: WithdrawalRequest[] = [
  { id: 'W1', memberId: 'VG001', memberName: 'Sarah Connor', amount: 5000, timestamp: new Date().toISOString(), status: 'Pending' }
];

const MOCK_REPORTS: WeeklyReport[] = Array.from({ length: 12 }, (_, i) => ({
  id: `REP_${i}`,
  weekNumber: i + 1,
  year: 2025,
  session: 'Toni' as SessionName,
  attendanceRate: 85 + Math.random() * 10,
  totalFines: Math.floor(Math.random() * 50000),
  generatedAt: new Date().toISOString(),
  url: '#'
})).reverse();


// Helper to simulate GAS calls
const runGas = async (functionName: string, ...args: any[]): Promise<any> => {
  // Simulate network delay
  await new Promise(r => setTimeout(r, 400)); 
    
  switch (functionName) {
    case 'loginMember':
      const [id, pwd] = args;
      const mem = MOCK_MEMBERS.find(m => m.id === id);
      if (id === 'ADMIN' && pwd === 'admin') return { success: true, member: MOCK_MEMBERS.find(m => m.id === 'ADM01') };
      if (mem) return { success: true, member: mem };
      return { success: false, error: 'Invalid ID' };
    
    case 'processHardwareEvent':
       const event: DeviceEvent = args[0];
       // Normalize logic
       if (event.actor_type === 'MEMBER') {
           const member = MOCK_MEMBERS.find(m => m.id === event.actor_id);
           if (!member) return { allowed: false, message: 'Hardware: Unknown ID' };
           if (member.status === Status.BLOCKED || member.status === Status.SUSPENDED) {
               return { allowed: false, member, message: `Hardware: ${member.status}` };
           }
           MOCK_LOGS.unshift({
               id: 'L_HW_' + Date.now(),
               timestamp: event.timestamp,
               memberId: member.id,
               action: event.event_type,
               status: 'GRANTED',
               deviceId: event.device_id,
               notes: 'Hardware Authenticated'
           });
           return { allowed: true, member, message: 'Hardware Access Granted' };
       }
       return { allowed: false, message: 'Visitor Hardware Not Implemented' };

    case 'processScan':
        // Legacy/Mobile Scan
       const visitor = MOCK_VISITORS.find(v => v.id === args[0]);
       if (visitor) {
           const now = new Date();
           if (visitor.status === 'Checked Out') return { allowed: false, isVisitor: true, member: visitor, message: 'Checked Out' };
           if (now > new Date(visitor.expiresAt)) return { allowed: false, isVisitor: true, member: visitor, message: 'Pass Expired' };
           return { allowed: true, isVisitor: true, member: visitor, message: 'Visitor Access Granted' };
       }

       const scannedMem = MOCK_MEMBERS.find(m => m.id === args[0]);
       if (!scannedMem) return { allowed: false, message: 'ID Not Recognized' };
       
       const isBlocked = scannedMem.status === Status.BLOCKED || scannedMem.status === Status.SUSPENDED;
       
       MOCK_LOGS.unshift({
          id: 'L' + Date.now(),
          timestamp: new Date().toISOString(),
          memberId: scannedMem.id,
          action: 'SCAN',
          status: isBlocked ? 'DENIED' : 'GRANTED',
          notes: scannedMem.status
       });
       return {
         allowed: !isBlocked,
         member: scannedMem,
         message: isBlocked ? `Access Denied: ${scannedMem.status}` : 'Welcome'
       };
       
    case 'getSessions': return MOCK_SESSIONS;
    case 'getJourney': return MOCK_JOURNEY;
    case 'getWeeklyReports': return MOCK_REPORTS;
    case 'generateHardwareConfig': 
        return {
            org_id: CURRENT_ORG.id,
            api_endpoint: 'https://api.cacentre.platform/v1/devices',
            sync_interval: 300,
            offline_mode: true,
            keys: {
                public: 'MOCK_PUB_KEY_123',
                secret: 'MOCK_SECRET_KEY_XYZ'
            }
        };

    case 'acknowledgeDashboard':
        const ackId = args[0];
        const m = MOCK_MEMBERS.find(x => x.id === ackId);
        if (m) m.lastDashboardView = new Date().toISOString();
        return true;

    // ... Standard CRUD ...
    case 'getMemberDetails': return MOCK_MEMBERS.find(m => m.id === args[0]);
    case 'getMemberHistory': return MOCK_TRANSACTIONS.filter(t => t.memberId === args[0]);
    case 'getAllMembers': return MOCK_MEMBERS;
    case 'getSystemStats': 
      return { 
          totalMembers: MOCK_MEMBERS.length, 
          activeToday: 45, 
          totalFines: MOCK_MEMBERS.reduce((acc, m) => acc + m.outstandingFines, 0),
          totalWallet: MOCK_MEMBERS.reduce((acc, m) => acc + m.walletBalance, 0)
      };
    case 'getSystemConfig': return MOCK_CONFIG;
    case 'updateSystemConfig': MOCK_CONFIG = { ...MOCK_CONFIG, ...args[0] }; return true;
    case 'triggerCompiler': return { success: true, message: 'Merged successfully.' };
    case 'adminUpdateMember':
      const target = MOCK_MEMBERS.find(m => m.id === args[0]);
      if (target) Object.assign(target, args[1]);
      return true;
    case 'adminBulkUpdateMembers':
      const [ids, updates] = args;
      ids.forEach((i: string) => {
          const m = MOCK_MEMBERS.find(x => x.id === i);
          if(m) Object.assign(m, updates);
      });
      return true;
    case 'adminPostTransaction':
      MOCK_TRANSACTIONS.unshift({
          id: 'MANUAL_' + Date.now(),
          timestamp: new Date().toISOString(),
          memberId: args[0],
          type: args[1],
          amount: args[2],
          description: args[3],
          reference: 'ADMIN'
      });
      return true;
    case 'getAccessLogs': return MOCK_LOGS;
    case 'getPendingWithdrawals': return MOCK_WITHDRAWALS.filter(w => w.status === 'Pending');
    case 'createVisitorPass':
      const [vName, vHost, vPurpose] = args;
      const vId = 'VIS-' + Math.floor(1000 + Math.random() * 9000);
      const expiresAt = new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString();
      const newVisitor: Visitor = {
          id: vId,
          organizationId: CURRENT_ORG.id,
          name: vName,
          hostName: vHost,
          purpose: vPurpose,
          checkInTime: new Date().toISOString(),
          expiresAt: expiresAt,
          status: 'Checked In'
      };
      MOCK_VISITORS.push(newVisitor);
      return newVisitor;
    case 'checkoutVisitor':
       const checkoutId = args[0];
       const vIndex = MOCK_VISITORS.findIndex(v => v.id === checkoutId || v.name.toLowerCase() === checkoutId.toLowerCase());
       if (vIndex > -1) {
           MOCK_VISITORS[vIndex].status = 'Checked Out';
           MOCK_VISITORS[vIndex].checkOutTime = new Date().toISOString();
           return { success: true, visitor: MOCK_VISITORS[vIndex] };
       }
       return { success: false, message: 'Visitor not found' };
    case 'getVisitorLogs': return MOCK_VISITORS;
    case 'processWithdrawal':
      const [wId, wStatus] = args;
      const w = MOCK_WITHDRAWALS.find(x => x.id === wId);
      if (w) w.status = wStatus;
      return true;
    case 'requestWithdrawal':
      const [reqId, reqAmount] = args;
      const reqMember = MOCK_MEMBERS.find(m => m.id === reqId);
      if(reqMember) {
          MOCK_WITHDRAWALS.push({
              id: 'W' + Date.now(),
              memberId: reqId,
              memberName: reqMember.name,
              amount: reqAmount,
              timestamp: new Date().toISOString(),
              status: 'Pending'
          });
          return { success: true };
      }
      return { success: false };
    case 'changePassword': return { success: true };
    default: return null;
  }
};

export const api = {
  login: (id: string, password: string) => runGas('loginMember', id, password),
  processHardwareEvent: (event: DeviceEvent) => runGas('processHardwareEvent', event),
  processScan: (id: string) => runGas('processScan', id),
  
  // Member
  getMember: (id: string) => runGas('getMemberDetails', id),
  getHistory: (id: string) => runGas('getMemberHistory', id),
  requestWithdrawal: (id: string, amount: number) => runGas('requestWithdrawal', id, amount),
  changePassword: (id: string, oldP: string, newP: string) => runGas('changePassword', id, oldP, newP),
  acknowledgeDashboard: (id: string) => runGas('acknowledgeDashboard', id),
  getJourney: (id: string) => runGas('getJourney', id),

  // Admin
  getAllMembers: () => runGas('getAllMembers'),
  getStats: () => runGas('getSystemStats'),
  getConfig: () => runGas('getSystemConfig'),
  updateConfig: (config: Partial<SystemConfig>) => runGas('updateSystemConfig', config),
  runCompiler: () => runGas('triggerCompiler'),
  updateMember: (id: string, updates: Partial<Member>) => runGas('adminUpdateMember', id, updates),
  bulkUpdateMembers: (ids: string[], updates: Partial<Member>) => runGas('adminBulkUpdateMembers', ids, updates),
  postTransaction: (memberId: string, type: string, amount: number, description: string) => runGas('adminPostTransaction', memberId, type, amount, description),
  getAccessLogs: () => runGas('getAccessLogs'),
  getPendingWithdrawals: () => runGas('getPendingWithdrawals'),
  processWithdrawal: (id: string, status: 'Approved' | 'Rejected') => runGas('processWithdrawal', id, status),
  
  // Platform / CACENTRE Specific
  getSessions: () => runGas('getSessions'),
  getWeeklyReports: () => runGas('getWeeklyReports'),
  generateHardwareConfig: () => runGas('generateHardwareConfig'),
  
  // Visitor
  createVisitor: (name: string, host: string, purpose: string) => runGas('createVisitorPass', name, host, purpose),
  checkoutVisitor: (idOrName: string) => runGas('checkoutVisitor', idOrName),
  getVisitorLogs: () => runGas('getVisitorLogs'),
};
