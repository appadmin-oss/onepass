import React, { useState, useEffect } from 'react';
import { 
  Users, AlertTriangle, TrendingUp, Search, Settings, 
  Database, RefreshCw, DollarSign, Lock, Shield, 
  CheckCircle, XCircle, Power, FileText, List, UserCheck, Key,
  Bot, Sparkles, CheckSquare, Square, Contact, Server, Calendar,
  BarChart2, Download, Link as LinkIcon, Cloud
} from 'lucide-react';
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';
import { api } from '../services/api';
import { queryAdminAnalyst } from '../services/ai';
import { Member, SystemConfig, Role, Status, AccessLog, WithdrawalRequest, Visitor, Session, JourneyItem, WeeklyReport } from '../types';

interface AdminDashboardProps {
  user: Member | null;
  setUser: (member: Member | null) => void;
}

export default function AdminDashboard({ user, setUser }: AdminDashboardProps) {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [adminId, setAdminId] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  
  const [activeTab, setActiveTab] = useState<'overview' | 'analytics' | 'members' | 'sessions' | 'hardware' | 'integration' | 'finance' | 'logs' | 'visitors'>('overview');
  const [loading, setLoading] = useState(false);
  const [syncing, setSyncing] = useState(false);

  // Data State
  const [members, setMembers] = useState<Member[]>([]);
  const [visitors, setVisitors] = useState<Visitor[]>([]);
  const [stats, setStats] = useState<any>(null);
  const [config, setConfig] = useState<SystemConfig | null>(null);
  const [logs, setLogs] = useState<AccessLog[]>([]);
  const [sessions, setSessions] = useState<Session[]>([]);
  const [reports, setReports] = useState<WeeklyReport[]>([]);
  const [withdrawals, setWithdrawals] = useState<WithdrawalRequest[]>([]);
  
  // UI State
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedMemberIds, setSelectedMemberIds] = useState<Set<string>>(new Set());

  // Config Form State
  const [gasUrl, setGasUrl] = useState('');
  const [sheetId, setSheetId] = useState('');

  // Visitor Form State
  const [showNewVisitor, setShowNewVisitor] = useState(false);
  const [visitorForm, setVisitorForm] = useState({ name: '', host: '', purpose: '' });

  useEffect(() => {
    if (user?.role === Role.ADMIN) {
      setIsAuthenticated(true);
      loadData();
    }
  }, [user]);

  const loadData = async () => {
    setLoading(true);
    try {
      const [m, s, c, sess, rep] = await Promise.all([
        api.getAllMembers(),
        api.getStats(),
        api.getConfig(),
        api.getSessions(),
        api.getWeeklyReports()
      ]);
      setMembers(m || []);
      setStats(s);
      setConfig(c);
      setSessions(sess || []);
      setReports(rep || []);
      
      if (c) {
          setGasUrl(c.gasWebAppUrl || '');
          setSheetId(c.googleSheetsId || '');
      }
      
      if (activeTab === 'logs') loadLogs();
      if (activeTab === 'finance') loadWithdrawals();
      if (activeTab === 'visitors') loadVisitors();
    } catch (e) {
      console.error("Failed to load admin data");
    } finally {
      setLoading(false);
    }
  };
  
  const loadLogs = async () => { const l = await api.getAccessLogs(); setLogs(l || []); }
  const loadWithdrawals = async () => { const w = await api.getPendingWithdrawals(); setWithdrawals(w || []); }
  const loadVisitors = async () => { const v = await api.getVisitorLogs(); setVisitors(v || []); }

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const res = await api.login(adminId, password);
      if (res.success && res.member.role === Role.ADMIN) {
        setUser(res.member);
        setIsAuthenticated(true);
        loadData();
      } else {
        setError('Access Denied.');
      }
    } catch (e) { setError('Login failed'); }
  };

  const handleSync = async () => {
      setSyncing(true);
      try {
          // Save config first
          await api.updateConfig({ gasWebAppUrl: gasUrl, googleSheetsId: sheetId });
          // Trigger sync
          const res = await api.syncWithGoogleSheets();
          if (res.success) {
              alert(res.message);
              await loadData();
          } else {
              alert("Sync Failed: " + res.message);
          }
      } catch (e) {
          alert("Sync Error");
      } finally {
          setSyncing(false);
      }
  };

  const handleConfigSave = async () => {
      await api.updateConfig({ gasWebAppUrl: gasUrl, googleSheetsId: sheetId });
      alert("Configuration Saved");
  };

  const downloadHardwareConfig = async () => {
      const conf = await api.generateHardwareConfig();
      const element = document.createElement("a");
      const file = new Blob([JSON.stringify(conf, null, 2)], {type: 'application/json'});
      element.href = URL.createObjectURL(file);
      element.download = "cacentre_hardware_config.json";
      document.body.appendChild(element);
      element.click();
      alert("Config downloaded. Load this onto your hardware node.");
  };

  const handleCreateVisitor = async (e: React.FormEvent) => {
    e.preventDefault();
    await api.createVisitor(visitorForm.name, visitorForm.host, visitorForm.purpose);
    setShowNewVisitor(false);
    setVisitorForm({ name: '', host: '', purpose: '' });
    loadVisitors();
  };

  if (!isAuthenticated) {
    return (
      <div className="max-w-md mx-auto mt-20">
        <div className="bg-slate-900 text-white p-8 rounded-xl shadow-2xl">
          <div className="text-center mb-8">
            <h1 className="text-3xl font-bold">CACENTRE <span className="text-brand-500">ADMIN</span></h1>
            <p className="text-slate-400 mt-2">Platform Control Center</p>
          </div>
          <form onSubmit={handleLogin} className="space-y-4">
            <input type="text" value={adminId} onChange={e => setAdminId(e.target.value)} className="w-full bg-slate-800 p-3 rounded text-white" placeholder="Admin ID" />
            <input type="password" value={password} onChange={e => setPassword(e.target.value)} className="w-full bg-slate-800 p-3 rounded text-white" placeholder="Password" />
            {error && <div className="text-red-400 text-sm text-center">{error}</div>}
            <button className="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold p-4 rounded mt-4">AUTHENTICATE</button>
          </form>
        </div>
      </div>
    );
  }

  // --- ANALYTICS DATA PREP ---
  const attendanceData = reports.map(r => ({
      name: `W${r.weekNumber}`,
      rate: r.attendanceRate,
      fines: r.totalFines / 1000 // In K
  }));

  const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042'];
  const statusData = [
      { name: 'Active', value: members.filter(m => m.status === Status.ACTIVE).length },
      { name: 'Late', value: members.filter(m => m.status === Status.LATE).length },
      { name: 'Suspended', value: members.filter(m => m.status === Status.SUSPENDED).length },
  ];

  return (
    <div className="flex flex-col md:flex-row min-h-[calc(100vh-100px)] gap-6 animate-fade-in relative">
        
      {/* Sidebar */}
      <div className="w-full md:w-64 flex-shrink-0">
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden sticky top-24">
          <div className="p-6 bg-slate-900 text-white">
            <h2 className="font-bold flex items-center"><Shield className="mr-2 text-brand-500"/> Platform</h2>
            <p className="text-xs text-slate-400 mt-1">Multi-Tenant: CACENTRE</p>
          </div>
          <nav className="p-2 space-y-1">
            <button onClick={() => setActiveTab('overview')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'overview' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <TrendingUp size={18} className="mr-3" /> Dashboard
            </button>
            <button onClick={() => setActiveTab('integration')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'integration' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Cloud size={18} className="mr-3" /> Integration & Sync
            </button>
            <button onClick={() => setActiveTab('analytics')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'analytics' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <BarChart2 size={18} className="mr-3" /> Analytics & Trends
            </button>
            <button onClick={() => setActiveTab('sessions')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'sessions' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Calendar size={18} className="mr-3" /> Sessions & Journey
            </button>
            <button onClick={() => setActiveTab('hardware')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'hardware' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Server size={18} className="mr-3" /> Hardware
            </button>
            <button onClick={() => setActiveTab('members')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'members' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Users size={18} className="mr-3" /> Members
            </button>
            <button onClick={() => { setActiveTab('finance'); loadWithdrawals(); }} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'finance' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <DollarSign size={18} className="mr-3" /> Wallet & Fines
            </button>
            <button onClick={() => { setActiveTab('visitors'); loadVisitors(); }} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'visitors' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Contact size={18} className="mr-3" /> Visitors
            </button>
            <button onClick={() => { setActiveTab('logs'); loadLogs(); }} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'logs' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <List size={18} className="mr-3" /> Access Logs
            </button>
          </nav>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 space-y-6">
        
        {activeTab === 'overview' && (
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="bg-slate-900 text-white p-6 rounded-xl shadow-lg">
                <div className="text-slate-400 text-sm mb-1 uppercase tracking-wider">Total Members</div>
                <div className="text-4xl font-bold">{stats?.totalMembers}</div>
            </div>
            <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div className="text-slate-500 text-sm mb-1 uppercase tracking-wider">Active Session</div>
                <div className="text-4xl font-bold text-brand-600">{sessions.find(s => s.isActive)?.name || 'None'}</div>
            </div>
             <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div className="text-slate-500 text-sm mb-1 uppercase tracking-wider">Attendance Rate</div>
                <div className="text-4xl font-bold text-green-600">{reports[0]?.attendanceRate.toFixed(1) || 0}%</div>
            </div>
          </div>
        )}

        {activeTab === 'integration' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div className="border-b border-slate-100 pb-4 mb-6">
                    <h3 className="font-bold text-lg flex items-center"><Cloud className="mr-2 text-brand-500"/> Google Integration</h3>
                    <p className="text-slate-500 text-sm">Configure Google Sheets database and Apps Script automation.</p>
                </div>

                <div className="space-y-6 max-w-2xl">
                    <div>
                        <label className="block text-sm font-bold text-slate-700 mb-1">Google Sheet ID</label>
                        <div className="flex gap-2">
                             <input type="text" value={sheetId} onChange={e => setSheetId(e.target.value)} className="flex-1 p-3 border border-slate-300 rounded-lg font-mono text-sm" placeholder="1BxiMvs..." />
                             <a href={`https://docs.google.com/spreadsheets/d/${sheetId}`} target="_blank" rel="noreferrer" className="p-3 bg-slate-100 rounded-lg text-slate-600 hover:text-brand-600">
                                <LinkIcon size={20} />
                             </a>
                        </div>
                        <p className="text-xs text-slate-400 mt-1">Found in the URL of your Google Sheet.</p>
                    </div>

                    <div>
                        <label className="block text-sm font-bold text-slate-700 mb-1">Web App URL (Apps Script)</label>
                        <input type="text" value={gasUrl} onChange={e => setGasUrl(e.target.value)} className="w-full p-3 border border-slate-300 rounded-lg font-mono text-sm" placeholder="https://script.google.com/macros/s/..." />
                        <p className="text-xs text-slate-400 mt-1">Deploy your GAS project as a Web App (Exec as Me, Access: Anyone) and paste the URL here.</p>
                    </div>

                    <div className="flex gap-4 pt-4 border-t border-slate-100">
                         <button onClick={handleConfigSave} className="px-6 py-2 border border-slate-300 rounded-lg font-bold text-slate-600 hover:bg-slate-50">Save Config</button>
                         <button onClick={handleSync} disabled={syncing} className="px-6 py-2 bg-brand-600 rounded-lg font-bold text-white hover:bg-brand-700 flex items-center">
                             <RefreshCw className={`mr-2 ${syncing ? 'animate-spin' : ''}`} size={18} />
                             {syncing ? 'Syncing...' : 'Sync Now'}
                         </button>
                    </div>

                    {config?.lastSyncTime && (
                        <div className="bg-green-50 text-green-700 p-3 rounded text-sm flex items-center">
                            <CheckCircle size={16} className="mr-2"/>
                            Last Synced: {new Date(config.lastSyncTime).toLocaleString()}
                        </div>
                    )}
                </div>
            </div>
        )}

        {activeTab === 'analytics' && (
            <div className="space-y-6">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                      <h3 className="font-bold text-lg mb-4">Weekly Attendance & Fine Trends</h3>
                      <div className="h-64">
                          <ResponsiveContainer width="100%" height="100%">
                              <LineChart data={attendanceData}>
                                  <CartesianGrid strokeDasharray="3 3" />
                                  <XAxis dataKey="name" />
                                  <YAxis yAxisId="left" />
                                  <YAxis yAxisId="right" orientation="right" />
                                  <Tooltip />
                                  <Legend />
                                  <Line yAxisId="left" type="monotone" dataKey="rate" stroke="#0ea5e9" activeDot={{ r: 8 }} name="Attendance %" />
                                  <Line yAxisId="right" type="monotone" dataKey="fines" stroke="#ef4444" name="Fines (k)" />
                              </LineChart>
                          </ResponsiveContainer>
                      </div>
                  </div>
                  <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                      <h3 className="font-bold text-lg mb-4">Member Status Distribution</h3>
                      <div className="h-64">
                          <ResponsiveContainer width="100%" height="100%">
                              <PieChart>
                                  <Pie
                                      data={statusData}
                                      cx="50%"
                                      cy="50%"
                                      innerRadius={60}
                                      outerRadius={80}
                                      paddingAngle={5}
                                      dataKey="value"
                                  >
                                      {statusData.map((entry, index) => (
                                          <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                      ))}
                                  </Pie>
                                  <Tooltip />
                                  <Legend />
                              </PieChart>
                          </ResponsiveContainer>
                      </div>
                  </div>
                </div>
            </div>
        )}

        {activeTab === 'sessions' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200">
                <div className="p-4 border-b border-slate-100">
                    <h3 className="font-bold">Session Management</h3>
                </div>
                <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    {sessions.map(s => (
                        <div key={s.id} className={`p-4 rounded-lg border-2 ${s.isActive ? 'border-brand-500 bg-brand-50' : 'border-slate-200'}`}>
                            <div className="flex justify-between items-start">
                                <div>
                                    <h4 className="font-bold text-lg">{s.name}</h4>
                                    <p className="text-sm text-slate-500">{s.startDate} - {s.endDate}</p>
                                </div>
                                {s.isActive && <span className="bg-brand-600 text-white text-xs px-2 py-1 rounded">ACTIVE</span>}
                            </div>
                            <div className="mt-4">
                                <div className="flex justify-between text-xs mb-1">
                                    <span>Week {s.weekCurrent} of {s.weekTotal}</span>
                                    <span>{Math.round((s.weekCurrent / s.weekTotal) * 100)}%</span>
                                </div>
                                <div className="h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div className="h-full bg-brand-500" style={{ width: `${(s.weekCurrent / s.weekTotal) * 100}%` }}></div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        )}

        {activeTab === 'hardware' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
                <Server size={64} className="mx-auto text-slate-300 mb-4" />
                <h3 className="text-2xl font-bold text-slate-800">Hardware Integration</h3>
                <p className="text-slate-500 max-w-md mx-auto mt-2">
                    Manage your connected attendance devices. Download the configuration file to flash onto your hardware nodes (Raspberry Pi / ESP32).
                </p>
                <div className="mt-8 flex justify-center gap-4">
                    <button onClick={downloadHardwareConfig} className="flex items-center bg-slate-900 text-white px-6 py-3 rounded-lg font-bold hover:bg-slate-800">
                        <Download className="mr-2" /> Download Config
                    </button>
                    <button className="flex items-center border border-slate-300 px-6 py-3 rounded-lg font-bold hover:bg-slate-50">
                        View Connected Devices
                    </button>
                </div>
            </div>
        )}

        {activeTab === 'members' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                 <h3 className="font-bold mb-4">Member Management</h3>
                 <div className="mb-4 flex gap-2">
                    <input 
                      type="text" 
                      placeholder="Search by ID or Name..." 
                      className="p-2 border rounded w-full max-w-sm" 
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                    />
                 </div>
                 <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50 text-slate-500">
                            <tr>
                                <th className="p-3">ID</th>
                                <th className="p-3">Name</th>
                                <th className="p-3">Role</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Wallet</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {members.filter(m => 
                              m.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
                              m.id.toLowerCase().includes(searchTerm.toLowerCase())
                            ).map(m => (
                                <tr key={m.id}>
                                    <td className="p-3 font-mono">{m.id}</td>
                                    <td className="p-3 font-medium">{m.name}</td>
                                    <td className="p-3">{m.role}</td>
                                    <td className="p-3">
                                        <span className={`px-2 py-1 rounded-full text-xs font-bold ${
                                            m.status === Status.ACTIVE ? 'bg-green-100 text-green-700' : 
                                            m.status === Status.LATE ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'
                                        }`}>
                                            {m.status}
                                        </span>
                                    </td>
                                    <td className="p-3">₦{m.walletBalance.toLocaleString()}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                 </div>
            </div>
        )}

        {activeTab === 'finance' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 className="font-bold mb-4">Pending Withdrawals</h3>
                {withdrawals.length === 0 ? (
                    <div className="text-center p-8 text-slate-500 bg-slate-50 rounded-lg">No pending requests</div>
                ) : (
                    <div className="space-y-4">
                        {withdrawals.map(w => (
                            <div key={w.id} className="flex items-center justify-between border border-slate-200 p-4 rounded-lg hover:bg-slate-50 transition">
                                <div>
                                    <p className="font-bold text-slate-900">{w.memberName} <span className="text-slate-400 font-normal">({w.memberId})</span></p>
                                    <p className="text-sm text-slate-500">₦{w.amount.toLocaleString()} • {new Date(w.timestamp).toLocaleDateString()}</p>
                                </div>
                                <div className="flex gap-2">
                                    <button onClick={async () => { await api.processWithdrawal(w.id, 'Approved'); loadWithdrawals(); }} className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">Approve</button>
                                    <button onClick={async () => { await api.processWithdrawal(w.id, 'Rejected'); loadWithdrawals(); }} className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition">Reject</button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        )}

        {activeTab === 'visitors' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                 <div className="flex justify-between items-center mb-6">
                    <h3 className="font-bold text-lg">Visitor Management</h3>
                    <button onClick={() => setShowNewVisitor(true)} className="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center transition">
                        <UserCheck size={16} className="mr-2"/> New Visitor Pass
                    </button>
                 </div>

                 {showNewVisitor && (
                     <div className="bg-slate-50 p-4 rounded-lg mb-6 border border-slate-200 animate-fade-in">
                         <h4 className="font-bold text-sm text-slate-700 mb-3">Create New Pass</h4>
                         <form onSubmit={handleCreateVisitor} className="grid grid-cols-1 md:grid-cols-4 gap-4">
                             <input className="p-2 border rounded" placeholder="Visitor Name" required value={visitorForm.name} onChange={e => setVisitorForm({...visitorForm, name: e.target.value})} />
                             <input className="p-2 border rounded" placeholder="Host Name (Member)" required value={visitorForm.host} onChange={e => setVisitorForm({...visitorForm, host: e.target.value})} />
                             <input className="p-2 border rounded" placeholder="Purpose" required value={visitorForm.purpose} onChange={e => setVisitorForm({...visitorForm, purpose: e.target.value})} />
                             <button className="bg-slate-900 text-white rounded font-bold">Generate Pass</button>
                         </form>
                         <button onClick={() => setShowNewVisitor(false)} className="text-xs text-red-500 mt-2 underline">Cancel</button>
                     </div>
                 )}

                 <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50 text-slate-500">
                            <tr>
                                <th className="p-3">Name</th>
                                <th className="p-3">Host</th>
                                <th className="p-3">Purpose</th>
                                <th className="p-3">Check In</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {visitors.map(v => (
                                <tr key={v.id} className="hover:bg-slate-50">
                                    <td className="p-3 font-medium">{v.name}</td>
                                    <td className="p-3">{v.hostName}</td>
                                    <td className="p-3">{v.purpose}</td>
                                    <td className="p-3 text-slate-500">{new Date(v.checkInTime).toLocaleString()}</td>
                                    <td className="p-3">
                                        <span className={`px-2 py-1 rounded-full text-xs font-bold ${v.status === 'Checked In' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`}>
                                            {v.status}
                                        </span>
                                    </td>
                                    <td className="p-3">
                                        {v.status === 'Checked In' && (
                                            <button onClick={async () => { await api.checkoutVisitor(v.id); loadVisitors(); }} className="text-red-600 hover:text-red-800 font-bold text-xs border border-red-200 px-2 py-1 rounded hover:bg-red-50 transition">Check Out</button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                 </div>
            </div>
        )}

        {activeTab === 'logs' && (
             <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div className="flex items-center justify-between mb-4">
                    <h3 className="font-bold text-lg">System Access Logs</h3>
                    <button onClick={loadLogs} className="text-brand-600 hover:text-brand-800"><RefreshCw size={18}/></button>
                </div>
                <div className="overflow-x-auto h-[600px] overflow-y-scroll border rounded-lg">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50 sticky top-0 shadow-sm">
                            <tr>
                                <th className="p-3 text-slate-500 font-medium">Timestamp</th>
                                <th className="p-3 text-slate-500 font-medium">User ID</th>
                                <th className="p-3 text-slate-500 font-medium">Action</th>
                                <th className="p-3 text-slate-500 font-medium">Status</th>
                                <th className="p-3 text-slate-500 font-medium">Device / Notes</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {logs.map(l => (
                                <tr key={l.id} className="hover:bg-slate-50">
                                    <td className="p-3 text-slate-500 whitespace-nowrap">{new Date(l.timestamp).toLocaleString()}</td>
                                    <td className="p-3 font-mono text-slate-700">{l.memberId}</td>
                                    <td className="p-3 font-medium">{l.action}</td>
                                    <td className="p-3"><span className={`px-2 py-0.5 rounded text-xs font-bold ${l.status === 'GRANTED' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>{l.status}</span></td>
                                    <td className="p-3 text-slate-500 text-xs">{l.notes || l.deviceId}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
             </div>
        )}

      </div>
    </div>
  );
}
