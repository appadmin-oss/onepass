
export enum Role {
  ADMIN = 'Admin',
  MEMBER = 'Member',
  STAFF = 'Staff',
  GUEST = 'Guest',
  MASTER = 'MasterAdmin'
}

export enum Status {
  ACTIVE = 'Active',
  LATE = 'Late',
  SUSPENDED = 'Suspended',
  BLOCKED = 'Blocked',
  LOCKED = 'Locked' // Financial lock
}

export type SessionType = 'Toni' | 'Medi' | 'Summer' | 'Domi';

export interface Session {
  id: string;
  type: SessionType;
  year: number;
  startDate: string;
  isCurrent: boolean;
}

export interface JourneyItem {
  id: string;
  title: string;
  scope: 'Weekly' | 'Monthly' | 'Yearly';
  required: boolean;
  status: 'Pending' | 'Approved' | 'Rejected';
  points: number;
}

export interface Organization {
  id: string;
  name: string;
  type: 'CACENTRE' | 'GENERAL';
  isPaid: boolean;
  settings: {
    walletEnabled: boolean;
    journeysEnabled: boolean;
    customHeaders?: Record<string, string>;
  };
}

export interface Member {
  id: string;
  organizationId: string;
  name: string;
  role: Role;
  status: Status;
  photoUrl: string;
  qrUrl?: string; // Link for custom QR code
  walletBalance: number;
  outstandingFines: number;
  rewardPoints: number;
  lastDashboardView?: string; 
  fineDeadline?: string;
  sessionProgress: number; 
}

export interface AttendanceRecord {
  timestamp: string;
  type: 'IN' | 'OUT';
  status: 'NORMAL' | 'LATE';
}

export interface HardwareNode {
  id: string;
  name: string;
  type: 'RFID' | 'Camera' | 'NFC';
  status: 'Online' | 'Offline';
  lastPing: string;
  securityKey: string;
}

export interface Visitor {
  id: string;
  name: string;
  hostId: string;
  hostName?: string;
  status: 'Active' | 'Expired' | 'CheckedOut';
  expiresAt: string;
}

export interface SystemConfig {
  resumptionTime: string;
  lateFineAmount: number;
  gasWebAppUrl?: string;
  publicSheetCsvUrl?: string;
  masterUpdateUrl: string;
  version: string;
}

export interface SyncConflict {
  memberId: string;
  name: string;
  field: string;
  localValue: any;
  sheetValue: any;
}

export interface Transaction {
  id: string;
  type: 'Credit' | 'Debit';
  amount: number;
  description: string;
  date: string;
}

export interface ScanResult {
  allowed: boolean;
  message: string;
  member?: Member;
  visitor?: Visitor;
}

export interface ChatMessage {
  id: string;
  role: 'user' | 'model';
  text: string;
  timestamp: Date;
}
