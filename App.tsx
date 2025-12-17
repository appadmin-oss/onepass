import React, { useState } from 'react';
import { HashRouter, Routes, Route, Navigate, Link, useLocation } from 'react-router-dom';
import { Shield, User, LayoutDashboard, ScanLine, Sparkles, Lock } from 'lucide-react';
import AdminDashboard from './pages/AdminDashboard';
import MemberDashboard from './pages/MemberDashboard';
import ReceptionPopup from './pages/ReceptionPopup';
import AIAssistant from './pages/AIAssistant';
import { Member, Role } from './types';

interface NavigationProps {
  user: Member | null;
}

const Navigation = ({ user }: NavigationProps) => {
  const location = useLocation();
  const isActive = (path: string) => location.pathname.startsWith(path) ? "text-brand-600 font-bold" : "text-slate-500 hover:text-slate-700";
  const isAdmin = user?.role === Role.ADMIN;

  return (
    <nav className="fixed bottom-0 w-full bg-white border-t border-slate-200 p-2 pb-safe flex justify-around items-center md:hidden z-50">
      <Link to="/reception" className={`flex flex-col items-center p-2 ${isActive('/reception')}`}>
        <ScanLine size={24} />
        <span className="text-[10px] mt-1">Scan</span>
      </Link>
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

interface HeaderProps {
  user: Member | null;
}

const Header = ({ user }: HeaderProps) => {
  const isAdmin = user?.role === Role.ADMIN;

  return (
    <header className="bg-brand-900 text-white p-4 shadow-lg sticky top-0 z-40">
      <div className="container mx-auto flex justify-between items-center">
        <div className="flex items-center space-x-2">
          <Shield className="text-brand-500" />
          <h1 className="text-xl font-bold tracking-tight">VANGUARD <span className="text-brand-500">ONEPASS™</span></h1>
        </div>
        <div className="hidden md:flex space-x-6">
           <Link to="/reception" className="hover:text-brand-300 transition-colors">Reception</Link>
           <Link to="/member" className="hover:text-brand-300 transition-colors">Member Portal</Link>
           <Link to="/assistant" className="hover:text-brand-300 transition-colors flex items-center gap-1"><Sparkles size={16}/> AI Concierge</Link>
           {isAdmin && (
             <Link to="/admin" className="text-yellow-400 font-bold hover:text-yellow-300 transition-colors flex items-center gap-1">
               <Lock size={14} /> God Mode
             </Link>
           )}
        </div>
      </div>
    </header>
  );
}

const ProtectedAdminRoute = ({ user, children }: { user: Member | null, children: React.ReactNode }) => {
  if (!user || user.role !== Role.ADMIN) {
    // If not admin, you can redirect or show a "Restricted" page.
    // However, to allow "Admin Login" flow, we might pass through to the dashboard 
    // and let the dashboard handle the "Login" state if user is null.
    // BUT, if user is logged in as MEMBER, they should be blocked.
    if (user && user.role !== Role.ADMIN) {
        return (
            <div className="flex flex-col items-center justify-center h-[80vh] text-center p-6">
                <Shield size={64} className="text-slate-300 mb-4" />
                <h1 className="text-2xl font-bold text-slate-800">Restricted Access</h1>
                <p className="text-slate-500 mt-2">You do not have clearance to access the God Mode console.</p>
                <Link to="/member" className="mt-6 text-brand-600 hover:underline">Return to Member Portal</Link>
            </div>
        )
    }
  }
  return <>{children}</>;
};

export default function App() {
  const [currentUser, setCurrentUser] = useState<Member | null>(null);

  return (
    <HashRouter>
      <div className="min-h-screen bg-slate-50 pb-24 md:pb-0 font-sans">
        <Header user={currentUser} />
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
            <Route path="/reception" element={<ReceptionPopup />} />
          </Routes>
        </main>
        <Navigation user={currentUser} />
      </div>
    </HashRouter>
  );
}