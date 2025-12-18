
import React, { useState, useEffect } from 'react';
import { HashRouter, Routes, Route, Navigate, Link, useLocation } from 'react-router-dom';
import { Shield, User, LayoutDashboard, ScanLine, Sparkles, Lock, Building2, ArrowRight, Settings, Globe, LogOut } from 'lucide-react';
import AdminDashboard from './pages/AdminDashboard.tsx';
import MemberDashboard from './pages/MemberDashboard.tsx';
import ReceptionPopup from './pages/ReceptionPopup.tsx';
import AIAssistant from './pages/AIAssistant.tsx';
import { Member, Role, Organization, Status } from './types.ts';
import { api } from './services/api.ts';

const Navigation = ({ user }: { user: Member | null }) => {
  const location = useLocation();
  const isActive = (path: string) => location.pathname.startsWith(path) ? "text-brand-600 font-bold" : "text-slate-500 hover:text-slate-700";
  const isAdmin = user?.role === Role.ADMIN || user?.role === Role.MASTER;
  const isStaff = user?.role === Role.STAFF;

  if (!user) return null;

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
      {isAdmin && (
        <Link to="/admin" className={`flex flex-col items-center p-2 ${isActive('/admin')}`}>
          <Shield size={24} />
          <span className="text-[10px] mt-1">Admin</span>
        </Link>
      )}
    </nav>
  );
};

const Header = ({ user, org, onLogout }: { user: Member | null, org: Organization | null, onLogout: () => void }) => {
  return (
    <header className="bg-slate-900 text-white p-4 shadow-lg sticky top-0 z-40 border-b border-brand-500">
      <div className="container mx-auto flex justify-between items-center">
        <div className="flex items-center gap-3">
          <div className="bg-brand-500 p-2 rounded-xl">
            <Shield className="text-white" size={20} />
          </div>
          <div>
            <h1 className="font-black text-lg tracking-tighter uppercase leading-none">Vanguard OnePass™</h1>
            <p className="text-[9px] font-bold text-brand-400 uppercase tracking-widest">{org?.name || 'Operational Core'}</p>
          </div>
        </div>
        
        {user && (
          <div className="flex items-center gap-4">
            <div className="hidden md:flex flex-col text-right">
              <span className="text-xs font-black">{user.name}</span>
              <span className="text-[10px] text-slate-400 uppercase tracking-widest">{user.role}</span>
            </div>
            <img src={user.photoUrl} className="w-8 h-8 rounded-lg border border-slate-700 object-cover" alt="Profile" />
            <button onClick={onLogout} className="p-2 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors">
              <LogOut size={18} />
            </button>
          </div>
        )}
      </div>
    </header>
  );
};

const Login = ({ onLogin }: { onLogin: (m: Member) => void }) => {
  const [id, setId] = useState('');
  const [pass, setPass] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    try {
      const res = await api.login(id, pass);
      if (res.success && res.member) {
        onLogin(res.member);
      } else {
        setError(res.error || 'Authentication Failed');
      }
    } catch (err) {
      setError('Connection Protocol Error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-[80vh] flex items-center justify-center p-6">
      <div className="bg-white p-10 rounded-[3rem] shadow-2xl border border-slate-100 w-full max-w-md">
        <div className="text-center mb-10">
          <div className="bg-slate-900 w-20 h-20 rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-xl">
            <Lock className="text-brand-500" size={40} />
          </div>
          <h2 className="text-3xl font-black text-slate-900 tracking-tighter">AUTHENTICATE</h2>
          <p className="text-slate-400 font-bold uppercase text-[10px] tracking-[0.2em] mt-2">Vanguard Identity Protocol</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Identity Core ID</label>
            <input 
              type="text" 
              placeholder="e.g. VG-001" 
              value={id} 
              onChange={e => setId(e.target.value.toUpperCase())}
              className="w-full p-5 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-brand-500 outline-none transition-all"
              required 
            />
          </div>
          <div className="space-y-2">
            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Secure Passcode</label>
            <input 
              type="password" 
              placeholder="••••••••" 
              value={pass} 
              onChange={e => setPass(e.target.value)}
              className="w-full p-5 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold focus:border-brand-500 outline-none transition-all"
            />
          </div>
          {error && <p className="text-red-500 text-xs font-bold text-center">{error}</p>}
          <button 
            disabled={loading}
            className="w-full bg-slate-900 text-white p-6 rounded-2xl font-black text-lg hover:bg-brand-600 shadow-xl transition-all flex items-center justify-center gap-3"
          >
            {loading ? 'VERIFYING...' : <>SECURE LOGIN <ArrowRight size={20}/></>}
          </button>
        </form>
      </div>
    </div>
  );
};

export default function App() {
  const [user, setUser] = useState<Member | null>(null);
  const [org] = useState<Organization>({
    id: 'CAC_01',
    name: 'Vanguard CACENTRE',
    type: 'CACENTRE',
    isPaid: true,
    settings: { walletEnabled: true, journeysEnabled: true }
  });

  const handleLogout = () => {
    setUser(null);
  };

  return (
    <HashRouter>
      <div className="min-h-screen bg-slate-50 flex flex-col">
        <Header user={user} org={org} onLogout={handleLogout} />
        
        <main className="flex-1 container mx-auto px-4 py-8 pb-32">
          {!user ? (
            <Login onLogin={setUser} />
          ) : (
            <Routes>
              <Route path="/member" element={<MemberDashboard user={user} setUser={setUser} />} />
              <Route path="/reception" element={<ReceptionPopup />} />
              <Route path="/assistant" element={<AIAssistant user={user} />} />
              <Route path="/admin" element={
                (user.role === Role.ADMIN || user.role === Role.MASTER)
                  ? <AdminDashboard user={user} setUser={setUser} /> 
                  : <Navigate to="/member" />
              } />
              <Route path="*" element={<Navigate to="/member" />} />
            </Routes>
          )}
        </main>

        <Navigation user={user} />
      </div>
    </HashRouter>
  );
}

