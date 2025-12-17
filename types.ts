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

export type OrganizationType = 'CACENTRE' | 'GENERAL';

export interface Organization {
  id: string;
  name: string;
  type: OrganizationType;
  logoUrl?: string;
}

export interface Member {
  id: string;
  organizationId: string;
  name: string;
  role: Role;
  status: Status;
  photoUrl: string;
  walletBalance: number;
  outstandingFines: number;
  rewardPoints: number;
  password?: string;
  lastDashboardView?: string; // For Wallet Lock logic
}

export interface Visitor {
  id: string;
  organizationId: string;
  name: string;
  hostName: string;
  purpose: string;
  checkInTime: string;
  checkOutTime?: string;
  expiresAt: string;
  status: 'Checked In' | 'Checked Out' | 'Expired';
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
  id: string;
  timestamp: string;
  memberId: string;
  action: string;
  status: string;
  notes?: string;
  deviceId?: string;
}

export interface ScanResult {
  allowed: boolean;
  member: Partial<Member> | Visitor;
  message?: string;
  isVisitor?: boolean;
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
  currentSessionId?: string;
}

export interface WithdrawalRequest {
  id: string;
  memberId: string;
  memberName: string;
  amount: number;
  timestamp: string;
  status: 'Pending' | 'Approved' | 'Rejected';
}

// --- CACENTRE SPECIFIC TYPES ---

export type SessionName = 'Toni' | 'Medi' | 'Summer' | 'Domi';

export interface Session {
  id: string;
  name: SessionName;
  startDate: string;
  endDate: string;
  isActive: boolean;
  weekCurrent: number;
  weekTotal: number;
}

export interface JourneyItem {
  id: string;
  sessionId: string;
  title: string;
  description: string;
  category: 'Attendance' | 'Participation' | 'Development';
  required: boolean;
  status: 'Pending' | 'In Progress' | 'Completed';
  progress: number; // 0-100
}

export interface DeviceEvent {
  device_id: string;
  organization_id: string;
  actor_type: 'MEMBER' | 'VISITOR';
  actor_id: string;
  event_type: 'ENTRY' | 'EXIT' | 'ATTENDANCE';
  timestamp: string;
  metadata?: any;
}

export interface WeeklyReport {
  id: string;
  weekNumber: number;
  year: number;
  session: SessionName;
  attendanceRate: number;
  totalFines: number;
  generatedAt: string;
  url: string; // link to PDF/CSV
}
