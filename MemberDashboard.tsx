import React, { useState, useEffect } from 'react';
import { Wallet, AlertCircle, History, LogOut, Lock, User, Sparkles } from 'lucide-react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { Member, Transaction } from '../types';

interface MemberDashboardProps {
  user: Member | null;
  setUser: (member: Member | null) => void;
}

export default function MemberDashboard({ user, setUser }: MemberDashboardProps) {
  const [step, setStep] = useState<1 | 2 | 3>(1); // 1: ID, 2: Password, 3: Dashboard
  const [memberId, setMemberId] = useState('');
  const [password, setPassword] = useState('');
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [error, setError] = useState('');

  // Auto-login if user exists in prop (persisted in App state)
  useEffect(() => {
    if (user) {
      setStep(3);
      setMemberId(user.id);
      loadHistory(user.id);
    } else {
      setStep(1);
    }
  }, [user]);

  const loadHistory = async (id: string) => {
    try {
      const tx = await api.getHistory(id);
      setTransactions(tx);
    } catch (e) {
      console.error("Failed to load history", e);
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
        setUser(res.member); // Set global state
        // Step 3 set via useEffect
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

  if (step === 1) {
    return (
      <div className="max-w-sm mx-auto mt-10 animate-fade-in">
        <div className="bg-white p-8 rounded-xl shadow-lg">
          <div className="text-center mb-6">
             <div className="bg-brand-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <User className="text-brand-600" size={32} />
             </div>
             <h2 className="text-2xl font-bold text-slate-800">Member Login</h2>
             <p className="text-slate-500">Enter your Member ID to continue</p>
          </div>
          <form onSubmit={handleIdSubmit}>
            <input
              type="text"
              value={memberId}
              onChange={(e) => setMemberId(e.target.value.toUpperCase())}
              className="w-full p-3 border border-slate-300 rounded-lg mb-4 focus:ring-2 focus:ring-brand-500 outline-none"
              placeholder="e.g. VG001"
              required
            />
            <button type="submit" className="w-full bg-brand-600 text-white p-3 rounded-lg font-bold hover:bg-brand-700 transition">
              Next
            </button>
          </form>
        </div>
      </div>
    );
  }

  if (step === 2) {
    return (
      <div className="max-w-sm mx-auto mt-10 animate-fade-in">
        <div className="bg-white p-8 rounded-xl shadow-lg">
          <div className="text-center mb-6">
             <div className="bg-brand-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <Lock className="text-brand-600" size={32} />
             </div>
             <h2 className="text-2xl font-bold text-slate-800">Secure Access</h2>
             <p className="text-slate-500">Enter your wallet password for {memberId}</p>
          </div>
          <form onSubmit={handleLogin}>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full p-3 border border-slate-300 rounded-lg mb-4 focus:ring-2 focus:ring-brand-500 outline-none"
              placeholder="Password"
              required
            />
            {error && <div className="text-red-500 text-sm mb-4 bg-red-50 p-2 rounded">{error}</div>}
            <div className="flex gap-2">
                <button type="button" onClick={() => setStep(1)} className="flex-1 bg-slate-100 text-slate-700 p-3 rounded-lg font-bold hover:bg-slate-200 transition">
                Back
                </button>
                <button type="submit" className="flex-1 bg-brand-600 text-white p-3 rounded-lg font-bold hover:bg-brand-700 transition">
                Login
                </button>
            </div>
          </form>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto animate-fade-in">
      {/* Profile Header */}
      <div className="bg-white rounded-xl shadow-sm p-6 mb-6 flex items-center justify-between">
        <div className="flex items-center space-x-4">
            <img src={user?.photoUrl} alt="Profile" className="w-16 h-16 rounded-full object-cover border-2 border-slate-200" />
            <div>
                <h1 className="text-xl font-bold text-slate-900">{user?.name}</h1>
                <span className="text-sm text-slate-500">{user?.role} • {user?.status}</span>
            </div>
        </div>
        <button onClick={handleLogout} className="text-slate-400 hover:text-red-500 transition">
            <LogOut />
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {/* Wallet Card */}
        <div className="bg-gradient-to-br from-brand-600 to-brand-800 rounded-xl p-6 text-white shadow-lg relative overflow-hidden">
            <div className="absolute top-0 right-0 p-4 opacity-10">
                <Wallet size={100} />
            </div>
            <p className="text-brand-100 text-sm font-medium mb-1">Wallet Balance</p>
            <h2 className="text-3xl font-bold mb-4">₦{user?.walletBalance.toLocaleString()}</h2>
            <div className="flex gap-2">
                 <button className="bg-white/20 hover:bg-white/30 text-white text-xs px-3 py-2 rounded backdrop-blur-sm transition">
                    Request Withdrawal
                 </button>
            </div>
        </div>

        {/* Fines Card */}
        <div className="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
             <div className="flex items-center justify-between mb-2">
                <p className="text-slate-500 text-sm font-medium">Outstanding Fines</p>
                <AlertCircle className="text-orange-500" size={20} />
             </div>
             <h2 className="text-3xl font-bold text-slate-800 mb-2">₦{user?.outstandingFines.toLocaleString()}</h2>
             {user?.outstandingFines! > 0 ? (
                <Link to="/assistant" className="text-xs bg-orange-50 text-orange-600 px-2 py-1 rounded inline-flex items-center hover:bg-orange-100">
                   Ask AI about this <Sparkles size={10} className="ml-1"/>
                </Link>
             ) : (
               <span className="text-xs text-slate-400">Clean Record</span>
             )}
        </div>

        {/* Points Card */}
        <div className="bg-white rounded-xl p-6 shadow-sm border border-slate-200">
             <div className="flex items-center justify-between mb-2">
                <p className="text-slate-500 text-sm font-medium">Reward Points</p>
                <div className="text-yellow-500">★</div>
             </div>
             <h2 className="text-3xl font-bold text-slate-800">{user?.rewardPoints}</h2>
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
                    {transactions.length === 0 && (
                        <tr>
                            <td colSpan={4} className="p-8 text-center text-slate-400">No transactions found</td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
      </div>
    </div>
  );
}