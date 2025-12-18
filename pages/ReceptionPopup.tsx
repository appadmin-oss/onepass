
import React, { useState, useEffect, useRef } from 'react';
import { Scan, ShieldAlert, Key, RefreshCw, QrCode, Camera, X, UserPlus, LogOut, CheckCircle2 } from 'lucide-react';
import { Html5QrcodeScanner } from 'html5-qrcode';
import { api } from '../services/api';
import { ScanResult, Member } from '../types';

export default function ReceptionPopup() {
  const [inputId, setInputId] = useState('');
  const [scanResult, setScanResult] = useState<ScanResult | null>(null);
  const [loading, setLoading] = useState(false);
  const [isScanning, setIsScanning] = useState(false);
  const [gateOpen, setGateOpen] = useState(false);
  const [showVisitorForm, setShowVisitorForm] = useState(false);
  const [visitorData, setVisitorData] = useState({ name: '', hostId: '', purpose: '' });
  
  const scannerRef = useRef<Html5QrcodeScanner | null>(null);

  useEffect(() => {
    if (isScanning && !scannerRef.current) {
      scannerRef.current = new Html5QrcodeScanner(
        "qr-reader", 
        { fps: 10, qrbox: { width: 250, height: 250 } }, 
        /* verbose= */ false
      );
      scannerRef.current.render(onScanSuccess, onScanFailure);
    }

    return () => {
      if (scannerRef.current) {
        scannerRef.current.clear().catch(err => console.error("Scanner clear failed", err));
        scannerRef.current = null;
      }
    };
  }, [isScanning]);

  function onScanSuccess(decodedText: string) {
    handleVerify(decodedText);
    stopScanner();
  }

  function onScanFailure(error: any) {
    // Fail silently to keep UX smooth
  }

  const stopScanner = () => {
    setIsScanning(false);
    if (scannerRef.current) {
      scannerRef.current.clear().catch(e => console.error(e));
      scannerRef.current = null;
    }
  };

  const handleVerify = async (id: string) => {
    if (!id.trim()) return;
    setLoading(true);
    setScanResult(null);
    try {
      const result = await api.processScan(id);
      setScanResult(result);
      if (result.allowed) {
        setGateOpen(true);
        setTimeout(() => setGateOpen(false), 3000);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
      setInputId('');
    }
  };

  const handleVisitorSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      const members = await api.getAllMembers();
      const host = members.find(m => m.id === visitorData.hostId);
      if (!host) {
        alert("Invalid Host ID");
        return;
      }
      const visitor = await api.createVisitor({
        ...visitorData,
        hostName: host.name
      });
      alert(`Visitor ID Generated: ${visitor.id}`);
      setShowVisitorForm(false);
      setVisitorData({ name: '', hostId: '', purpose: '' });
      handleVerify(visitor.id);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-xl mx-auto space-y-6 pt-4 px-2">
      {/* Visual Access Banner */}
      <div className={`p-8 rounded-[2.5rem] shadow-2xl border transition-all duration-700 flex items-center justify-between ${gateOpen ? 'bg-green-600 border-green-400 scale-[1.02]' : 'bg-slate-950 border-slate-800'}`}>
        <div className="flex items-center text-white">
          <div className={`p-4 rounded-3xl mr-6 backdrop-blur-md ${gateOpen ? 'bg-white/20' : 'bg-slate-800'}`}>
            {gateOpen ? <Key className="animate-bounce" size={32}/> : <ShieldAlert size={32}/>}
          </div>
          <div>
            <h3 className="font-black text-2xl tracking-tighter">{gateOpen ? 'ACCESS GRANTED' : 'SECURE HUB'}</h3>
            <p className="text-sm font-medium opacity-60">
              {gateOpen ? 'System Core Unlocked' : 'Waiting for Passport Scan'}
            </p>
          </div>
        </div>
      </div>

      <div className="bg-white rounded-[3rem] shadow-2xl overflow-hidden border border-slate-100">
        <div className="bg-slate-50 p-6 flex justify-around border-b border-slate-100">
           <button onClick={() => { setIsScanning(!isScanning); setShowVisitorForm(false); }} className={`flex-1 mx-2 p-4 rounded-2xl font-black text-xs flex flex-col items-center gap-2 transition-all ${isScanning ? 'bg-red-100 text-red-600' : 'bg-brand-600 text-white shadow-lg'}`}>
              {isScanning ? <X size={20}/> : <Camera size={20}/>}
              {isScanning ? 'STOP SCAN' : 'START CAMERA'}
           </button>
           <button onClick={() => { setShowVisitorForm(!showVisitorForm); setIsScanning(false); }} className={`flex-1 mx-2 p-4 rounded-2xl font-black text-xs flex flex-col items-center gap-2 transition-all ${showVisitorForm ? 'bg-slate-900 text-white' : 'bg-white border-2 border-slate-100 text-slate-500 hover:bg-slate-50'}`}>
              <UserPlus size={20}/>
              VISITOR LOG
           </button>
        </div>

        <div className="p-8">
          {isScanning ? (
            <div id="qr-reader" className="w-full rounded-3xl overflow-hidden border-4 border-slate-100"></div>
          ) : showVisitorForm ? (
            <form onSubmit={handleVisitorSubmit} className="space-y-4 animate-slide-up">
               <h3 className="font-black text-slate-800 uppercase tracking-widest text-xs mb-4">Guest Registration</h3>
               <input type="text" placeholder="GUEST NAME" value={visitorData.name} onChange={e => setVisitorData({...visitorData, name: e.target.value})} className="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl outline-none font-bold" required />
               <input type="text" placeholder="HOST ID (e.g. VG001)" value={visitorData.hostId} onChange={e => setVisitorData({...visitorData, hostId: e.target.value.toUpperCase()})} className="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl outline-none font-bold" required />
               <input type="text" placeholder="PURPOSE OF VISIT" value={visitorData.purpose} onChange={e => setVisitorData({...visitorData, purpose: e.target.value})} className="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl outline-none font-bold" required />
               <button className="w-full bg-slate-900 text-white p-5 rounded-2xl font-black shadow-xl">ISSUE VISITOR PASS</button>
            </form>
          ) : (
            <div className="space-y-6">
              <div className="relative">
                <input
                  id="manualId"
                  type="text"
                  value={inputId}
                  onChange={(e) => setInputId(e.target.value.toUpperCase())}
                  placeholder="MEMBER OR VISITOR ID"
                  className="w-full text-center text-3xl p-8 bg-slate-50 border-4 border-slate-100 rounded-3xl outline-none focus:border-brand-500 focus:bg-white font-black text-brand-600 tracking-widest"
                />
              </div>
              <button onClick={() => handleVerify(inputId)} disabled={loading} className="w-full bg-slate-900 text-white p-6 rounded-3xl font-black text-xl hover:bg-brand-600 transition-all flex items-center justify-center gap-3">
                {loading ? <RefreshCw className="animate-spin" /> : <Scan size={24} />} VERIFY HUB ID
              </button>
            </div>
          )}

          {scanResult && !loading && (
            <div className="mt-8 p-6 bg-slate-50 rounded-3xl animate-slide-up border-2 border-slate-100">
              <div className="flex flex-col items-center">
                {scanResult.allowed ? (
                  <>
                    {scanResult.member ? (
                       <div className="text-center">
                         <img src={scanResult.member.photoUrl} className="w-24 h-24 rounded-full border-4 border-white shadow-xl mx-auto mb-4 object-cover" />
                         <h3 className="text-2xl font-black text-slate-900">{scanResult.member.name}</h3>
                         <p className="text-xs font-black text-brand-600 uppercase tracking-widest">{scanResult.member.id} • {scanResult.member.role}</p>
                         <p className="mt-4 p-3 bg-white rounded-2xl text-xs font-bold text-slate-500 border border-slate-100">{scanResult.message}</p>
                       </div>
                    ) : scanResult.visitor ? (
                       <div className="text-center">
                         <div className="w-20 h-20 bg-brand-100 text-brand-600 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-inner">
                            <UserPlus size={40} />
                         </div>
                         <h3 className="text-2xl font-black text-slate-900">{scanResult.visitor.name}</h3>
                         <p className="text-xs font-black text-brand-600 uppercase tracking-widest">VISITOR • {scanResult.visitor.id}</p>
                         <div className="mt-4 grid grid-cols-2 gap-2">
                            <div className="bg-white p-3 rounded-xl border border-slate-100">
                               <p className="text-[8px] font-black text-slate-400">HOST</p>
                               <p className="text-[10px] font-black">{scanResult.visitor.hostName}</p>
                            </div>
                            <div className="bg-white p-3 rounded-xl border border-slate-100">
                               <p className="text-[8px] font-black text-slate-400">STATUS</p>
                               <p className="text-[10px] font-black text-green-600">VALID</p>
                            </div>
                         </div>
                         <button onClick={async () => {
                           await api.checkoutVisitor(scanResult.visitor!.id);
                           setScanResult(null);
                           alert("Visitor Checked Out Successfully");
                         }} className="mt-6 w-full p-4 bg-red-50 text-red-600 rounded-2xl font-black text-xs hover:bg-red-100 flex items-center justify-center gap-2">
                            <LogOut size={16}/> CHECK-OUT VISITOR
                         </button>
                       </div>
                    ) : null}
                  </>
                ) : (
                  <div className="text-center py-6">
                    <ShieldAlert size={64} className="text-red-500 mx-auto mb-4" />
                    <p className="text-red-600 font-black text-xl uppercase tracking-tighter">{scanResult.message}</p>
                  </div>
                )}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
