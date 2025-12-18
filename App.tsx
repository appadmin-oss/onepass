
import React, { useState, useEffect } from 'react';
import { HashRouter, Routes, Route, Navigate, Link, useLocation } from 'react-router-dom';
import { Shield, User, LayoutDashboard, ScanLine, Sparkles, Lock, Building2, ArrowRight, Settings, Globe } from 'lucide-react';
import AdminDashboard from './pages/AdminDashboard';
import MemberDashboard from './pages/MemberDashboard';
import ReceptionPopup from './pages/ReceptionPopup';
import AIAssistant from './pages/AIAssistant';
import { Member, Role, Organization } from './types';
import { api } from './services/api';

const Navigation = ({ user }: { user: Member | null }) => {
  const location = useLocation();
  const isActive = (path: string) => location.pathname.startsWith(path) ? "text-brand-600 font-bold" : "text-slate-500 hover:text-slate-700";
  const isAdmin = user?.role === Role.ADMIN || user?.role === Role.MASTER;
  const isStaff = user?.role === Role.STAFF;

  return (
    <nav className="fixed bottom-0 w-full bg-white border-t border-slate-200 p-2 pb-safe flex justify-around items-center md:hidden z-50">
      {(isAdmin || isStaff) && (
        <Link to="/reception" className={`flex flex-col items-center p-2 ${isActive('/reception')}`}>
          <ScanLine size={24} />
          <span className="text-[10px] mt-1">Scan</span>
        </Link>
      )}
      <Link to="/member" className={`flex flex-col items-center p-2 ${isActive('/member')}`}>
        <User size={24} />
        <span className="text-[10px] mt-1">Profile</span>
      </Link>
      <Link to="/assistant" className={`flex flex-col items-center p-2 ${isActive('/assistant')}`}>
        <div className="relative">
          <Sparkles size={24} />
          <span className="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
        </div>
        <span className="text-[10px] mt-1">AI Help</span>
      </Link>
      {(isAdmin) && (
        <Link to={user?.role === Role.MASTER ? "/master" : "/admin"} className={`flex flex-col items-center p-2 ${isActive('/admin') || isActive('/master')}`}>
          <Shield size={24} />
          <span className="text-[10px] mt-1">{user?.role === Role.MASTER ? 'Master' : 'Admin'}</span>
        </Link>
      )}
    </nav>
  );
};

const Header = ({ user, org }: { user: Member | null, org: Organization | null }) => {
  return (
    <header className="bg-slate-900 text-white p-4 shadow-lg sticky top-0 z-40 border-b border-brand-500">
      <div className="container mx-auto flex justify-between items-center">
        <div className="flex items-center space-x-2">
          <div className="bg-brand-600 p-1.5 rounded-lg shadow-inner">
             <Shield className="text-white" size={20} />
          </div>
          <div className="flex flex-col">
              <h1 className="text-xl font-black tracking-tighter leading-none">ONEPASS</h1>
              <span className="text-[9px] text-brand-400 font-mono tracking-widest uppercase">
                {user?.role === Role.MASTER ? 'Global Controller' : `${org?.name || 'Vanguard'} Hub`}
              </span>
          </div>
        </div>
        <div className="hidden md:flex items-center space-x-6 text-sm font-medium">
           {(user?.role === Role.ADMIN || user?.role === Role.STAFF || user?.role === Role.MASTER) && (
              <Link to="/reception" className="hover:text-brand-400 transition-colors">Reception</Link>
           )}
           <Link to="/member" className="hover:text-brand-400 transition-colors">Portal</Link>
           {user?.role === Role.ADMIN && (
             <Link to="/admin" className="text-yellow-400 font-bold flex items-center gap-1.5 px-3 py-1 border border-yellow-400/30 rounded-full hover:bg-yellow-400/10 transition-colors">
               <Lock size={14} /> Admin
             </Link>
           )}
           {user?.role === Role.MASTER && (
             <Link to="/master" className="text-purple-400 font-bold flex items-center gap-1.5 px-3 py-1 border border-purple-400/30 rounded-full hover:bg-purple-400/10 transition-colors">
               <Globe size={14} /> Master Console
             </Link>
           )}
        </div>
      </div>
    </header>
  );
};

const MasterAdminPortal = ({ setUser }: { setUser: (m: Member | null) => void }) => {
    const [org, setOrg] = useState<Organization | null>(null);
    useEffect(() => {
        const d = localStorage.getItem('onepass_org');
        if (d) setOrg(JSON.parse(d));
    }, []);

    const togglePayment = async () => {
        if (!org) return;
        const next = !org.isPaid;
        await api.setOrgPaidStatus(org.id, next);
        setOrg({ ...org, isPaid: next });
        alert(`Organization Payment Status: ${next ? 'PAID' : 'FREE'}`);
    };

    return (
        <div className="max-w-4xl mx-auto p-10 bg-white rounded-[3rem] shadow-2xl border border-slate-100 animate-fade-in">
            <div className="flex items-center justify-between mb-12">
                <div>
                    <h2 className="text-4xl font-black tracking-tighter text-slate-900">Master Controller</h2>
                    <p className="text-slate-400 font-medium uppercase tracking-widest text-[10px] mt-1">Global System Oversight</p>
                </div>
                <div className="p-4 bg-purple-100 text-purple-700 rounded-2xl"><Globe size={32}/></div>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div className="p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 space-y-6">
                    <h3 className="text-xl font-black">Org Management</h3>
                    <div className="flex items-center justify-between p-6 bg-white rounded-3xl shadow-sm">
                        <div>
                            <p className="font-bold text-slate-800">{org?.name}</p>
                            <p className="text-[10px] text-slate-400 font-mono">{org?.id}</p>
                        </div>
                        <button onClick={togglePayment} className={`px-4 py-2 rounded-xl font-black text-[10px] uppercase transition-all ${org?.isPaid ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-200 text-slate-500'}`}>
                            {org?.isPaid ? 'PAID TIER' : 'FREE TIER'}
                        </button>
                    </div>
                </div>
                
                <div className="p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 space-y-6">
                    <h3 className="text-xl font-black">Global Stats</h3>
                    <div className="grid grid-cols-2 gap-4">
                        <div className="p-6 bg-white rounded-3xl shadow-sm text-center">
                            <p className="text-[8px] font-black text-slate-400 uppercase">Active Hubs</p>
                            <p className="text-2xl font-black">1</p>
                        </div>
                        <div className="p-6 bg-white rounded-3xl shadow-sm text-center">
                            <p className="text-[8px] font-black text-slate-400 uppercase">Revenue</p>
                            <p className="text-2xl font-black">₦0</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <button onClick={() => { setUser(null); window.location.hash = "/member"; }} className="mt-12 w-full p-6 text-slate-400 font-black text-xs uppercase tracking-widest hover:text-purple-600 transition-colors">Exit Master Protocol</button>
        </div>
    );
};

const OrgSelector = ({ setOrg }: { setOrg: (org: Organization) => void }) => {
  const [orgId, setOrgId] = useState('');
  const [isNew, setIsNew] = useState(false);
  const [orgName, setOrgName] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (orgId.trim().length < 3) return alert("Invalid Organization ID");
    // Added required settings property to meet Organization interface requirements
    const newOrg: Organization = {
      id: orgId.toUpperCase().replace(/\s/g, '_'),
      name: isNew ? orgName : orgId.toUpperCase(),
      type: orgId.toUpperCase().includes('CACENTRE') ? 'CACENTRE' : 'GENERAL',
      isPaid: false,
      settings: {
        walletEnabled: true,
        journeysEnabled: true
      }
    };
    localStorage.setItem('onepass_org', JSON.stringify(newOrg));
    setOrg(newOrg);
  };

  return (
    <div className="min-h-screen bg-slate-950 flex items-center justify-center p-4 font-sans antialiased overflow-hidden">
      <div className="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_50%_50%,rgba(14,165,233,0.15),transparent_50%)] pointer-events-none"></div>
      <div className="bg-white rounded-3xl shadow-[0_32px_64px_-12px_rgba(0,0,0,0.5)] overflow-hidden w-full max-w-4xl flex flex-col md:flex-row border border-white/10">
         <div className="md:w-1/2 bg-brand-600 p-12 text-white flex flex-col justify-center relative overflow-hidden">
            <div className="absolute -bottom-24 -left-24 w-64 h-64 bg-brand-500 rounded-full opacity-20 blur-3xl"></div>
            <div className="mb-8 bg-white/20 w-16 h-16 rounded-2xl flex items-center justify-center backdrop-blur-xl border border-white/30 shadow-2xl">
               <Shield size={32} />
            </div>
            <h1 className="text-5xl font-black tracking-tighter mb-6 leading-tight">Vanguard<br/>OnePass™</h1>
            <p className="text-brand-100 text-lg leading-relaxed opacity-90 font-medium">
               A unified core for access, identity, and digital finance.
            </p>
         </div>
         <div className="md:w-1/2 p-12 flex flex-col justify-center bg-white">
            <h2 className="text-2xl font-black text-slate-900 mb-8 flex items-center">
               <Building2 className="mr-3 text-brand-600" />
               {isNew ? 'Create Hub' : 'Enter Gateway'}
            </h2>
            <form onSubmit={handleSubmit} className="space-y-6">
                {isNew && (
                  <div className="animate-fade-in">
                    <label className="block text-xs font-black text-slate-400 mb-2 uppercase tracking-widest">Org Name</label>
                    <input 
                      type="text" 
                      value={orgName} 
                      onChange={e => setOrgName(e.target.value)}
                      className="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-brand-500 focus:bg-white transition-all outline-none font-semibold text-slate-800" 
                      placeholder="e.g. Global Tech Inc."
                      required
                    />
                  </div>
                )}
                <div className="animate-fade-in">
                  <label className="block text-xs font-black text-slate-400 mb-2 uppercase tracking-widest">Org Identifier</label>
                  <input 
                    type="text" 
                    value={orgId} 
                    onChange={e => setOrgId(e.target.value)}
                    className="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-brand-500 focus:bg-white transition-all outline-none font-mono font-bold text-brand-600 uppercase" 
                    placeholder="E.G. VANGUARD_X"
                    required
                  />
                </div>
                <button className="w-full bg-slate-900 text-white p-5 rounded-2xl font-black text-lg hover:bg-brand-600 shadow-xl shadow-brand-200/20 transition-all flex items-center justify-center group active:scale-[0.98]">
                   {isNew ? 'Initialize Hub' : 'Access Gateway'}
                   <ArrowRight size={22} className="ml-3 group-hover:translate-x-1.5 transition-transform" />
                </button>
            </form>
            <div className="mt-10 text-center">
               <button onClick={() => setIsNew(!isNew)} className="text-sm text-brand-600 font-black hover:text-brand-800 transition-colors">
                 {isNew ? 'HUB EXISTS? LOGIN' : 'NEW ORGANIZATION? REGISTER'}
               </button>
            </div>
         </div>
      </div>
    </div>
  );
};

export default function App() {
  const [currentUser, setCurrentUser] = useState<Member | null>(null);
  const [currentOrg, setCurrentOrg] = useState<Organization | null>(null);

  useEffect(() => {
    const saved = localStorage.getItem('onepass_org');
    if (saved) setCurrentOrg(JSON.parse(saved));
  }, []);

  if (!currentOrg) return <OrgSelector setOrg={setCurrentOrg} />;

  return (
    <HashRouter>
      <div className="min-h-screen bg-slate-50 pb-24 md:pb-0 font-sans selection:bg-brand-100 selection:text-brand-900">
        <Header user={currentUser} org={currentOrg} />
        <main className="container mx-auto p-4 md:p-8">
          <Routes>
            <Route path="/" element={<Navigate to="/member" replace />} />
            <Route path="/admin/*" element={
              currentUser?.role === Role.ADMIN 
                ? <AdminDashboard user={currentUser} setUser={setCurrentUser} /> 
                : <Navigate to="/member" />
            } />
            <Route path="/master" element={
                currentUser?.role === Role.MASTER
                ? <MasterAdminPortal setUser={setCurrentUser} />
                : <Navigate to="/member" />
            } />
            <Route path="/member/*" element={<MemberDashboard user={currentUser} setUser={setCurrentUser} />} />
            <Route path="/assistant" element={<AIAssistant user={currentUser} />} />
            <Route path="/reception" element={
              (currentUser?.role === Role.ADMIN || currentUser?.role === Role.STAFF || currentUser?.role === Role.MASTER)
                ? <ReceptionPopup />
                : <Navigate to="/member" />
            } />
          </Routes>
        </main>
        <Navigation user={currentUser} />
      </div>
    </HashRouter>
  );
}
