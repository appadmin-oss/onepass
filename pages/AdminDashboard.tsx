
import React, { useState, useEffect } from 'react';
import { 
  Users, Shield, LayoutDashboard, Settings, Cpu, Wifi, Activity, 
  Search, RefreshCw, Server, Download, Cloud, Clock, AlertTriangle, 
  ShieldCheck, LogOut, QrCode, TrendingUp, PieChart as PieIcon, BarChart3, Layers,
  FileDown, X, Edit2, Check, BookOpen, ExternalLink, Link as LinkIcon
} from 'lucide-react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar } from 'recharts';
import { api } from '../services/api';
import { Member, Status, SystemConfig, HardwareNode, SyncConflict, Role, AttendanceRecord, JourneyItem } from '../types';

const COLORS = ['#0ea5e9', '#6366f1', '#f59e0b', '#ef4444'];

interface MemberModalProps {
  member: Member;
  onClose: () => void;
  onUpdate: () => void;
}

const MemberDetailModal = ({ member, onClose, onUpdate }: MemberModalProps) => {
  const [attendance, setAttendance] = useState<AttendanceRecord[]>([]);
  const [journey, setJourney] = useState<JourneyItem[]>([]);
  const [qrLink, setQrLink] = useState(member.qrUrl || '');
  const [isUpdatingQr, setIsUpdatingQr] = useState(false);

  useEffect(() => {
    api.getMemberAttendanceHistory(member.id).then(setAttendance);
    api.getMemberJourney(member.id).then(setJourney);
  }, [member]);

  const handleUpdateQr = async () => {
    setIsUpdatingQr(true);
    await api.updateMemberQrUrl(member.id, qrLink);
    setIsUpdatingQr(false);
    onUpdate();
  };

  const chartData = attendance.map(a => ({
    day: new Date(a.timestamp).toLocaleDateString(undefined, { weekday: 'short' }),
    val: a.status === 'NORMAL' ? 100 : 50
  })).reverse();

  return (
    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
      <div className="bg-white rounded-[3rem] w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl animate-scale-in flex flex-col">
        <div className="bg-slate-900 p-8 text-white flex justify-between items-center shrink-0">
          <div className="flex items-center gap-6">
            <img src={member.photoUrl} className="w-16 h-16 rounded-2xl border-2 border-slate-700 object-cover" />
            <div>
              <h3 className="text-2xl font-black tracking-tight">{member.name}</h3>
              <p className="text-brand-400 font-black text-xs uppercase tracking-widest">{member.id} • {member.role}</p>
            </div>
          </div>
          <button onClick={onClose} className="p-3 bg-slate-800 rounded-2xl hover:bg-slate-700 transition-colors">
            <X size={24} />
          </button>
        </div>

        <div className="flex-1 overflow-y-auto p-8 space-y-10">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
            {/* Analytics Section */}
            <div className="space-y-6">
              <h4 className="text-sm font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                <BarChart3 size={16} /> Attendance Analytics
              </h4>
              <div className="h-48 w-full bg-slate-50 rounded-3xl p-4 border border-slate-100">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={chartData}>
                    <Bar dataKey="val" fill="#0ea5e9" radius={[4, 4, 0, 0]} />
                    <XAxis dataKey="day" axisLine={false} tickLine={false} tick={{fontSize: 10, fontWeight: 700}} />
                    <Tooltip contentStyle={{borderRadius: '12px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)'}} cursor={{fill: 'transparent'}} />
                  </BarChart>
                </ResponsiveContainer>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                  <p className="text-[10px] font-black text-slate-400 uppercase mb-1">Session Progress</p>
                  <p className="text-xl font-black text-slate-900">{member.sessionProgress}%</p>
                </div>
                <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                  <p className="text-[10px] font-black text-slate-400 uppercase mb-1">Reward Points</p>
                  <p className="text-xl font-black text-brand-600">{member.rewardPoints} PTS</p>
                </div>
              </div>
            </div>

            {/* Timeline & Actions Section */}
            <div className="space-y-6">
              <h4 className="text-sm font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                <Clock size={16} /> Recent Timeline
              </h4>
              <div className="space-y-3">
                {attendance.slice(0, 4).map((a, i) => (
                  <div key={i} className="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <div className="flex items-center gap-3">
                      <div className={`w-2 h-2 rounded-full ${a.status === 'NORMAL' ? 'bg-green-500' : 'bg-red-500'}`} />
                      <p className="text-sm font-bold text-slate-700">{a.type === 'IN' ? 'Check-In' : 'Check-Out'}</p>
                    </div>
                    <p className="text-[10px] font-black text-slate-400 uppercase">
                      {new Date(a.timestamp).toLocaleString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                    </p>
                  </div>
                ))}
              </div>

              <div className="pt-4 border-t border-slate-100 space-y-4">
                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block">QR Code URL (Link Upload)</label>
                <div className="flex gap-2">
                  <div className="relative flex-1">
                    <LinkIcon className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={16} />
                    <input 
                      type="text" 
                      placeholder="Paste QR Image Link..." 
                      value={qrLink}
                      onChange={e => setQrLink(e.target.value)}
                      className="w-full pl-10 pr-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl font-bold text-sm outline-none focus:border-brand-500 transition-all"
                    />
                  </div>
                  <button 
                    onClick={handleUpdateQr}
                    disabled={isUpdatingQr}
                    className="p-3 bg-slate-900 text-white rounded-xl hover:bg-brand-600 transition-all"
                  >
                    {isUpdatingQr ? <RefreshCw className="animate-spin" size={20} /> : <Check size={20} />}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div className="space-y-6">
             <h4 className="text-sm font-black uppercase tracking-[0.2em] text-slate-400 flex items-center gap-2">
                <Layers size={16} /> Journey Milestones
             </h4>
             <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {journey.map(j => (
                  <div key={j.id} className="flex items-center justify-between p-5 bg-slate-50 rounded-3xl border border-slate-100">
                    <div className="flex items-center gap-4">
                      <div className={`p-2 rounded-xl ${j.status === 'Approved' ? 'bg-green-100 text-green-600' : 'bg-slate-200 text-slate-400'}`}>
                        <Check size={16} />
                      </div>
                      <p className="font-bold text-slate-800 text-sm">{j.title}</p>
                    </div>
                    <span className={`text-[10px] font-black px-2 py-1 rounded-lg ${j.status === 'Approved' ? 'bg-green-100 text-green-600' : 'bg-slate-200 text-slate-400'}`}>
                      {j.status}
                    </span>
                  </div>
                ))}
             </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default function AdminDashboard({ user, setUser }: { user: any; setUser: any }) {
  const [activeTab, setActiveTab] = useState<'overview' | 'members' | 'hardware' | 'sync' | 'guide'>('overview');
  const [members, setMembers] = useState<Member[]>([]);
  const [conflicts, setConflicts] = useState<SyncConflict[]>([]);
  const [syncing, setSyncing] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedMember, setSelectedMember] = useState<Member | null>(null);
  const [editingRoleId, setEditingRoleId] = useState<string | null>(null);

  useEffect(() => { loadData(); }, []);

  const loadData = async () => {
    const m = await api.getAllMembers();
    setMembers(m);
  };

  const handleSync = async () => {
    setSyncing(true);
    const res = await api.syncData();
    if (res.conflicts) setConflicts(res.conflicts);
    else loadData();
    setSyncing(false);
  };

  const handleRoleChange = async (memberId: string, newRole: Role) => {
    const success = await api.updateMemberRole(memberId, newRole);
    if (success) {
      loadData();
      setEditingRoleId(null);
    }
  };

  const exportCsv = () => {
    const headers = ['ID', 'Name', 'Role', 'Status', 'Wallet Balance', 'Outstanding Fines', 'Reward Points'];
    const rows = members.map(m => [
      m.id,
      m.name,
      m.role,
      m.status,
      m.walletBalance,
      m.outstandingFines,
      m.rewardPoints
    ]);

    const csvContent = "data:text/csv;charset=utf-8," 
      + [headers.join(','), ...rows.map(r => r.join(','))].join('\n');

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Vanguard_Members_Export_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const data = [
    { name: 'Week 1', attendance: 85, punctuality: 92 },
    { name: 'Week 2', attendance: 88, punctuality: 89 },
    { name: 'Week 3', attendance: 92, punctuality: 95 },
    { name: 'Week 4', attendance: 90, punctuality: 91 },
  ];

  const filteredMembers = members.filter(m => 
    m.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
    m.id.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="flex flex-col lg:flex-row gap-8 animate-fade-in relative">
      {selectedMember && (
        <MemberDetailModal 
          member={selectedMember} 
          onClose={() => setSelectedMember(null)} 
          onUpdate={loadData}
        />
      )}

      {/* Sidebar Navigation */}
      <aside className="w-full lg:w-72 space-y-3 shrink-0">
        <div className="bg-slate-900 text-white p-6 rounded-[2rem] mb-6 shadow-2xl relative overflow-hidden">
          <div className="relative z-10">
            <h2 className="text-2xl font-black tracking-tighter uppercase flex items-center gap-2">
              <Shield className="text-brand-400" /> Console
            </h2>
            <p className="text-[10px] text-slate-500 font-mono mt-1 uppercase tracking-widest">CACENTRE Operational Core</p>
          </div>
          <div className="absolute top-0 right-0 p-4 opacity-10"><Settings size={80}/></div>
        </div>

        {[
          { id: 'overview', icon: LayoutDashboard, label: 'Hub Intelligence' },
          { id: 'members', icon: Users, label: 'Identity Core' },
          { id: 'hardware', icon: Cpu, label: 'Mesh Nodes' },
          { id: 'sync', icon: RefreshCw, label: 'Sheet Polling' },
          { id: 'guide', icon: BookOpen, label: 'Admin Guide' }
        ].map(tab => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id as any)}
            className={`w-full flex items-center p-5 rounded-2xl font-bold transition-all ${activeTab === tab.id ? 'bg-brand-600 text-white shadow-xl' : 'text-slate-500 hover:bg-slate-100'}`}
          >
            <tab.icon className="mr-4" size={20} /> {tab.label}
          </button>
        ))}

        <div className="pt-6 border-t border-slate-100">
           <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest px-4 mb-3">Sync Protocol</p>
           <button onClick={handleSync} disabled={syncing} className="w-full p-5 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-brand-600 transition-all flex items-center justify-center gap-3 shadow-xl">
             {syncing ? <RefreshCw className="animate-spin" /> : <Cloud />} Run Polling
           </button>
           <p className="text-[8px] text-slate-300 mt-2 px-4 italic leading-tight">Apps Script integration is optional. Local data remains persistent.</p>
        </div>
      </aside>

      {/* Main Content Area */}
      <main className="flex-1 space-y-6">
        {activeTab === 'overview' && (
          <div className="space-y-6 animate-slide-up">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Hub Resilience</p>
                <h4 className="text-4xl font-black text-slate-900">94.2%</h4>
                <div className="mt-4 flex items-center gap-2 text-green-500 font-bold text-xs">
                  <TrendingUp size={14} /> +2.1% from last session
                </div>
              </div>
              <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Device Network</p>
                <h4 className="text-4xl font-black text-slate-900">8/8</h4>
                <div className="mt-4 flex items-center gap-2 text-blue-500 font-bold text-xs uppercase tracking-widest">All Nodes Online</div>
              </div>
              <div className="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pending Fines</p>
                <h4 className="text-4xl font-black text-red-600">₦245k</h4>
                <div className="mt-4 flex items-center gap-2 text-red-400 font-bold text-xs uppercase tracking-widest">12 Auto-Locks imminent</div>
              </div>
            </div>

            <div className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm">
              <div className="flex justify-between items-center mb-10">
                <h3 className="text-xl font-black tracking-tighter uppercase flex items-center gap-3">
                  <BarChart3 className="text-brand-600" /> Attendance Matrix (Toni Session)
                </h3>
              </div>
              <div className="h-[300px] w-full">
                <ResponsiveContainer width="100%" height="100%">
                  <AreaChart data={data}>
                    <defs>
                      <linearGradient id="colorAttendance" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="5%" stopColor="#0ea5e9" stopOpacity={0.1}/>
                        <stop offset="95%" stopColor="#0ea5e9" stopOpacity={0}/>
                      </linearGradient>
                    </defs>
                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                    <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{fontSize: 10, fontWeight: 700, fill: '#94a3b8'}} />
                    <YAxis axisLine={false} tickLine={false} tick={{fontSize: 10, fontWeight: 700, fill: '#94a3b8'}} />
                    <Tooltip contentStyle={{borderRadius: '16px', border: 'none', boxShadow: '0 10px 15px -3px rgb(0 0 0 / 0.1)'}} />
                    <Area type="monotone" dataKey="attendance" stroke="#0ea5e9" strokeWidth={4} fillOpacity={1} fill="url(#colorAttendance)" />
                  </AreaChart>
                </ResponsiveContainer>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'members' && (
          <div className="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden animate-slide-up">
            <div className="p-8 border-b border-slate-50 flex flex-col md:flex-row justify-between items-center gap-4">
              <div className="relative flex-1 w-full">
                <Search size={20} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300" />
                <input 
                  type="text" 
                  placeholder="Search Identity Core..." 
                  value={searchTerm}
                  onChange={e => setSearchTerm(e.target.value)}
                  className="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl outline-none font-bold text-slate-800 focus:border-brand-500 transition-all" 
                />
              </div>
              <button 
                onClick={exportCsv}
                className="p-4 bg-slate-100 text-slate-600 rounded-2xl font-black text-xs uppercase tracking-widest flex items-center gap-2 hover:bg-slate-200 transition-all whitespace-nowrap"
              >
                <FileDown size={18} /> Export CSV
              </button>
            </div>
            
            <div className="overflow-x-auto">
              <table className="w-full text-left">
                <thead className="bg-slate-50 text-[10px] font-black uppercase text-slate-400">
                  <tr>
                    <th className="p-6">Member Identity</th>
                    <th className="p-6">Role</th>
                    <th className="p-6">Status</th>
                    <th className="p-6 text-right">Wallet</th>
                    <th className="p-6 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-50 font-bold">
                  {filteredMembers.map(m => (
                    <tr key={m.id} className="hover:bg-slate-50/50 transition-colors group">
                      <td className="p-6">
                        <div className="flex items-center gap-4">
                          <img src={m.photoUrl} className="w-10 h-10 rounded-xl object-cover" />
                          <div>
                            <button 
                              onClick={() => setSelectedMember(m)}
                              className="text-slate-900 text-sm hover:text-brand-600 transition-colors underline decoration-slate-200 hover:decoration-brand-300"
                            >
                              {m.name}
                            </button>
                            <p className="text-[10px] text-slate-400 uppercase tracking-widest font-mono">{m.id}</p>
                          </div>
                        </div>
                      </td>
                      <td className="p-6">
                        {editingRoleId === m.id ? (
                          <select 
                            autoFocus
                            className="p-2 bg-white border border-brand-200 rounded-lg text-xs font-black outline-none"
                            defaultValue={m.role}
                            onChange={(e) => handleRoleChange(m.id, e.target.value as Role)}
                            onBlur={() => setEditingRoleId(null)}
                          >
                            {Object.values(Role).map(r => <option key={r} value={r}>{r}</option>)}
                          </select>
                        ) : (
                          <button 
                            onClick={() => setEditingRoleId(m.id)}
                            className="flex items-center gap-2 hover:text-brand-600 transition-all text-xs text-slate-600"
                          >
                            {m.role} <Edit2 size={12} className="opacity-0 group-hover:opacity-100" />
                          </button>
                        )}
                      </td>
                      <td className="p-6">
                        <span className={`px-2 py-1 rounded-full text-[9px] uppercase tracking-widest ${m.status === Status.ACTIVE ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                          {m.status}
                        </span>
                      </td>
                      <td className="p-6 text-right text-sm">₦{m.walletBalance.toLocaleString()}</td>
                      <td className="p-6 text-right">
                         <button 
                           onClick={() => setSelectedMember(m)}
                           className="p-2 bg-slate-100 rounded-xl text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-all"
                         >
                           <ExternalLink size={16} />
                         </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
              {filteredMembers.length === 0 && (
                <div className="p-20 text-center text-slate-300 font-black uppercase tracking-widest text-sm">
                  No Identities Match Your Search
                </div>
              )}
            </div>
          </div>
        )}

        {activeTab === 'hardware' && (
          <div className="space-y-6 animate-slide-up">
            <div className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm flex justify-between items-center">
              <div>
                <h3 className="text-3xl font-black tracking-tighter uppercase">Mesh Provisioner</h3>
                <p className="text-slate-400 text-sm font-medium mt-1">Generate encrypted configuration for Vanguard Node Hardware.</p>
              </div>
              <button onClick={() => {
                const { downloadUrl } = api.generateHardwarePackage('ORG_001', 'NODE_99');
                const a = document.createElement('a'); a.href = downloadUrl; a.download = 'config.json'; a.click();
              }} className="p-5 bg-slate-900 text-white rounded-2xl font-black text-xs hover:bg-brand-600 shadow-xl transition-all flex items-center gap-2">
                <Download size={20} /> Download Master Package
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {[1, 2, 3, 4].map(n => (
                <div key={n} className="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center justify-between">
                  <div className="flex items-center gap-4">
                    <div className="bg-green-100 text-green-600 p-4 rounded-2xl">
                      <Wifi size={24} />
                    </div>
                    <div>
                      <p className="font-black text-slate-900">Lobby Portal Alpha-{n}</p>
                      <p className="text-[10px] text-slate-400 font-mono">SEC_KEY: VNG_NODE_ENCRYPTED</p>
                    </div>
                  </div>
                  <div className="text-right">
                    <span className="px-2 py-1 bg-green-50 text-green-600 text-[8px] font-black rounded-full uppercase">Online</span>
                    <p className="text-[8px] text-slate-400 mt-1 uppercase">Ping: 14ms</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {activeTab === 'sync' && (
          <div className="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm animate-slide-up space-y-8">
            <div className="flex items-center justify-between border-b border-slate-50 pb-6">
              <h3 className="text-2xl font-black tracking-tighter">Conflict Resolution Hub</h3>
              <div className="bg-brand-50 text-brand-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase">Review Required</div>
            </div>

            {conflicts.length > 0 ? (
              <div className="space-y-4">
                {conflicts.map(c => (
                  <div key={c.memberId} className="p-6 bg-slate-50 rounded-[2rem] border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                      <h4 className="font-black text-slate-900">{c.name}</h4>
                      <p className="text-[10px] text-slate-400 uppercase font-black">{c.field} Mismatch</p>
                    </div>
                    <div className="flex gap-4">
                      <button onClick={() => api.resolveSync(c.memberId, 'local', c.localValue)} className="p-4 bg-slate-900 text-white rounded-xl text-xs font-black shadow-lg">Local Hub (₦{c.localValue})</button>
                      <button onClick={() => api.resolveSync(c.memberId, 'sheet', c.sheetValue)} className="p-4 bg-white border border-slate-200 text-brand-600 rounded-xl text-xs font-black shadow-sm">Google Sheets (₦{c.sheetValue})</button>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-20 text-slate-300">
                <ShieldCheck size={64} className="mx-auto mb-4 opacity-20" />
                <p className="font-black text-sm uppercase tracking-widest">Hub Database Synchronized</p>
              </div>
            )}
          </div>
        )}

        {activeTab === 'guide' && (
          <div className="bg-white p-12 rounded-[3rem] border border-slate-100 shadow-sm animate-slide-up space-y-10 max-w-4xl">
            <div className="space-y-4">
              <h3 className="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                <BookOpen className="text-brand-600" /> Administrative Protocol Guide
              </h3>
              <p className="text-slate-500 font-medium leading-relaxed">
                Welcome to the Vanguard OnePass Core. This system manages identity, access, and finance through a high-resilience local hub.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div className="space-y-4">
                <h4 className="text-xs font-black text-slate-400 uppercase tracking-widest">Core Management</h4>
                <ul className="space-y-4 text-sm font-bold text-slate-700">
                  <li className="flex gap-3">
                    <div className="w-1.5 h-1.5 bg-brand-500 rounded-full mt-2 shrink-0" />
                    <p><span className="text-slate-900">Member Insights:</span> Click any member's name to view their full attendance timeline and journey progress.</p>
                  </li>
                  <li className="flex gap-3">
                    <div className="w-1.5 h-1.5 bg-brand-500 rounded-full mt-2 shrink-0" />
                    <p><span className="text-slate-900">Role Modification:</span> Hover over the 'Role' column in the member list to quickly toggle between Member, Staff, and Admin.</p>
                  </li>
                  <li className="flex gap-3">
                    <div className="w-1.5 h-1.5 bg-brand-500 rounded-full mt-2 shrink-0" />
                    <p><span className="text-slate-900">QR Provisioning:</span> Admins can link custom QR codes per member by pasting an image link in the member detail panel.</p>
                  </li>
                </ul>
              </div>

              <div className="space-y-4">
                <h4 className="text-xs font-black text-slate-400 uppercase tracking-widest">Sync & Automation</h4>
                <ul className="space-y-4 text-sm font-bold text-slate-700">
                  <li className="flex gap-3">
                    <div className="w-1.5 h-1.5 bg-green-500 rounded-full mt-2 shrink-0" />
                    <p><span className="text-slate-900">Optional Cloud:</span> Google Apps Script integration is purely for external synchronization. The system works 100% offline using local persistence.</p>
                  </li>
                  <li className="flex gap-3">
                    <div className="w-1.5 h-1.5 bg-green-500 rounded-full mt-2 shrink-0" />
                    <p><span className="text-slate-900">Conflict Hub:</span> If a mismatch occurs during polling, the Hub will ask you to verify which data source is authoritative.</p>
                  </li>
                  <li className="flex gap-3">
                    <div className="w-1.5 h-1.5 bg-green-500 rounded-full mt-2 shrink-0" />
                    <p><span className="text-slate-900">Data Portability:</span> Use the CSV Export tool to back up your identity core at any time for spreadsheet analysis.</p>
                  </li>
                </ul>
              </div>
            </div>

            <div className="p-8 bg-slate-950 rounded-[2.5rem] text-white space-y-4">
               <h4 className="text-xs font-black text-brand-400 uppercase tracking-[0.2em]">Security Protocol</h4>
               <p className="text-sm font-medium opacity-80 leading-relaxed">
                 Always ensure your "Master Protocol" credentials remain secure. The Admin Console allows for financial adjustments and access level elevation which bypasses standard member journey gates.
               </p>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
