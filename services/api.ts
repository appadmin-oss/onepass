import { Member, Transaction, ScanResult, Status, Role, SystemConfig } from '../types';

// Mock data for development when not running in GAS
const MOCK_MEMBERS: Member[] = [
  {
    id: 'VG001',
    name: 'Sarah Connor',
    role: Role.MEMBER,
    status: Status.ACTIVE,
    photoUrl: 'https://picsum.photos/200',
    walletBalance: 15000,
    outstandingFines: 0,
    rewardPoints: 120
  },
  {
    id: 'VG002',
    name: 'John Wick',
    role: Role.MEMBER,
    status: Status.LATE,
    photoUrl: 'https://picsum.photos/201',
    walletBalance: 2500,
    outstandingFines: 5000,
    rewardPoints: 450
  },
  {
    id: 'ADM01',
    name: 'Admin User',
    role: Role.ADMIN,
    status: Status.ACTIVE,
    photoUrl: 'https://picsum.photos/202',
    walletBalance: 0,
    outstandingFines: 0,
    rewardPoints: 0
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
  maintenanceMode: false
};

// Helper to simulate GAS calls
const runGas = async (functionName: string, ...args: any[]): Promise<any> => {
  if (typeof window !== 'undefined' && (window as any).google && (window as any).google.script) {
    return new Promise((resolve, reject) => {
      (window as any).google.script.run
        .withSuccessHandler(resolve)
        .withFailureHandler(reject)
        [functionName](...args);
    });
  } else {
    // Mock simulation
    console.log(`[MOCK GAS] Calling ${functionName}`, args);
    await new Promise(r => setTimeout(r, 500)); // Simulate latency
    
    switch (functionName) {
      case 'loginMember':
        const [id, pwd] = args;
        const mem = MOCK_MEMBERS.find(m => m.id === id);
        // Simple mock admin check
        if (id === 'ADMIN' && pwd === 'admin') {
           return { success: true, member: MOCK_MEMBERS[2] };
        }
        if (mem) return { success: true, member: mem };
        return { success: false, error: 'Invalid ID' };
      case 'getMemberDetails':
        return MOCK_MEMBERS.find(m => m.id === args[0]);
      case 'getMemberHistory':
        return MOCK_TRANSACTIONS.filter(t => t.memberId === args[0]);
      case 'processScan':
         const scannedMem = MOCK_MEMBERS.find(m => m.id === args[0]);
         if (!scannedMem) return { allowed: false, message: 'Member not found' };
         return {
           allowed: scannedMem.status !== Status.BLOCKED,
           member: scannedMem,
           message: scannedMem.status === Status.BLOCKED ? 'Access Denied' : 'Welcome'
         };
      case 'getAllMembers':
        return MOCK_MEMBERS;
      case 'getSystemStats':
        return { totalMembers: 150, activeToday: 45, totalFines: 25000, totalWallet: 1500000 };
      
      // ADMIN FUNCTIONS
      case 'getSystemConfig':
        return MOCK_CONFIG;
      case 'updateSystemConfig':
        MOCK_CONFIG = { ...MOCK_CONFIG, ...args[0] };
        return true;
      case 'triggerCompiler':
        return { success: true, message: 'Merged 4 sheets successfully.' };
      case 'adminUpdateMember':
        const target = MOCK_MEMBERS.find(m => m.id === args[0]);
        if (target) Object.assign(target, args[1]);
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
      default:
        return null;
    }
  }
};

export const api = {
  login: (id: string, password: string) => runGas('loginMember', id, password),
  getMember: (id: string) => runGas('getMemberDetails', id),
  getHistory: (id: string) => runGas('getMemberHistory', id),
  processScan: (id: string) => runGas('processScan', id),
  getAllMembers: () => runGas('getAllMembers'),
  getStats: () => runGas('getSystemStats'),
  requestWithdrawal: (id: string, amount: number) => runGas('requestWithdrawal', id, amount),
  changePassword: (id: string, oldP: string, newP: string) => runGas('changePassword', id, oldP, newP),
  
  // Admin API
  getConfig: () => runGas('getSystemConfig'),
  updateConfig: (config: Partial<SystemConfig>) => runGas('updateSystemConfig', config),
  runCompiler: () => runGas('triggerCompiler'),
  updateMember: (id: string, updates: Partial<Member>) => runGas('adminUpdateMember', id, updates),
  postTransaction: (memberId: string, type: string, amount: number, description: string) => runGas('adminPostTransaction', memberId, type, amount, description),
};