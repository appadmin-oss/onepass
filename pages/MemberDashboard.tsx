
import React, { useState, useEffect } from 'react';
import { 
  Wallet, Clock, Trophy, HardDrive, History, LogOut, QrCode, 
  Sparkles, ExternalLink, Shield, AlertCircle, CheckCircle2, 
  Layers, Star, Calendar, ArrowRight
} from 'lucide-react';
import { api } from '../services/api';
import { generateMemberInsights } from '../services/ai';
import { Member, JourneyItem, Session } from '../types';

export default function MemberDashboard({ user, setUser }: { user: Member | null; setUser: (m: Member | null) => void }) {
  const [session, setSession] = useState<Session | null>(null);
  const [journey, setJourney] = useState<JourneyItem[]>([]);
  const [insight, setInsight] = useState('');
  const [countdown, setCountdown] = useState('');
  const [showQr, setShowQr] = useState(false);
  const [walletLocked, setWalletLocked] = useState(true);

  useEffect(() => {
    if (user) {
      loadData();
      const timer = setInterval(updateCountdown, 1000);
      return () => clearInterval(timer);
    }
  }, [user]);

  const loadData = async () => {
    if (!user) return;
    const sess = api.getCurrentSession();
    const jrny = await api.getMemberJourney(user.id);
    const ins = await generateMemberInsights(user, []);
    
    setSession(sess);
    setJourney(jrny);
    setInsight(ins);
    
    // Check if wallet should be unlocked
    setWalletLocked(api.isWalletLocked(user));
  };

  const updateCountdown = () => {
    if (!user?.fineDeadline) return;
    const diff = new Date(user.fineDeadline).getTime() - new Date().getTime();
    if (diff <= 0) {
      setCountdown("LOCK EXECUTED");
    } else {
      const h = Math.floor(diff / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      setCountdown(`${h}h ${m}m ${s}s`);
    }
  };

  const unlockWallet = async () => {
    if (!user) return;
    await api.recordDashboardView(user.id);
    setWalletLocked(false);
  };

  if (!user) return null;

  return (
    <div className="max-w-4xl mx-auto space-y-6 pb-20 animate-fade-in">
      {/* Session Progress Header */}
      <div className="bg-slate-900 p-8 rounded-[3rem] text-white shadow-2xl relative overflow-hidden">
        <div className="absolute top-0 right-0 p-8 opacity-10"><Layers size={120} /></div>
        <div className="relative z-10 flex flex-col md:flex-row justify-between items-end gap-6">
           <div className="space-y-2">
              <div className="flex items-center gap-3">
                 <div className="bg-brand-500 p-2 rounded-xl"><Calendar size={20} /></div>
                 <h2 className="text-3xl font-black tracking-tighter">{session?.type} Session {session?.year}</h2>
              </div>
              <p className="text-slate-400 font-bold uppercase tracking-[0.2em] text-[10px]">Current Organizational Phase</p>
           </div>
           <div className="w-full md:w-64 text-right">
              <div className="flex justify-between items-end mb-2">
                 <span className="text-[10px] font-black text-slate-400 uppercase">Hub Completion</span>
                 <span className="text-2xl font-black">{user.sessionProgress}%</span>
              </div>
              <div className="w-full h-3 bg-white/10 rounded-full overflow-hidden">
                 <div className="h-full bg-brand-500 transition-all duration-1000" style={{ width: `${user.sessionProgress}%` }}></div>
              </div>
           </div>
        </div>
      </div>

      {/* Grid: Identity & Analytics */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
         {/* Identity Card */}
         <div className="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100 flex flex-col items-center text-center">
            <img src={user.photoUrl} className="w-32 h-32 rounded-[2.5rem] object-cover ring-8 ring-slate-50 mb-6 shadow-xl" />
            <h3 className="text-2xl font-black text-slate-900 tracking-tight">{user.name}</h3>
            <span className="bg-brand-50 text-brand-600 px-4 py-1 rounded-full text-[10px] font-black uppercase mt-2 tracking-widest">{user.id}</span>
            <button onClick={() => setShowQr(true)} className="mt-8 p-4 bg-slate-900 text-white rounded-2xl hover:bg-brand-600 transition-all">
               <QrCode size={24} />
            </button>
         </div>

         {/* Journey Tracker */}
         <div className="lg:col-span-2 bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100 space-y-6">
            <h3 className="text-xl font-black flex items-center gap-3"><Star className="text-yellow-500" /> Member Journey</h3>
            <div className="space-y-4">
               {journey.map(item => (
                 <div key={item.id} className="flex items-center justify-between p-5 bg-slate-50 rounded-3xl group hover:bg-white hover:shadow-lg transition-all border border-transparent hover:border-slate-100">
                    <div className="flex items-center gap-4">
                       <div className={`p-3 rounded-2xl ${item.status === 'Approved' ? 'bg-green-100 text-green-600' : 'bg-slate-200 text-slate-400'}`}>
                          {item.status === 'Approved' ? <CheckCircle2 size={20} /> : <Clock size={20} />}
                       </div>
                       <div>
                          <p className="font-bold text-slate-900">{item.title}</p>
                          <p className="text-[10px] text-slate-400 uppercase font-black">{item.scope} Goal</p>
                       </div>
                    </div>
                    <span className="text-[10px] font-black text-brand-600">+{item.points} PTS</span>
                 </div>
               ))}
            </div>
         </div>
      </div>

      {/* Wallet Access Guard Area */}
      {walletLocked ? (
        <div className="bg-brand-600 p-10 rounded-[3rem] text-white shadow-2xl text-center space-y-6 relative overflow-hidden">
           <div className="absolute top-0 right-0 p-10 opacity-10"><Shield size={160} /></div>
           <h3 className="text-3xl font-black tracking-tighter">Liquid Wallet Encrypted</h3>
           <p className="text-brand-100 font-medium max-w-md mx-auto">
             Organizational Protocol: Dashboard and Journey metrics must be reviewed before wallet unlock.
           </p>
           <button onClick={unlockWallet} className="px-10 py-5 bg-white text-brand-600 rounded-[2rem] font-black text-lg shadow-xl hover:scale-105 transition-all flex items-center justify-center gap-3 mx-auto">
             <Shield size={24} /> Authorize Unlock
           </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 animate-scale-in">
           {/* Liquid Wallet */}
           <div className="bg-white p-10 rounded-[3rem] shadow-sm border border-slate-100 relative overflow-hidden">
              <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Available Balance</p>
              <h4 className="text-5xl font-black text-slate-900 tracking-tighter">₦{user.walletBalance.toLocaleString()}</h4>
              <div className="mt-8 flex gap-3">
                 <button className="flex-1 p-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-brand-600">Withdraw</button>
                 <button className="p-4 bg-slate-50 text-slate-400 rounded-2xl hover:bg-slate-100"><History size={20}/></button>
              </div>
           </div>

           {/* Cloud Hub */}
           <div className="bg-slate-50 p-10 rounded-[3rem] border border-slate-200 relative overflow-hidden group">
              <div className="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity"><HardDrive size={150}/></div>
              <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Vanguard Cloud</p>
              <h4 className="text-2xl font-black text-slate-800 mb-6">CACENTRE Drive</h4>
              <button onClick={() => window.open('https://drive.google.com', '_blank')} className="w-full p-4 bg-white border border-slate-200 rounded-2xl font-black text-xs uppercase tracking-widest flex items-center justify-center gap-3 hover:bg-brand-600 hover:text-white hover:border-transparent transition-all">
                 Open Repository <ExternalLink size={16} />
              </button>
           </div>
        </div>
      )}

      {/* Active Fines Banner */}
      {user.outstandingFines > 0 && (
        <div className="bg-red-600 p-8 rounded-[3rem] text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl animate-pulse">
           <div className="flex items-center gap-6">
              <div className="bg-white/20 p-4 rounded-3xl"><AlertCircle size={32} /></div>
              <div>
                 <h4 className="text-2xl font-black tracking-tighter">Fine Lockdown Pending</h4>
                 <p className="text-red-100 text-xs font-bold uppercase tracking-widest">Protocol Failure: ₦{user.outstandingFines.toLocaleString()}</p>
              </div>
           </div>
           <div className="text-center md:text-right">
              <p className="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Time to Auto-Lock</p>
              <p className="text-4xl font-black font-mono">{countdown}</p>
           </div>
        </div>
      )}

      {/* QR Passport Modal */}
      {showQr && (
        <div className="fixed inset-0 bg-slate-950/95 z-50 flex items-center justify-center p-6 backdrop-blur-xl" onClick={() => setShowQr(false)}>
           <div className="bg-white p-12 rounded-[4rem] w-full max-w-sm text-center animate-scale-in" onClick={e => e.stopPropagation()}>
              <h3 className="text-3xl font-black text-slate-900 tracking-tighter mb-10">Hub Passport</h3>
              <div className="bg-slate-50 p-8 rounded-[3rem] inline-block border-4 border-slate-100 mb-10">
                 <img src={`https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${btoa(JSON.stringify({id: user.id, org: user.organizationId}))}`} alt="QR" className="w-56 h-56" />
              </div>
              <p className="text-slate-400 text-xs font-black uppercase tracking-widest">Scan at any Mesh Node</p>
           </div>
        </div>
      )}

      <button onClick={() => setUser(null)} className="w-full p-10 text-slate-400 font-black text-xs uppercase tracking-[0.3em] hover:text-red-600 transition-colors">Terminate Hub Session</button>
    </div>
  );
}
