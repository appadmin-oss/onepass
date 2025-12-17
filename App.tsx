import React, { useState, useEffect } from 'react';
import { HashRouter, Routes, Route, Navigate, Link, useLocation } from 'react-router-dom';
import { Shield, User, LayoutDashboard, ScanLine, Sparkles, Lock, BarChart3, Building2, LogIn, ArrowRight } from 'lucide-react';
import AdminDashboard from './pages/AdminDashboard';
import MemberDashboard from './pages/MemberDashboard';
import ReceptionPopup from './pages/ReceptionPopup';
import AIAssistant from './pages/AIAssistant';
import { Member, Role, Organization } from './types';

// --- COMPONENTS ---

const Navigation = ({ user }: { user: Member | null }) => {
  const location = useLocation();
  const isActive = (path: string) => location.pathname.startsWith(path) ? "text-brand-600 font-bold" : "text-slate-500 hover:text-slate-700";
  const isAdmin = user?.role === Role.ADMIN;
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
      {isAdmin && (
        <Link to="/admin" className={`flex flex-col items-center p-2 ${isActive('/admin')}`}>
          <Shield size={24} />
          <span className="text-[10px] mt-1">Admin</span>
        </Link>
      )}
    </nav>
  );
};

const Header = ({ user, org }: { user: Member | null, org: Organization | null }) => {
  const isAdmin = user?.role === Role.ADMIN;
  const isStaff = user?.role === Role.STAFF;

  return (
    <header className="bg-slate-900 text-white p-4 shadow-lg sticky top-0 z-40 border-b border-brand-500">
      <div className="container mx-auto flex justify-between items-center">
        <div className="flex items-center space-x-2">
          <div className="bg-brand-600 p-1 rounded">
             <Shield className="text-white" size={20} />
          </div>
          <div className="flex flex-col">
              <h1 className="text-xl font-bold tracking-tight leading-none">ONEPASS</h1>
              <span className="text-[10px] text-brand-400 font-mono tracking-wider">
                {org ? org.name.toUpperCase() : 'SYSTEM'} v4.2
              </span>
          </div>
        </div>
        <div className="hidden md:flex space-x-6">
           {(isAdmin || isStaff) && (
              <Link to="/reception" className="hover:text-brand-300 transition-colors">Reception & Scan</Link>
           )}
           <Link to="/member" className="hover:text-brand-300 transition-colors">Member Portal</Link>
           <Link to="/assistant" className="hover:text-brand-300 transition-colors flex items-center gap-1"><Sparkles size={16}/> AI Concierge</Link>
           {isAdmin && (
             <Link to="/admin" className="text-yellow-400 font-bold hover:text-yellow-300 transition-colors flex items-center gap-1">
               <Lock size={14} /> Admin Console
             </Link>
           )}
        </div>
      </div>
    </header>
  );
}

// --- ROUTE GUARDS ---

const ProtectedAdminRoute = ({ user, children }: { user: Member | null, children: React.ReactNode }) => {
  if (!user || user.role !== Role.ADMIN) {
      return (
          <div className="flex flex-col items-center justify-center h-[80vh] text-center p-6">
              <Shield size={64} className="text-slate-300 mb-4" />
              <h1 className="text-2xl font-bold text-slate-800">Restricted Access</h1>
              <p className="text-slate-500 mt-2">You do not have clearance to access the Admin console.</p>
              <Link to="/member" className="mt-6 text-brand-600 hover:underline">Return to Member Portal</Link>
          </div>
      )
  }
  return <>{children}</>;
};

const ProtectedReceptionRoute = ({ user, children }: { user: Member | null, children: React.ReactNode }) => {
  if (!user || (user.role !== Role.ADMIN && user.role !== Role.STAFF)) {
      return (
          <div className="flex flex-col items-center justify-center h-[80vh] text-center p-6">
              <Lock size={64} className="text-slate-300 mb-4" />
              <h1 className="text-2xl font-bold text-slate-800">Staff Access Only</h1>
              <p className="text-slate-500 mt-2">Please login as Staff or Admin to access the scanner.</p>
              <Link to="/member" className="mt-6 text-brand-600 hover:underline">Login via Member Portal</Link>
          </div>
      )
  }
  return <>{children}</>;
};

// --- ORGANIZATION LOGIN FLOW ---

const OrgSelector = ({ setOrg }: { setOrg: (org: Organization) => void }) => {
  const [orgId, setOrgId] = useState('');
  const [isNew, setIsNew] = useState(false);
  const [orgName, setOrgName] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (orgId.trim().length < 3) return alert("Invalid Organization ID");
    
    // Simulate Org Fetch/Creation
    const newOrg: Organization = {
      id: orgId.toUpperCase().replace(/\s/g, '_'),
      name: isNew ? orgName : orgId.toUpperCase(),
      type: 'GENERAL'
    };
    
    // Persist to session for this demo
    localStorage.setItem('onepass_org', JSON.stringify(newOrg));
    setOrg(newOrg);
  };

  return (
    <div className="min-h-screen bg-slate-900 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-4xl flex flex-col md:flex-row">
         {/* Left Side: Brand */}
         <div className="md:w-1/2 bg-brand-600 p-12 text-white flex flex-col justify-center">
            <div className="mb-6 bg-white/20 w-16 h-16 rounded-2xl flex items-center justify-center backdrop-blur-lg">
               <Shield size={40} />
            </div>
            <h1 className="text-4xl font-black tracking-tight mb-4">Vanguard<br/>OnePass™</h1>
            <p className="text-brand-100 text-lg leading-relaxed opacity-90">
               The world's most advanced secure access and wallet system. Manage attendance, fines, and rewards in one unified platform.
            </p>
         </div>

         {/* Right Side: Form */}
         <div className="md:w-1/2 p-12 flex flex-col justify-center">
            <h2 className="text-2xl font-bold text-slate-800 mb-6 flex items-center">
               <Building2 className="mr-3 text-slate-400" />
               {isNew ? 'Register Organization' : 'Organization Login'}
            </h2>

            <form onSubmit={handleSubmit} className="space-y-4">
                {isNew && (
                  <div>
                    <label className="block text-sm font-bold text-slate-700 mb-1">Organization Name</label>
                    <input 
                      type="text" 
                      value={orgName} 
                      onChange={e => setOrgName(e.target.value)}
                      className="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none" 
                      placeholder="e.g. Tech Corp Inc."
                      required
                    />
                  </div>
                )}

                <div>
                  <label className="block text-sm font-bold text-slate-700 mb-1">
                     {isNew ? 'Create Org ID' : 'Organization ID'}
                  </label>
                  <input 
                    type="text" 
                    value={orgId} 
                    onChange={e => setOrgId(e.target.value)}
                    className="w-full p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none font-mono uppercase" 
                    placeholder="e.g. ORG_001"
                    required
                  />
                </div>

                <button className="w-full bg-slate-900 text-white p-4 rounded-xl font-bold hover:bg-slate-800 transition flex items-center justify-center group">
                   {isNew ? 'Create & Continue' : 'Access System'}
                   <ArrowRight size={18} className="ml-2 group-hover:translate-x-1 transition-transform" />
                </button>
            </form>

            <div className="mt-6 text-center">
               <button 
                 onClick={() => setIsNew(!isNew)}
                 className="text-sm text-brand-600 font-bold hover:underline"
               >
                 {isNew ? 'Already have an account? Login' : 'New Organization? Register here'}
               </button>
            </div>
         </div>
      </div>
    </div>
  );
};

// --- APP ROOT ---

export default function App() {
  const [currentUser, setCurrentUser] = useState<Member | null>(null);
  const [currentOrg, setCurrentOrg] = useState<Organization | null>(null);

  useEffect(() => {
    // Check for saved org
    const saved = localStorage.getItem('onepass_org');
    if (saved) setCurrentOrg(JSON.parse(saved));
  }, []);

  if (!currentOrg) {
    return <OrgSelector setOrg={setCurrentOrg} />;
  }

  return (
    <HashRouter>
      <div className="min-h-screen bg-slate-50 pb-24 md:pb-0 font-sans">
        <Header user={currentUser} org={currentOrg} />
        <main className="container mx-auto p-4">
          <Routes>
            <Route path="/" element={<Navigate to="/member" replace />} />
            <Route 
              path="/admin/*" 
              element={
                <ProtectedAdminRoute user={currentUser}>
                  <AdminDashboard user={currentUser} setUser={setCurrentUser} />
                </ProtectedAdminRoute>
              } 
            />
            <Route 
              path="/member/*" 
              element={<MemberDashboard user={currentUser} setUser={setCurrentUser} />} 
            />
            <Route 
              path="/assistant" 
              element={<AIAssistant user={currentUser} />} 
            />
            <Route 
              path="/reception" 
              element={
                <ProtectedReceptionRoute user={currentUser}>
                   <ReceptionPopup />
                </ProtectedReceptionRoute>
              } 
            />
          </Routes>
        </main>
        <Navigation user={currentUser} />
      </div>
    </HashRouter>
  );
}
