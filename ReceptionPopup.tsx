import React, { useState, useEffect } from 'react';
import { Scan, CheckCircle, XCircle, AlertTriangle, Clock } from 'lucide-react';
import { api } from '../services/api';
import { ScanResult, Status } from '../types';

export default function ReceptionPopup() {
  const [inputId, setInputId] = useState('');
  const [scanResult, setScanResult] = useState<ScanResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [autoFocus, setAutoFocus] = useState(true);

  // Auto-focus input on mount
  useEffect(() => {
    if (autoFocus) {
      const el = document.getElementById('scanInput');
      el?.focus();
    }
  }, [autoFocus, scanResult]);

  const handleScan = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!inputId.trim()) return;

    setLoading(true);
    setScanResult(null);
    try {
      const result = await api.processScan(inputId);
      setScanResult(result);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
      setInputId('');
    }
  };

  const getStatusColor = (status?: Status) => {
    switch (status) {
      case Status.ACTIVE: return 'bg-green-100 text-green-800 border-green-200';
      case Status.LATE: return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case Status.SUSPENDED: return 'bg-red-100 text-red-800 border-red-200';
      case Status.BLOCKED: return 'bg-slate-800 text-white border-slate-700';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="max-w-md mx-auto mt-6">
      <div className="bg-white rounded-xl shadow-xl overflow-hidden">
        <div className="bg-slate-900 p-6 text-center">
          <h2 className="text-2xl font-bold text-white mb-2">Reception Access</h2>
          <p className="text-slate-400 text-sm">Scan QR or Enter Member ID</p>
        </div>

        <div className="p-6">
          <form onSubmit={handleScan} className="mb-6 relative">
            <input
              id="scanInput"
              type="text"
              value={inputId}
              onChange={(e) => setInputId(e.target.value)}
              placeholder="Waiting for scan..."
              className="w-full text-center text-xl p-4 border-2 border-slate-200 rounded-lg focus:border-brand-500 focus:ring-4 focus:ring-brand-100 outline-none transition-all"
              autoComplete="off"
            />
            <div className="absolute right-4 top-4 text-slate-400">
              <Scan />
            </div>
          </form>

          {loading && (
            <div className="text-center py-8">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-500 mx-auto"></div>
              <p className="mt-4 text-slate-500">Verifying access...</p>
            </div>
          )}

          {scanResult && scanResult.member && (
            <div className={`text-center animate-fade-in`}>
              <div className={`inline-block p-4 rounded-full mb-4 ${scanResult.allowed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'}`}>
                {scanResult.allowed ? <CheckCircle size={48} /> : <XCircle size={48} />}
              </div>
              
              <h3 className="text-2xl font-bold text-slate-900 mb-1">{scanResult.member.name}</h3>
              <p className="text-slate-500 mb-4">{scanResult.member.id}</p>

              <div className="flex justify-center mb-6">
                <img 
                  src={scanResult.member.photoUrl} 
                  alt="Member" 
                  className="w-32 h-32 rounded-full object-cover border-4 border-slate-100 shadow-md"
                />
              </div>

              <div className="grid grid-cols-2 gap-4 mb-6">
                <div className={`p-3 rounded-lg border ${getStatusColor(scanResult.member.status as Status)}`}>
                  <div className="text-xs uppercase font-bold tracking-wider opacity-70">Status</div>
                  <div className="text-lg font-bold">{scanResult.member.status}</div>
                </div>
                <div className="p-3 rounded-lg border border-slate-200 bg-slate-50">
                  <div className="text-xs uppercase font-bold tracking-wider text-slate-500">Wallet</div>
                  <div className="text-lg font-bold text-slate-800">₦{scanResult.member.walletBalance?.toLocaleString()}</div>
                </div>
              </div>

              {!scanResult.allowed && (
                 <div className="bg-red-50 border border-red-100 text-red-700 p-3 rounded-lg text-sm font-medium">
                    ⛔ ACCESS DENIED: {scanResult.message || "Please contact admin."}
                 </div>
              )}
               {scanResult.allowed && scanResult.member.status === Status.LATE && (
                 <div className="bg-yellow-50 border border-yellow-100 text-yellow-700 p-3 rounded-lg text-sm font-medium flex items-center justify-center">
                    <Clock size={16} className="mr-2"/>
                    LATE ENTRY RECORDED
                 </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}