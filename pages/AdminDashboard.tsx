import React, { useState, useEffect } from 'react';
import { 
  Users, AlertTriangle, TrendingUp, Search, Settings, 
  Database, RefreshCw, DollarSign, Lock, Shield, 
  CheckCircle, XCircle, Power, FileText, List, UserCheck, Key,
  Bot, Sparkles, CheckSquare, Square, Contact, Server, Calendar,
  BarChart2, Download, Link as LinkIcon, Cloud, Upload, Trash2, Edit, CreditCard, Activity
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
  
  // Member Management State
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedMemberIds, setSelectedMemberIds] = useState<Set<string>>(new Set());
  const [editingMember, setEditingMember] = useState<Member | null>(null);

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
      
      // Lazy load these when tab is clicked usually, but we load here for "Full code" simplicity
      loadLogs();
      loadWithdrawals();
      loadVisitors();
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

  // --- MEMBER BULK ACTIONS ---
  
  const toggleSelectMember = (id: string) => {
      const newSet = new Set(selectedMemberIds);
      if (newSet.has(id)) newSet.delete(id);
      else newSet.add(id);
      setSelectedMemberIds(newSet);
  };

  const toggleSelectAll = () => {
      if (selectedMemberIds.size === members.length) {
          setSelectedMemberIds(new Set());
      } else {
          setSelectedMemberIds(new Set(members.map(m => m.id)));
      }
  };

  const handleBulkAction = async (action: string) => {
      if (!window.confirm(`Are you sure you want to ${action} ${selectedMemberIds.size} members?`)) return;
      
      const ids = Array.from(selectedMemberIds);
      let updates = {};
      
      if (action === 'block') updates = { status: Status.BLOCKED };
      if (action === 'activate') updates = { status: Status.ACTIVE };
      if (action === 'suspend') updates = { status: Status.SUSPENDED };
      if (action === 'make_admin') updates = { role: Role.ADMIN };
      
      await api.bulkUpdateMembers(ids, updates);
      setSelectedMemberIds(new Set());
      loadData();
      alert("Bulk action completed.");
  };

  const handlePhotoUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
      if (!editingMember || !e.target.files?.[0]) return;
      
      const file = e.target.files[0];
      const reader = new FileReader();
      reader.onloadend = async () => {
          const base64 = reader.result as string;
          await api.updateMember(editingMember.id, { photoUrl: base64 });
          // Refresh
          const updated = { ...editingMember, photoUrl: base64 };
          setEditingMember(updated);
          setMembers(prev => prev.map(m => m.id === updated.id ? updated : m));
          alert("Photo updated and synced.");
      };
      reader.readAsDataURL(file);
  };

  // --- FINANCE ACTIONS ---
  const handleApproveWithdrawal = async (id: string) => {
      await api.processWithdrawal(id, 'Approved');
      loadWithdrawals();
      alert("Withdrawal Approved");
  };

  // --- HARDWARE CONFIG ---

  const downloadHardwareConfig = async () => {
      const conf = await api.generateHardwareConfig();
      const element = document.createElement("a");
      const file = new Blob([JSON.stringify(conf, null, 2)], {type: 'application/json'});
      element.href = URL.createObjectURL(file);
      element.download = "onepass_device_config.json";
      document.body.appendChild(element);
      element.click();
      alert("Config downloaded. Load this onto your hardware node.");
  };

  // --- VISITOR ---

  const handleCreateVisitor = async (e: React.FormEvent) => {
    e.preventDefault();
    await api.createVisitor(visitorForm.name, visitorForm.host, visitorForm.purpose);
    setShowNewVisitor(false);
    setVisitorForm({ name: '', host: '', purpose: '' });
    loadVisitors();
  };
  
  const handleSync = async () => {
      setSyncing(true);
      try {
          await api.updateConfig({ gasWebAppUrl: gasUrl, googleSheetsId: sheetId });
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

  if (!isAuthenticated) {
    return (
      <div className="max-w-md mx-auto mt-20">
        <div className="bg-slate-900 text-white p-8 rounded-xl shadow-2xl">
          <div className="text-center mb-8">
            <h1 className="text-3xl font-bold">ONEPASS <span className="text-brand-500">ADMIN</span></h1>
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

  // --- RENDER HELPERS ---
  const totalWalletValue = members.reduce((sum, m) => sum + (m.walletBalance || 0), 0);
  const totalOutstandingFines = members.reduce((sum, m) => sum + (m.outstandingFines || 0), 0);

  return (
    <div className="flex flex-col md:flex-row min-h-[calc(100vh-100px)] gap-6 animate-fade-in relative">
        
      {/* Sidebar */}
      <div className="w-full md:w-64 flex-shrink-0">
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden sticky top-24">
          <div className="p-6 bg-slate-900 text-white">
            <h2 className="font-bold flex items-center"><Shield className="mr-2 text-brand-500"/> Platform</h2>
            <p className="text-xs text-slate-400 mt-1">Multi-Tenant: ONEPASS</p>
          </div>
          <nav className="p-2 space-y-1">
            <button onClick={() => setActiveTab('overview')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'overview' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <TrendingUp size={18} className="mr-3" /> Dashboard
            </button>
             <button onClick={() => setActiveTab('logs')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'logs' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Activity size={18} className="mr-3" /> Access Logs
            </button>
            <button onClick={() => setActiveTab('members')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'members' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Users size={18} className="mr-3" /> Members
            </button>
            <button onClick={() => { setActiveTab('finance'); loadWithdrawals(); }} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'finance' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <DollarSign size={18} className="mr-3" /> Wallet & Fines
            </button>
            <button onClick={() => setActiveTab('hardware')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'hardware' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Server size={18} className="mr-3" /> Hardware
            </button>
            <button onClick={() => setActiveTab('integration')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium ${activeTab === 'integration' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Cloud size={18} className="mr-3" /> Integration
            </button>
          </nav>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 space-y-6">
        
        {activeTab === 'overview' && (
          <div className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div className="bg-slate-900 text-white p-6 rounded-xl shadow-lg">
                  <div className="text-slate-400 text-sm mb-1 uppercase tracking-wider">Total Members</div>
                  <div className="text-3xl font-bold">{stats?.totalMembers}</div>
              </div>
              <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                  <div className="text-slate-500 text-sm mb-1 uppercase tracking-wider">Attendance Rate</div>
                  <div className="text-3xl font-bold text-brand-600">{reports[0]?.attendanceRate.toFixed(1) || 0}%</div>
              </div>
               <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                  <div className="text-slate-500 text-sm mb-1 uppercase tracking-wider">System Wallet</div>
                  <div className="text-3xl font-bold text-green-600">₦{(totalWalletValue/1000).toFixed(1)}k</div>
              </div>
              <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                  <div className="text-slate-500 text-sm mb-1 uppercase tracking-wider">Pending Fines</div>
                  <div className="text-3xl font-bold text-red-600">₦{(totalOutstandingFines/1000).toFixed(1)}k</div>
              </div>
            </div>

            {/* Recent Activity Feed */}
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
               <h3 className="font-bold mb-4 text-slate-800 flex items-center"><Activity className="mr-2" size={20} /> Live Activity Feed</h3>
               {logs.length === 0 ? (
                 <p className="text-slate-500 italic">No recent logs.</p>
               ) : (
                 <div className="space-y-3">
                   {logs.slice(0, 5).map(log => (
                     <div key={log.id} className="flex items-center justify-between text-sm p-2 hover:bg-slate-50 rounded">
                       <div className="flex items-center">
                          <span className={`w-2 h-2 rounded-full mr-3 ${log.status === 'GRANTED' ? 'bg-green-500' : 'bg-red-500'}`}></span>
                          <span className="font-mono text-slate-500 mr-2">{log.memberId}</span>
                          <span className="font-medium text-slate-700">{log.action}</span>
                       </div>
                       <span className="text-slate-400">{new Date(log.timestamp).toLocaleTimeString()}</span>
                     </div>
                   ))}
                 </div>
               )}
            </div>
          </div>
        )}

        {activeTab === 'hardware' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
                <Server size={64} className="mx-auto text-slate-300 mb-4" />
                <h3 className="text-2xl font-bold text-slate-800">Hardware Integration</h3>
                <p className="text-slate-500 max-w-md mx-auto mt-2 mb-8">
                    Generate configuration packages for your Raspberry Pi or ESP32 access nodes.
                </p>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto text-left">
                    <div className="border border-slate-200 rounded-lg p-6 hover:shadow-md transition">
                         <h4 className="font-bold flex items-center mb-2"><Download className="mr-2 text-brand-600"/> Device Config</h4>
                         <p className="text-sm text-slate-500 mb-4">Includes API endpoints, Org ID, and sync intervals.</p>
                         <button onClick={downloadHardwareConfig} className="w-full bg-slate-900 text-white py-2 rounded font-bold hover:bg-slate-800">Download .JSON</button>
                    </div>
                    <div className="border border-slate-200 rounded-lg p-6 hover:shadow-md transition">
                         <h4 className="font-bold flex items-center mb-2"><CheckCircle className="mr-2 text-green-600"/> Connected Devices</h4>
                         <ul className="text-sm space-y-2 mt-2">
                             <li className="flex justify-between"><span>Main Gate</span> <span className="text-green-600 font-mono text-xs">ONLINE</span></li>
                             <li className="flex justify-between"><span>Reception Kiosk</span> <span className="text-green-600 font-mono text-xs">ONLINE</span></li>
                             <li className="flex justify-between"><span>Back Door</span> <span className="text-red-400 font-mono text-xs">OFFLINE</span></li>
                         </ul>
                    </div>
                </div>
            </div>
        )}

        {activeTab === 'members' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6 relative">
                 <h3 className="font-bold mb-4">Member Management</h3>
                 
                 <div className="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                    <input 
                      type="text" 
                      placeholder="Search by ID or Name..." 
                      className="p-2 border rounded w-full max-w-sm" 
                      value={searchTerm}
                      onChange={(e) => setSearchTerm(e.target.value)}
                    />
                    
                    {/* BULK ACTIONS TOOLBAR */}
                    {selectedMemberIds.size > 0 && (
                        <div className="flex items-center gap-2 bg-slate-900 text-white px-4 py-2 rounded-lg animate-fade-in shadow-lg">
                            <span className="text-xs font-bold mr-2">{selectedMemberIds.size} Selected</span>
                            <button onClick={() => handleBulkAction('activate')} className="p-1 hover:bg-slate-700 rounded" title="Activate"><CheckCircle size={16}/></button>
                            <button onClick={() => handleBulkAction('block')} className="p-1 hover:bg-slate-700 rounded" title="Block"><XCircle size={16}/></button>
                            <button onClick={() => handleBulkAction('suspend')} className="p-1 hover:bg-slate-700 rounded" title="Suspend"><AlertTriangle size={16}/></button>
                        </div>
                    )}
                 </div>

                 {/* MEMBER TABLE */}
                 <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50 text-slate-500">
                            <tr>
                                <th className="p-3 w-8">
                                    <input type="checkbox" checked={selectedMemberIds.size === members.length && members.length > 0} onChange={toggleSelectAll} />
                                </th>
                                <th className="p-3">ID</th>
                                <th className="p-3">Name</th>
                                <th className="p-3">Role</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Wallet</th>
                                <th className="p-3">Points</th>
                                <th className="p-3">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {members.filter(m => 
                              m.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
                              m.id.toLowerCase().includes(searchTerm.toLowerCase())
                            ).map(m => (
                                <tr key={m.id} className={selectedMemberIds.has(m.id) ? 'bg-blue-50' : ''}>
                                    <td className="p-3">
                                        <input type="checkbox" checked={selectedMemberIds.has(m.id)} onChange={() => toggleSelectMember(m.id)} />
                                    </td>
                                    <td className="p-3 font-mono">{m.id}</td>
                                    <td className="p-3 font-medium flex items-center">
                                        <img src={m.photoUrl || 'https://via.placeholder.com/30'} className="w-6 h-6 rounded-full mr-2 object-cover"/>
                                        {m.name}
                                    </td>
                                    <td className="p-3">{m.role}</td>
                                    <td className="p-3">
                                        <span className={`px-2 py-1 rounded-full text-xs font-bold ${
                                            m.status === Status.ACTIVE ? 'bg-green-100 text-green-700' : 
                                            m.status === Status.LATE ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'
                                        }`}>
                                            {m.status}
                                        </span>
                                    </td>
                                    <td className="p-3 font-mono">₦{m.walletBalance.toLocaleString()}</td>
                                    <td className="p-3 text-brand-600 font-bold">{m.rewardPoints}</td>
                                    <td className="p-3">
                                        <button onClick={() => setEditingMember(m)} className="text-brand-600 hover:underline">Edit</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                 </div>

                 {/* EDIT MEMBER MODAL (PHOTO UPLOAD) */}
                 {editingMember && (
                     <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                         <div className="bg-white rounded-xl p-6 w-full max-w-md">
                             <h3 className="font-bold text-lg mb-4">Edit Member: {editingMember.name}</h3>
                             <div className="flex flex-col items-center mb-6">
                                 <img src={editingMember.photoUrl || 'https://via.placeholder.com/100'} className="w-24 h-24 rounded-full object-cover mb-4 border-2 border-slate-200" />
                                 <label className="cursor-pointer bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-lg text-sm font-bold flex items-center">
                                     <Upload size={16} className="mr-2"/> Upload Photo
                                     <input type="file" className="hidden" accept="image/*" onChange={handlePhotoUpload} />
                                 </label>
                                 <p className="text-xs text-slate-400 mt-2">Auto-syncs to Database</p>
                             </div>
                             <div className="flex justify-end">
                                 <button onClick={() => setEditingMember(null)} className="px-4 py-2 bg-slate-200 rounded-lg font-bold mr-2">Close</button>
                             </div>
                         </div>
                     </div>
                 )}
            </div>
        )}

        {activeTab === 'logs' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                 <h3 className="font-bold mb-4">Access Logs</h3>
                 <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50 text-slate-500">
                            <tr>
                                <th className="p-3">Time</th>
                                <th className="p-3">Member ID</th>
                                <th className="p-3">Action</th>
                                <th className="p-3">Status</th>
                                <th className="p-3">Notes</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {logs.length === 0 && <tr><td colSpan={5} className="p-4 text-center text-slate-400">No logs available</td></tr>}
                            {logs.map((log) => (
                                <tr key={log.id}>
                                    <td className="p-3">{new Date(log.timestamp).toLocaleString()}</td>
                                    <td className="p-3 font-mono">{log.memberId}</td>
                                    <td className="p-3">{log.action}</td>
                                    <td className="p-3">
                                       <span className={`px-2 py-1 rounded text-xs font-bold ${log.status === 'GRANTED' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                          {log.status}
                                       </span>
                                    </td>
                                    <td className="p-3 text-slate-500">{log.notes || '-'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                 </div>
            </div>
        )}

        {activeTab === 'finance' && (
            <div className="space-y-6">
                <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 className="font-bold mb-4">Pending Withdrawals</h3>
                    {withdrawals.length === 0 ? (
                        <p className="text-slate-500 italic">No pending requests.</p>
                    ) : (
                        <div className="space-y-2">
                            {withdrawals.map(w => (
                                <div key={w.id} className="flex items-center justify-between p-3 border border-slate-100 rounded-lg">
                                    <div>
                                        <p className="font-bold text-slate-800">{w.memberName} ({w.memberId})</p>
                                        <p className="text-sm text-slate-500">Requested: {new Date(w.timestamp).toLocaleDateString()}</p>
                                    </div>
                                    <div className="flex items-center gap-4">
                                        <span className="font-bold text-lg">₦{w.amount.toLocaleString()}</span>
                                        <button 
                                          onClick={() => handleApproveWithdrawal(w.id)}
                                          className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                                        >
                                            Approve
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        )}

        {activeTab === 'integration' && (
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 className="font-bold mb-4">Google Integration</h3>
                <div className="space-y-4 max-w-md">
                    <input type="text" value={sheetId} onChange={e => setSheetId(e.target.value)} className="w-full p-2 border rounded" placeholder="Google Sheet ID" />
                    <input type="text" value={gasUrl} onChange={e => setGasUrl(e.target.value)} className="w-full p-2 border rounded" placeholder="Web App URL" />
                    <button onClick={handleSync} className="w-full bg-brand-600 text-white p-2 rounded font-bold flex justify-center items-center">
                         <RefreshCw className={`mr-2 ${syncing ? 'animate-spin' : ''}`} size={16} /> Sync
                    </button>
                </div>
            </div>
        )}

      </div>
    </div>
  );
}
