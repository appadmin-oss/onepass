import React, { useState, useEffect } from 'react';
import { Wallet, AlertCircle, History, LogOut, Lock, User, Sparkles, QrCode, Settings, X, Lightbulb, Map, ChevronRight, CheckCircle2, Shield } from 'lucide-react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { generateMemberInsights } from '../services/ai';
import { Member, Transaction, Session, JourneyItem } from '../types';

interface MemberDashboardProps {
  user: Member | null;
  setUser: (member: Member | null) => void;
}

export default function MemberDashboard({ user, setUser }: MemberDashboardProps) {
  const [step, setStep] = useState<1 | 2 | 3>(1);
  const [memberId, setMemberId] = useState('');
  const [password, setPassword] = useState('');
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [insight, setInsight] = useState('');
  const [error, setError] = useState('');
  
  // New State
  const [sessions, setSessions] = useState<Session[]>([]);
  const [journey, setJourney] = useState<JourneyItem[]>([]);
  const [isWalletLocked, setIsWalletLocked] = useState(true);

  // Modals
  const [showDigitalId, setShowDigitalId] = useState(false);
  const [showWithdrawal, setShowWithdrawal] = useState(false);
  const [withdrawalAmount, setWithdrawalAmount] = useState('');

  useEffect(() => {
    if (user) {
      setStep(3);
      setMemberId(user.id);
      loadDashboardData(user);
    } else {
      setStep(1);
    }
  }, [user]);

  const loadDashboardData = async (userData: Member) => {
    try {
      const [tx, sess, journ] = await Promise.all([
          api.getHistory(userData.id),
          api.getSessions(),
          api.getJourney(userData.id)
      ]);
      setTransactions(tx);
      setSessions(sess || []);
      setJourney(journ || []);
      
      const aiText = await generateMemberInsights(userData, tx);
      setInsight(aiText);
      
      setTimeout(() => {
          if (userData.outstandingFines > 0) {
              setIsWalletLocked(true);
          } else {
              setIsWalletLocked(false);
              api.acknowledgeDashboard(userData.id);
          }
      }, 1500);

    } catch (e) {
      console.error("Failed to load dashboard data", e);
    }
  };

  const handleIdSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!memberId) return;
    setStep(2);
    setError('');
  };

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    try {
      const res = await api.login(memberId, password);
      if (res.success) {
        setUser(res.member);
      } else {
        setError(res.error || 'Invalid credentials');
      }
    } catch (err) {
      setError('Login failed. Try again.');
    }
  };

  const handleLogout = () => {
    setUser(null);
    setMemberId('');
    setPassword('');
    setStep(1);
  };

  const handleWithdrawalSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user) return;
    const amount = parseFloat(withdrawalAmount);
    if (amount <= 0 || amount > user.walletBalance) {
        alert("Invalid amount.");
        return;
    }
    await api.requestWithdrawal(user.id, amount);
    setShowWithdrawal(false);
    setWithdrawalAmount('');
    alert("Withdrawal request submitted.");
  };

  // --- RENDER LOGIN FLOW ---
  if (step === 1) {
    return (
      <div className="flex items-center justify-center min-h-[70vh] p-4">
        <div className="w-full max-w-sm">
            <div className="text-center mb-10">
                <h1 className="text-3xl font-black text-slate-900 tracking-tight mb-2">Vanguard OnePass</h1>
                <p className="text-slate-500">Secure Access Platform</p>
            </div>

            <div className="bg-white p-8 rounded-2xl shadow-xl border border-slate-100 relative overflow-hidden">
                <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-400 to-brand-600"></div>
                
                <h2 className="text-lg font-bold mb-6 text-slate-800">Select Login Method</h2>
                
                <button 
                  onClick={() => document.getElementById('manual-login')?.classList.remove('hidden')}
                  className="w-full bg-brand-600 hover:bg-brand-700 text-white p-4 rounded-xl font-bold flex items-center justify-center transition-all shadow-lg shadow-brand-200 mb-4 group"
                >
                    <Shield className="mr-3 group-hover:scale-110 transition-transform" fill="currentColor" size={20} />
                    Login with CACENTRE
                </button>

                <div className="grid grid-cols-2 gap-3 mb-6">
                    <button className="p-3 border border-slate-200 rounded-xl flex flex-col items-center justify-center hover:bg-slate-50 text-slate-600">
                        <QrCode size={24} className="mb-1 text-slate-400"/>
                        <span className="text-xs font-bold">Scan QR</span>
                    </button>
                    <button className="p-3 border border-slate-200 rounded-xl flex flex-col items-center justify-center hover:bg-slate-50 text-slate-600">
                        <Lock size={24} className="mb-1 text-slate-400"/>
                        <span className="text-xs font-bold">Hardware</span>
                    </button>
                </div>

                {/* Hidden by default until clicked */}
                <form id="manual-login" onSubmit={handleIdSubmit} className="hidden animate-fade-in border-t border-slate-100 pt-6">
                    <label className="block text-xs font-bold uppercase text-slate-400 mb-1">Member ID</label>
                    <input
                    type="text"
                    value={memberId}
                    onChange={(e) => setMemberId(e.target.value.toUpperCase())}
                    className="w-full p-3 border border-slate-300 rounded-lg mb-4 focus:ring-2 focus:ring-brand-500 outline-none font-mono"
                    placeholder="e.g. VG001"
                    required
                    />
                    <button type="submit" className="w-full bg-slate-900 text-white p-3 rounded-lg font-bold hover:bg-slate-800 transition">
                    Continue
                    </button>
                </form>

                <div className="mt-6 text-center flex justify-center items-center gap-2">
                    <span className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <p className="text-xs text-slate-400">System Online • CACENTRE</p>
                </div>
            </div>
        </div>
      </div>
    );
  }

  if (step === 2) {
    return (
      <div className="flex items-center justify-center min-h-[70vh] p-4">
        <div className="w-full max-w-sm bg-white p-8 rounded-2xl shadow-xl border border-slate-100 relative">
            <div className="text-center mb-6">
                <div className="inline-block p-3 rounded-full bg-brand-50 mb-3">
                    <Shield className="text-brand-600" size={32} />
                </div>
                <h2 className="text-xl font-bold text-slate-800">Verify Identity</h2>
                <div className="inline-block bg-slate-100 rounded px-2 py-1 mt-2 text-xs font-mono text-slate-600">
                    {memberId}
                </div>
            </div>
            <form onSubmit={handleLogin}>
                <input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="w-full p-3 border border-slate-300 rounded-lg mb-4 focus:ring-2 focus:ring-brand-500 outline-none text-center tracking-widest"
                    placeholder="••••••••"
                    required
                    autoFocus
                />
                {error && <div className="text-red-500 text-sm mb-4 bg-red-50 p-3 rounded-lg text-center font-medium">{error}</div>}
                <div className="flex gap-2">
                    <button type="button" onClick={() => setStep(1)} className="w-1/3 bg-slate-100 text-slate-700 p-3 rounded-lg font-bold hover:bg-slate-200 transition">
                    Back
                    </button>
                    <button type="submit" className="flex-1 bg-brand-600 text-white p-3 rounded-lg font-bold hover:bg-brand-700 transition">
                    Authenticate
                    </button>
                </div>
            </form>
        </div>
      </div>
    );
  }

  // --- RENDER DASHBOARD ---
  const activeSession = sessions.find(s => s.isActive);

  return (
    <div className="max-w-4xl mx-auto animate-fade-in relative pb-20">
      
      {/* Modals */}
      {showDigitalId && (
          <div className="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4" onClick={() => setShowDigitalId(false)}>
              <div className="bg-white rounded-2xl overflow-hidden w-full max-w-sm shadow-2xl relative" onClick={e => e.stopPropagation()}>
                  <div className="bg-brand-900 p-4 text-white text-center">
                      <h2 className="font-bold text-lg tracking-wider">CACENTRE PASS</h2>
                  </div>
                  <div className="p-8 flex flex-col items-center">
                      <img src={user?.photoUrl} className="w-24 h-24 rounded-full border-4 border-brand-100 mb-4 object-cover" />
                      <h3 className="text-2xl font-bold text-slate-900">{user?.name}</h3>
                      <p className="text-brand-600 font-medium mb-6">{user?.role} • {user?.id}</p>
                      <div className="bg-white p-2 border-2 border-slate-100 rounded-xl">
                         <img src={`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${user?.id}`} alt="QR Code" className="w-48 h-48" />
                      </div>
                  </div>
                  <button onClick={() => setShowDigitalId(false)} className="absolute top-2 right-2 text-white/80 hover:text-white">
                      <X size={24} />
                  </button>
              </div>
          </div>
      )}

      {showWithdrawal && (
          <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onClick={() => setShowWithdrawal(false)}>
              <div className="bg-white rounded-xl p-6 w-full max-w-sm shadow-xl" onClick={e => e.stopPropagation()}>
                  <h3 className="text-lg font-bold mb-4 flex items-center"><Wallet className="mr-2"/> Request Withdrawal</h3>
                  <form onSubmit={handleWithdrawalSubmit}>
                      <label className="block text-sm font-medium mb-1">Amount (₦)</label>
                      <input 
                         type="number" 
                         value={withdrawalAmount}
                         onChange={e => setWithdrawalAmount(e.target.value)}
                         className="w-full p-2 border border-slate-300 rounded mb-4"
                         required
                         max={user?.walletBalance}
                         min={100}
                      />
                      <button className="w-full bg-green-600 text-white p-2 rounded font-bold">Submit Request</button>
                  </form>
              </div>
          </div>
      )}

      {/* Header */}
      <div className="bg-white rounded-xl shadow-sm p-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4 border border-slate-200">
        <div className="flex items-center space-x-4 w-full">
            <img src={user?.photoUrl} alt="Profile" className="w-16 h-16 rounded-full object-cover border-2 border-slate-200" />
            <div className="flex-1">
                <div className="flex items-center gap-2 mb-1">
                    <h1 className="text-xl font-bold text-slate-900">{user?.name}</h1>
                    <span className="bg-brand-100 text-brand-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-brand-200">CACENTRE</span>
                </div>
                <span className="text-sm text-slate-500">{user?.role} • {activeSession ? `${activeSession.name} Session` : 'Break'}</span>
            </div>
            <div className="flex gap-2">
                <button onClick={() => setShowDigitalId(true)} className="p-2 bg-brand-50 text-brand-600 rounded-lg hover:bg-brand-100 transition" title="Show ID">
                    <QrCode size={20} />
                </button>
                <button onClick={handleLogout} className="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition" title="Logout">
                    <LogOut size={20} />
                </button>
            </div>
        </div>
      </div>

      {/* AI Intelligence Briefing */}
      <div className="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-xl p-4 mb-6 shadow-md text-white flex items-start space-x-3">
          <div className="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
             <Lightbulb className="text-yellow-300" size={24} />
          </div>
          <div>
             <h3 className="font-bold text-sm text-violet-100 uppercase tracking-wider mb-1">Daily Briefing</h3>
             <p className="text-lg font-medium leading-snug">
                {insight || "Analyzing your profile..."}
             </p>
          </div>
      </div>

      {/* Journey Tracker (CACENTRE Feature) */}
      <div className="bg-white rounded-xl p-6 shadow-sm border border-slate-200 mb-6">
          <div className="flex items-center justify-between mb-4">
              <h3 className="font-bold flex items-center"><Map className="mr-2 text-brand-500"/> My Journey</h3>
              <span className="text-xs font-bold bg-brand-100 text-brand-700 px-2 py-1 rounded-full">{activeSession?.name} Session</span>
          </div>
          <div className="space-y-4">
              {journey.map(j => (
                  <div key={j.id} className="border border-slate-100 rounded-lg p-3 flex items-center justify-between">
                      <div className="flex items-center">
                          {j.status === 'Completed' ? <CheckCircle2 className="text-green-500 mr-3"/> : <div className="w-6 h-6 rounded-full border-2 border-slate-300 mr-3"></div>}
                          <div>
                              <p className="font-bold text-sm text-slate-800">{j.title}</p>
                              <p className="text-xs text-slate-500">{j.description}</p>
                          </div>
                      </div>
                      <div className="text-right">
                          <span className={`text-xs font-bold px-2 py-1 rounded ${j.status === 'Completed' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`}>{j.status}</span>
                      </div>
                  </div>
              ))}
          </div>
      </div>

      {/* Wallet Section (Locked until viewed/unlocked) */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div className={`bg-gradient-to-br from-brand-600 to-brand-800 rounded-xl p-6 text-white shadow-lg relative overflow-hidden group transition-all ${isWalletLocked ? 'grayscale opacity-90' : ''}`}>
            {isWalletLocked && (
                <div className="absolute inset-0 bg-black/60 z-10 flex flex-col items-center justify-center text-center p-4">
                    <Lock size={48} className="mb-2 text-slate-300"/>
                    <h3 className="font-bold text-lg">Wallet Locked</h3>
                    <p className="text-sm text-slate-300 mb-4">{user?.outstandingFines! > 0 ? "Pay fines to unlock" : "Reviewing Dashboard..."}</p>
                    {user?.outstandingFines === 0 && <div className="animate-spin w-6 h-6 border-2 border-white border-t-transparent rounded-full"></div>}
                </div>
            )}
            <p className="text-brand-100 text-sm font-medium mb-1">Wallet Balance</p>
            <h2 className="text-3xl font-bold mb-4">₦{user?.walletBalance.toLocaleString()}</h2>
            <div className="flex gap-2">
                 <button disabled={isWalletLocked} onClick={() => setShowWithdrawal(true)} className="bg-white/20 hover:bg-white/30 disabled:opacity-50 text-white text-xs px-3 py-2 rounded backdrop-blur-sm transition font-bold">
                    Request Withdrawal
                 </button>
            </div>
        </div>

        <div className="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
             <div className="flex items-center justify-between mb-2">
                <p className="text-slate-500 text-sm font-medium">Outstanding Fines</p>
                <AlertCircle className="text-orange-500" size={20} />
             </div>
             <h2 className="text-3xl font-bold text-slate-800 mb-2">₦{user?.outstandingFines.toLocaleString()}</h2>
             {user?.outstandingFines! > 0 ? (
                <span className="text-xs text-red-600 font-bold">Auto-Lock Enabled</span>
             ) : (
               <span className="text-xs text-slate-400">Clean Record</span>
             )}
        </div>
      </div>

      {/* Transactions */}
      <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div className="p-4 border-b border-slate-100 flex items-center space-x-2">
            <History size={20} className="text-slate-400" />
            <h3 className="font-bold text-slate-800">Recent Transactions</h3>
        </div>
        <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 text-slate-500">
                    <tr>
                        <th className="p-4 font-medium">Date</th>
                        <th className="p-4 font-medium">Type</th>
                        <th className="p-4 font-medium">Description</th>
                        <th className="p-4 font-medium text-right">Amount</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {transactions.map((t) => (
                        <tr key={t.id} className="hover:bg-slate-50">
                            <td className="p-4 text-slate-600">{new Date(t.timestamp).toLocaleDateString()}</td>
                            <td className="p-4">
                                <span className={`px-2 py-1 rounded text-xs font-bold ${
                                    t.type === 'Credit' ? 'bg-green-100 text-green-700' :
                                    t.type === 'Debit' ? 'bg-red-100 text-red-700' :
                                    t.type === 'Fine' ? 'bg-orange-100 text-orange-700' :
                                    'bg-blue-100 text-blue-700'
                                }`}>
                                    {t.type}
                                </span>
                            </td>
                            <td className="p-4 text-slate-800">{t.description}</td>
                            <td className={`p-4 text-right font-bold ${t.amount < 0 ? 'text-red-600' : 'text-green-600'}`}>
                                {t.amount < 0 ? '-' : '+'}₦{Math.abs(t.amount).toLocaleString()}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
      </div>
    </div>
  );
}
