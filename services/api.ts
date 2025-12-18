
import { Member, Status, Role, Organization, Session, JourneyItem, HardwareNode, SystemConfig, Visitor, SyncConflict, ScanResult, Transaction, AttendanceRecord } from '../types';

const VNG_VERSION = "7.2.0-PROD";
const MASTER_UPDATE_SERVER = "https://updates.vanguard-onepass.io";

// Persistence Layer
const getStorage = <T>(key: string, fallback: T): T => {
  const data = localStorage.getItem(key);
  return data ? JSON.parse(data) : fallback;
};

let MOCK_MEMBERS: Member[] = getStorage('vng_members', [
  { id: 'VG-001', organizationId: 'CAC_01', name: 'Ayodeji Vanguard', role: Role.ADMIN, status: Status.ACTIVE, photoUrl: 'https://i.pravatar.cc/150?u=1', qrUrl: '', walletBalance: 50000, outstandingFines: 0, rewardPoints: 250, sessionProgress: 85, fineDeadline: '' },
  { id: 'VG-002', organizationId: 'CAC_01', name: 'Blessing Okafor', role: Role.MEMBER, status: Status.ACTIVE, photoUrl: 'https://i.pravatar.cc/150?u=2', qrUrl: '', walletBalance: 12000, outstandingFines: 5000, rewardPoints: 45, sessionProgress: 40, fineDeadline: new Date(Date.now() + 86400000).toISOString() }
]);

let MOCK_VISITORS: Visitor[] = getStorage('vng_visitors', []);

let MOCK_CONFIG: SystemConfig = getStorage('vng_config', {
  resumptionTime: "08:30",
  lateFineAmount: 5000,
  masterUpdateUrl: MASTER_UPDATE_SERVER,
  version: VNG_VERSION
});

const persist = () => {
  localStorage.setItem('vng_members', JSON.stringify(MOCK_MEMBERS));
  localStorage.setItem('vng_visitors', JSON.stringify(MOCK_VISITORS));
  localStorage.setItem('vng_config', JSON.stringify(MOCK_CONFIG));
};

export const api = {
  // Authentication & Session
  login: async (id: string, password?: string) => {
    if (id === 'VNG_MASTER' && password === 'MASTER_PROTOCOL_2025') {
      return { success: true, member: { id: 'MASTER', name: 'Global Controller', role: Role.MASTER } as Member };
    }
    const member = MOCK_MEMBERS.find(m => m.id === id);
    if (!member) return { success: false, error: 'Identity not found in Hub.' };
    return { success: true, member };
  },

  getAllMembers: async () => MOCK_MEMBERS,

  updateMemberRole: async (memberId: string, newRole: Role) => {
    const member = MOCK_MEMBERS.find(m => m.id === memberId);
    if (member) {
      member.role = newRole;
      persist();
      return true;
    }
    return false;
  },

  updateMemberQrUrl: async (memberId: string, qrUrl: string) => {
    const member = MOCK_MEMBERS.find(m => m.id === memberId);
    if (member) {
      member.qrUrl = qrUrl;
      persist();
      return true;
    }
    return false;
  },

  getMemberAttendanceHistory: async (memberId: string): Promise<AttendanceRecord[]> => {
    // Return mock timeline data
    return [
      { timestamp: new Date(Date.now() - 86400000).toISOString(), type: 'IN', status: 'NORMAL' },
      { timestamp: new Date(Date.now() - 172800000).toISOString(), type: 'IN', status: 'LATE' },
      { timestamp: new Date(Date.now() - 259200000).toISOString(), type: 'IN', status: 'NORMAL' },
      { timestamp: new Date(Date.now() - 345600000).toISOString(), type: 'IN', status: 'NORMAL' },
      { timestamp: new Date(Date.now() - 432000000).toISOString(), type: 'IN', status: 'NORMAL' },
    ];
  },

  setOrgPaidStatus: async (id: string, isPaid: boolean) => {
    console.debug(`Setting org ${id} paid status to ${isPaid}`);
    return true;
  },

  processScan: async (id: string): Promise<ScanResult> => {
    const member = MOCK_MEMBERS.find(m => m.id === id);
    if (member) {
      const now = new Date();
      const [h, min] = MOCK_CONFIG.resumptionTime.split(':').map(Number);
      const resumption = new Date();
      resumption.setHours(h, min, 0, 0);

      let message = "Passport verified. Access Granted.";
      
      if (now > resumption && member.status !== Status.LATE) {
        member.status = Status.LATE;
        member.outstandingFines += MOCK_CONFIG.lateFineAmount;
        message = `LATE ARRIVAL. Fine of ₦${MOCK_CONFIG.lateFineAmount} auto-applied to wallet.`;
      }
      
      persist();
      return { allowed: true, member, message };
    }

    const visitor = MOCK_VISITORS.find(v => v.id === id);
    if (visitor) {
      if (visitor.status === 'Active') {
        return { allowed: true, visitor, message: "Visitor Pass Valid." };
      }
      return { allowed: false, message: "Visitor Pass Expired or Already Checked Out." };
    }

    return { allowed: false, message: "Identity Unknown. Protocol Denied." };
  },

  getAllVisitors: async () => MOCK_VISITORS,

  createVisitor: async (data: any): Promise<Visitor> => {
    const newVisitor: Visitor = {
      id: `VIS-${Math.floor(Math.random() * 10000)}`,
      name: data.name,
      hostId: data.hostId,
      hostName: data.hostName,
      status: 'Active',
      expiresAt: new Date(Date.now() + 14400000).toISOString()
    };
    MOCK_VISITORS.push(newVisitor);
    persist();
    return newVisitor;
  },

  checkoutVisitor: async (id: string) => {
    const v = MOCK_VISITORS.find(x => x.id === id);
    if (v) {
      v.status = 'CheckedOut';
      persist();
    }
  },

  getHistory: async (id: string): Promise<Transaction[]> => {
    return [
      { id: 'TX-001', type: 'Credit', amount: 10000, description: 'Wallet Initial Deposit', date: new Date().toISOString() },
      { id: 'TX-002', type: 'Debit', amount: 500, description: 'Canteen Purchase', date: new Date().toISOString() }
    ];
  },

  recordDashboardView: async (memberId: string) => {
    const m = MOCK_MEMBERS.find(x => x.id === memberId);
    if (m) {
      m.lastDashboardView = new Date().toISOString();
      persist();
      return true;
    }
    return false;
  },

  isWalletLocked: (member: Member) => {
    if (!member.lastDashboardView) return true;
    const lastView = new Date(member.lastDashboardView).getTime();
    const now = new Date().getTime();
    return (now - lastView) > 3600000;
  },

  generateHardwarePackage: (orgId: string, nodeId: string) => {
    const securityToken = btoa(`${orgId}:${nodeId}:${Date.now()}`);
    const pkg = {
      hub_id: orgId,
      node_id: nodeId,
      endpoint: MOCK_CONFIG.gasWebAppUrl,
      security_key: securityToken,
      sync_interval: 300,
      protocols: ['RFID', 'NFC', 'FACE_AUTH'],
      version: VNG_VERSION
    };
    return { pkg, downloadUrl: `data:text/json;charset=utf-8,${encodeURIComponent(JSON.stringify(pkg))}` };
  },

  syncData: async (): Promise<{ success: boolean; conflicts?: SyncConflict[] }> => {
    const sheetData = [
      { id: 'VG-001', walletBalance: 50000, outstandingFines: 0 },
      { id: 'VG-002', walletBalance: 15000, outstandingFines: 5000 }
    ];

    const conflicts: SyncConflict[] = [];
    sheetData.forEach(s => {
      const local = MOCK_MEMBERS.find(m => m.id === s.id);
      if (local && local.walletBalance !== s.walletBalance) {
        conflicts.push({
          memberId: local.id,
          name: local.name,
          field: 'Wallet Balance',
          localValue: local.walletBalance,
          sheetValue: s.walletBalance
        });
      }
    });

    if (conflicts.length > 0) return { success: true, conflicts };
    
    persist();
    return { success: true };
  },

  resolveSync: async (memberId: string, choice: 'local' | 'sheet', value: any) => {
    const m = MOCK_MEMBERS.find(x => x.id === memberId);
    if (m) {
      m.walletBalance = value;
      persist();
    }
  },

  getCurrentSession: (): Session => {
    const now = new Date();
    const month = now.getMonth();
    let type: any = 'Toni';
    if (month >= 3 && month <= 5) type = 'Medi';
    if (month >= 6 && month <= 8) type = 'Summer';
    if (month >= 9) type = 'Domi';
    
    return { id: `S-${type}-${now.getFullYear()}`, type, year: now.getFullYear(), startDate: '2025-01-01', isCurrent: true };
  },

  getMemberJourney: async (id: string): Promise<JourneyItem[]> => {
    return [
      { id: 'J1', title: 'Orientation Attendance', scope: 'Weekly', required: true, status: 'Approved', points: 10 },
      { id: 'J2', title: 'Mid-Session Assessment', scope: 'Monthly', required: true, status: 'Pending', points: 50 }
    ];
  }
};

