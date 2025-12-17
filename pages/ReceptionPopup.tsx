import React, { useState, useEffect } from 'react';
import { Scan, CheckCircle, XCircle, AlertTriangle, Clock, UserPlus, ShieldAlert, Key, LogOut, Search, Cpu } from 'lucide-react';
import { api } from '../services/api';
import { ScanResult, Status, Member, Visitor } from '../types';

export default function ReceptionPopup() {
  const [mode, setMode] = useState<'scan' | 'hardware'>('scan');
  const [inputId, setInputId] = useState('');
  const [scanResult, setScanResult] = useState<ScanResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [gateOpen, setGateOpen] = useState(false);

  // Auto-focus
  useEffect(() => {
    const el = document.getElementById('scanInput');
    el?.focus();
  }, [mode]);

  const handleScan = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!inputId.trim()) return;

    setLoading(true);
    setScanResult(null);
    setGateOpen(false);
    
    try {
      if (mode === 'scan') {
        const result = await api.processScan(inputId);
        setScanResult(result);
        if (result.allowed) triggerHardwareGate();
      } else if (mode === 'hardware') {
          // Simulate a hardware event payload
          const result = await api.processHardwareEvent({
              device_id: 'SIM_DEV_01',
              organization_id: 'ORG_CACENTRE_001',
              actor_type: 'MEMBER',
              actor_id: inputId,
              event_type: 'ENTRY',
              timestamp: new Date().toISOString()
          });
          setScanResult(result as any);
          if (result.allowed) triggerHardwareGate();
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
      setInputId('');
    }
  };
  
  const triggerHardwareGate = () => {
      setGateOpen(true);
      setTimeout(() => setGateOpen(false), 3000); 
  };

  return (
    <div className="max-w-md mx-auto mt-6 space-y-4">
      
      {/* Hardware Status Indicator */}
      <div className={`rounded-xl p-4 shadow-sm border transition-all duration-300 flex items-center justify-between ${gateOpen ? 'bg-green-600 border-green-700' : 'bg-red-600 border-red-700'}`}>
          <div className="flex items-center text-white">
             {gateOpen ? <Key className="mr-3" size={24}/> : <ShieldAlert className="mr-3" size={24}/>}
             <div>
                 <h3 className="font-bold text-lg">{gateOpen ? 'GATE UNLOCKED' : 'SECURE LOCK ENGAGED'}</h3>
                 <p className="text-white/80 text-xs">{gateOpen ? 'Pass Through Now' : 'Awaiting Valid Credentials'}</p>
             </div>
          </div>
          <div className={`w-4 h-4 rounded-full ${gateOpen ? 'bg-green-300 animate-ping' : 'bg-red-800'}`}></div>
      </div>

      <div className="bg-white rounded-xl shadow-xl overflow-hidden">
        <div className="bg-slate-900 p-6 text-center relative">
          <div className="flex justify-center space-x-4 mb-4">
             <button 
                onClick={() => { setMode('scan'); setScanResult(null); }}
                className={`px-4 py-1 rounded-full text-sm font-bold transition-all ${mode === 'scan' ? 'bg-white text-brand-900' : 'text-white/50 hover:text-white'}`}
             >
                App Scan
             </button>
             <button 
                onClick={() => { setMode('hardware'); setScanResult(null); }}
                className={`px-4 py-1 rounded-full text-sm font-bold transition-all ${mode === 'hardware' ? 'bg-blue-500 text-white' : 'text-white/50 hover:text-white'}`}
             >
                Hardware Sim
             </button>
          </div>
          
          <h2 className="text-2xl font-bold text-white mb-2">{mode === 'scan' ? 'Access Control' : 'Device Simulation'}</h2>
          <p className="text-slate-400 text-sm">{mode === 'scan' ? 'Scan QR or Enter ID' : 'Simulate RFID/NFC Tag Input'}</p>
        </div>

        <div className="p-6">
          <form onSubmit={handleScan} className="mb-6 relative">
            <input
              id="scanInput"
              type="text"
              value={inputId}
              onChange={(e) => setInputId(e.target.value)}
              placeholder={mode === 'scan' ? "Scan ID..." : "Simulate Tag ID..."}
              className={`w-full text-center text-xl p-4 border-2 rounded-lg outline-none transition-all ${mode === 'hardware' ? 'border-blue-100 focus:border-blue-500 focus:ring-blue-100' : 'border-slate-200 focus:border-brand-500 focus:ring-brand-100'}`}
              autoComplete="off"
            />
            <div className="absolute right-4 top-4 text-slate-400">
              {mode === 'scan' ? <Scan /> : <Cpu />}
            </div>
          </form>

          {loading && (
            <div className="text-center py-8">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-500 mx-auto"></div>
              <p className="mt-4 text-slate-500">Processing...</p>
            </div>
          )}

          {scanResult && (
            <div className={`text-center animate-fade-in`}>
              <div className={`inline-block p-4 rounded-full mb-4 ${scanResult.allowed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'}`}>
                {scanResult.allowed ? <CheckCircle size={48} /> : <XCircle size={48} />}
              </div>
              
              <h3 className="text-2xl font-bold text-slate-900 mb-1">{scanResult.member.name}</h3>
              <p className="text-slate-500 mb-4">{scanResult.member.id}</p>

              {!scanResult.allowed && (
                 <div className="bg-red-50 border border-red-100 text-red-700 p-3 rounded-lg text-sm font-medium">
                    ⛔ ACCESS DENIED: {scanResult.message || "Please contact admin."}
                 </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
