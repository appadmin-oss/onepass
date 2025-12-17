import React, { useState, useEffect } from 'react';
import { 
  Users, AlertTriangle, TrendingUp, Search, Settings, 
  Database, RefreshCw, DollarSign, Lock, Shield, 
  CheckCircle, XCircle, Power, FileText 
} from 'lucide-react';
import { api } from '../services/api';
import { Member, SystemConfig, Role, Status } from '../types';

interface AdminDashboardProps {
  user: Member | null;
  setUser: (member: Member | null) => void;
}

export default function AdminDashboard({ user, setUser }: AdminDashboardProps) {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [adminId, setAdminId] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  
  const [activeTab, setActiveTab] = useState<'overview' | 'members' | 'rules' | 'finance' | 'sync'>('overview');
  const [loading, setLoading] = useState(false);

  // Data State
  const [members, setMembers] = useState<Member[]>([]);
  const [stats, setStats] = useState<any>(null);
  const [config, setConfig] = useState<SystemConfig | null>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [syncStatus, setSyncStatus] = useState('');

  useEffect(() => {
    if (user?.role === Role.ADMIN) {
      setIsAuthenticated(true);
      loadData();
    }
  }, [user]);

  const loadData = async () => {
    setLoading(true);
    try {
      const [m, s, c] = await Promise.all([
        api.getAllMembers(),
        api.getStats(),
        api.getConfig()
      ]);
      setMembers(m || []);
      setStats(s);
      setConfig(c);
    } catch (e) {
      console.error("Failed to load admin data");
    } finally {
      setLoading(false);
    }
  };

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const res = await api.login(adminId, password);
      if (res.success && res.member.role === Role.ADMIN) {
        setUser(res.member);
        setIsAuthenticated(true);
        loadData();
      } else {
        setError('Access Denied. Admin privileges required.');
      }
    } catch (e) {
      setError('Login failed');
    }
  };

  const handleSync = async () => {
    setSyncStatus('Compiling Central DB from Google Sheets...');
    try {
      await api.runCompiler();
      setSyncStatus('Sync Complete. Reloading data...');
      await loadData();
      setSyncStatus('Success: Data merged from all sources.');
    } catch (e) {
      setSyncStatus('Error: Failed to sync.');
    }
  };

  const handleConfigUpdate = async (key: keyof SystemConfig, value: any) => {
    if (!config) return;
    const newConfig = { ...config, [key]: value };
    setConfig(newConfig);
    await api.updateConfig({ [key]: value });
  };

  const handleMemberAction = async (id: string, action: 'BLOCK' | 'ACTIVATE') => {
    const status = action === 'BLOCK' ? Status.BLOCKED : Status.ACTIVE;
    await api.updateMember(id, { status });
    setMembers(prev => prev.map(m => m.id === id ? { ...m, status } : m));
  };

  if (!isAuthenticated) {
    return (
      <div className="max-w-md mx-auto mt-20">
        <div className="bg-slate-900 text-white p-8 rounded-xl shadow-2xl border border-slate-700">
          <div className="text-center mb-8">
            <div className="bg-red-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-[0_0_20px_rgba(220,38,38,0.5)]">
              <Lock size={32} className="text-white" />
            </div>
            <h1 className="text-3xl font-bold tracking-tight">Vanguard <span className="text-red-500">GOD MODE</span></h1>
            <p className="text-slate-400 mt-2">Restricted Access. Authorized Personnel Only.</p>
          </div>
          <form onSubmit={handleLogin} className="space-y-4">
            <div>
              <label className="text-xs uppercase font-bold text-slate-500 tracking-wider">Admin ID</label>
              <input 
                type="text" 
                value={adminId}
                onChange={e => setAdminId(e.target.value)}
                className="w-full bg-slate-800 border border-slate-700 p-3 rounded text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition-all"
              />
            </div>
            <div>
              <label className="text-xs uppercase font-bold text-slate-500 tracking-wider">Password</label>
              <input 
                type="password" 
                value={password}
                onChange={e => setPassword(e.target.value)}
                className="w-full bg-slate-800 border border-slate-700 p-3 rounded text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition-all"
              />
            </div>
            {error && <div className="text-red-400 text-sm text-center">{error}</div>}
            <button className="w-full bg-red-600 hover:bg-red-700 text-white font-bold p-4 rounded shadow-lg transition-all mt-4">
              AUTHENTICATE
            </button>
          </form>
        </div>
      </div>
    );
  }

  return (
    <div className="flex flex-col md:flex-row min-h-[calc(100vh-100px)] gap-6 animate-fade-in">
      {/* Sidebar Navigation */}
      <div className="w-full md:w-64 flex-shrink-0">
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden sticky top-24">
          <div className="p-6 bg-slate-900 text-white">
            <h2 className="font-bold flex items-center"><Shield className="mr-2 text-red-500"/> Admin Console</h2>
            <p className="text-xs text-slate-400 mt-1">v2.5.0 • Connected</p>
          </div>
          <nav className="p-2 space-y-1">
            <button onClick={() => setActiveTab('overview')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium transition-colors ${activeTab === 'overview' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <TrendingUp size={18} className="mr-3" /> Dashboard Overview
            </button>
            <button onClick={() => setActiveTab('members')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium transition-colors ${activeTab === 'members' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Users size={18} className="mr-3" /> Member Management
            </button>
            <button onClick={() => setActiveTab('finance')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium transition-colors ${activeTab === 'finance' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <DollarSign size={18} className="mr-3" /> Financial Control
            </button>
            <button onClick={() => setActiveTab('rules')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium transition-colors ${activeTab === 'rules' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Settings size={18} className="mr-3" /> Rules Engine
            </button>
            <button onClick={() => setActiveTab('sync')} className={`w-full flex items-center p-3 rounded-lg text-sm font-medium transition-colors ${activeTab === 'sync' ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50'}`}>
              <Database size={18} className="mr-3" /> Data Sync
            </button>
          </nav>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 space-y-6">
        
        {/* OVERVIEW TAB */}
        {activeTab === 'overview' && (
          <>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="bg-slate-900 text-white p-6 rounded-xl shadow-lg">
                <div className="text-slate-400 text-sm mb-1 uppercase tracking-wider">Total Members</div>
                <div className="text-4xl font-bold">{stats?.totalMembers}</div>
                <div className="text-green-400 text-sm mt-2 flex items-center"><CheckCircle size={14} className="mr-1"/> {stats?.activeToday} active today</div>
              </div>
              <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div className="text-slate-500 text-sm mb-1 uppercase tracking-wider">Outstanding Fines</div>
                <div className="text-4xl font-bold text-red-600">₦{stats?.totalFines.toLocaleString()}</div>
                <div className="text-slate-400 text-sm mt-2">Needs collection</div>
              </div>
              <div className="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div className="text-slate-500 text-sm mb-1 uppercase tracking-wider">System Liability</div>
                <div className="text-4xl font-bold text-brand-600">₦{stats?.totalWallet.toLocaleString()}</div>
                <div className="text-slate-400 text-sm mt-2">Total wallet balances</div>
              </div>
            </div>
          </>
        )}

        {/* MEMBERS TAB */}
        {activeTab === 'members' && (
          <div className="bg-white rounded-xl shadow-sm border border-slate-200">
            <div className="p-4 border-b border-slate-100 flex justify-between items-center">
              <h2 className="font-bold text-lg">Member Database</h2>
              <div className="relative">
                <Search className="absolute left-3 top-2.5 text-slate-400" size={18} />
                <input 
                  type="text" 
                  placeholder="Search..." 
                  value={searchTerm}
                  onChange={e => setSearchTerm(e.target.value)}
                  className="pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                />
              </div>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 text-slate-500">
                  <tr>
                    <th className="p-4">Identity</th>
                    <th className="p-4">Role</th>
                    <th className="p-4">Status</th>
                    <th className="p-4">Wallet</th>
                    <th className="p-4 text-center">Controls</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {members.filter(m => m.name.toLowerCase().includes(searchTerm.toLowerCase()) || m.id.includes(searchTerm)).map(m => (
                    <tr key={m.id} className="hover:bg-slate-50">
                      <td className="p-4 flex items-center space-x-3">
                        <img src={m.photoUrl} className="w-8 h-8 rounded-full bg-slate-200" alt="" />
                        <div>
                          <div className="font-bold text-slate-900">{m.name}</div>
                          <div className="text-xs text-slate-500">{m.id}</div>
                        </div>
                      </td>
                      <td className="p-4"><span className="bg-slate-100 px-2 py-1 rounded text-xs">{m.role}</span></td>
                      <td className="p-4">
                        <span className={`px-2 py-1 rounded-full text-xs font-bold ${
                           m.status === Status.ACTIVE ? 'bg-green-100 text-green-700' : 
                           m.status === Status.BLOCKED ? 'bg-slate-800 text-white' : 'bg-red-100 text-red-700'
                        }`}>{m.status}</span>
                      </td>
                      <td className="p-4 font-mono">₦{m.walletBalance.toLocaleString()}</td>
                      <td className="p-4 flex justify-center space-x-2">
                        {m.status !== Status.BLOCKED ? (
                          <button onClick={() => handleMemberAction(m.id, 'BLOCK')} className="text-red-600 hover:bg-red-50 p-2 rounded" title="Block Member">
                            <XCircle size={18} />
                          </button>
                        ) : (
                          <button onClick={() => handleMemberAction(m.id, 'ACTIVATE')} className="text-green-600 hover:bg-green-50 p-2 rounded" title="Activate Member">
                            <CheckCircle size={18} />
                          </button>
                        )}
                        <button className="text-brand-600 hover:bg-brand-50 p-2 rounded" title="Edit Profile">
                          <Settings size={18} />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* RULES TAB */}
        {activeTab === 'rules' && config && (
          <div className="grid gap-6">
            <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
              <h3 className="font-bold text-lg mb-4 flex items-center text-slate-800">
                <Settings className="mr-2" size={20}/> Global Rule Engine
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-slate-500 mb-1">Daily Resumption Time</label>
                  <div className="flex items-center">
                    <input 
                      type="time" 
                      value={config.resumptionTime} 
                      onChange={(e) => handleConfigUpdate('resumptionTime', e.target.value)}
                      className="w-full p-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none" 
                    />
                    <button onClick={() => handleConfigUpdate('resumptionTime', '')} className="ml-2 text-slate-400 hover:text-red-500" title="Nullify Rule">
                      <Power size={18} />
                    </button>
                  </div>
                  <p className="text-xs text-slate-400 mt-1">Arrivals after this time will trigger automatic fines.</p>
                </div>

                <div>
                  <label className="block text-sm font-medium text-slate-500 mb-1">Late Fine Amount (₦)</label>
                  <div className="flex items-center">
                    <input 
                      type="number" 
                      value={config.lateFineAmount} 
                      onChange={(e) => handleConfigUpdate('lateFineAmount', parseInt(e.target.value))}
                      className="w-full p-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none" 
                    />
                     <button onClick={() => handleConfigUpdate('lateFineAmount', 0)} className="ml-2 text-slate-400 hover:text-red-500" title="Nullify Fine">
                      <Power size={18} />
                    </button>
                  </div>
                </div>

                <div>
                   <label className="block text-sm font-medium text-slate-500 mb-1">Auto-Suspend Debt Threshold</label>
                   <input 
                      type="number" 
                      value={config.autoSuspendThreshold} 
                      onChange={(e) => handleConfigUpdate('autoSuspendThreshold', parseInt(e.target.value))}
                      className="w-full p-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-red-600" 
                    />
                    <p className="text-xs text-slate-400 mt-1">Members with debt exceeding this (negative value) are auto-blocked.</p>
                </div>
              </div>
            </div>

            <div className="bg-red-50 border border-red-100 rounded-xl p-6">
              <h3 className="font-bold text-red-800 mb-2">Danger Zone</h3>
              <div className="flex items-center justify-between">
                <div>
                  <div className="font-medium text-red-900">System Maintenance Mode</div>
                  <div className="text-sm text-red-700">Blocks all member logins and freezes transactions.</div>
                </div>
                <label className="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" checked={config.maintenanceMode} onChange={(e) => handleConfigUpdate('maintenanceMode', e.target.checked)} className="sr-only peer" />
                  <div className="w-11 h-6 bg-red-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                </label>
              </div>
            </div>
          </div>
        )}

        {/* FINANCE TAB */}
        {activeTab === 'finance' && (
          <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 className="font-bold text-lg mb-6 flex items-center">
              <DollarSign className="mr-2" size={20}/> Manual Transaction Entry
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
               <div className="space-y-4">
                 <label className="block text-sm font-medium">Target Member ID</label>
                 <input type="text" placeholder="VG..." className="w-full p-3 border border-slate-200 rounded-lg" />
                 
                 <label className="block text-sm font-medium">Transaction Type</label>
                 <select className="w-full p-3 border border-slate-200 rounded-lg">
                   <option value="Credit">Credit (Deposit)</option>
                   <option value="Fine">Fine (Penalty)</option>
                   <option value="Debit">Debit (Withdrawal)</option>
                   <option value="Award">Award (Bonus)</option>
                 </select>

                 <label className="block text-sm font-medium">Amount (₦)</label>
                 <input type="number" placeholder="0.00" className="w-full p-3 border border-slate-200 rounded-lg" />
               </div>
               <div className="space-y-4">
                 <label className="block text-sm font-medium">Description</label>
                 <textarea className="w-full p-3 border border-slate-200 rounded-lg h-32" placeholder="Reason for transaction..."></textarea>
                 
                 <button className="w-full bg-slate-900 text-white font-bold p-3 rounded-lg hover:bg-slate-800 transition">
                   Post to Ledger
                 </button>
               </div>
            </div>
          </div>
        )}

        {/* SYNC TAB */}
        {activeTab === 'sync' && (
          <div className="flex flex-col items-center justify-center h-full bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
             <div className="bg-brand-50 p-6 rounded-full mb-6">
               <Database size={64} className="text-brand-600" />
             </div>
             <h2 className="text-2xl font-bold text-slate-800 mb-2">Google Sheets Data Sync</h2>
             <p className="text-slate-500 max-w-md mb-8">
               This will trigger the Central Compiler to merge 'Import-MGT', 'Import-NGV', and other source sheets into the live database. 
               <br/><strong className="text-slate-800">This action cannot be undone.</strong>
             </p>
             
             {syncStatus && (
               <div className="mb-6 p-4 bg-slate-100 rounded-lg text-slate-700 font-mono text-sm">
                 {syncStatus}
               </div>
             )}

             <button 
               onClick={handleSync}
               disabled={loading}
               className="flex items-center px-8 py-4 bg-brand-600 text-white rounded-xl font-bold text-lg hover:bg-brand-700 transition shadow-lg disabled:opacity-50"
             >
               {loading ? <RefreshCw className="animate-spin mr-2" /> : <RefreshCw className="mr-2" />}
               Run Compiler & Sync
             </button>
          </div>
        )}

      </div>
    </div>
  );
}