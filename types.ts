export enum Role {
  ADMIN = 'Admin',
  MEMBER = 'Member',
  STAFF = 'Staff',
  GUEST = 'Guest'
}

export enum Status {
  ACTIVE = 'Active',
  LATE = 'Late',
  SUSPENDED = 'Suspended',
  BLOCKED = 'Blocked'
}

export interface Member {
  id: string;
  name: string;
  role: Role;
  status: Status;
  photoUrl: string;
  walletBalance: number;
  outstandingFines: number;
  rewardPoints: number;
}

export interface Transaction {
  id: string;
  timestamp: string;
  memberId: string;
  type: 'Credit' | 'Debit' | 'Fine' | 'Award';
  amount: number;
  description: string;
  reference?: string;
}

export interface AccessLog {
  timestamp: string;
  memberId: string;
  action: string;
  status: string;
  notes?: string;
}

export interface ScanResult {
  allowed: boolean;
  member: Partial<Member>;
  message?: string;
}

export interface ChatMessage {
  id: string;
  role: 'user' | 'model';
  text: string;
  timestamp: Date;
}

export interface SystemConfig {
  resumptionTime: string;
  lateFineAmount: number;
  autoSuspendThreshold: number;
  maintenanceMode: boolean;
}