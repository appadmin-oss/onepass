
import React, { useState, useEffect, useRef } from 'react';
import { Send, Bot, User, Sparkles, AlertCircle } from 'lucide-react';
import { Member, ChatMessage, Transaction } from '../types';
import { createAssistantSession } from '../services/ai';
import { api } from '../services/api';

interface AIAssistantProps {
  user: Member | null;
}

export default function AIAssistant({ user }: AIAssistantProps) {
  const [messages, setMessages] = useState<ChatMessage[]>([
    {
      id: 'welcome',
      role: 'model',
      text: `Greetings ${user ? user.name.split(' ')[0] : 'Vanguard'}! I am your OnePass Hub Intelligence. How can I assist your organizational journey today?`,
      timestamp: new Date()
    }
  ]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(false);
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const chatSessionRef = useRef<any>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (user) {
      api.getHistory(user.id).then(txs => {
        setTransactions(txs);
        chatSessionRef.current = createAssistantSession(user, txs);
      });
    }
  }, [user]);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  const handleSend = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!input.trim() || !user || !chatSessionRef.current) return;

    const userMsg: ChatMessage = {
      id: Date.now().toString(),
      role: 'user',
      text: input,
      timestamp: new Date()
    };

    setMessages(prev => [...prev, userMsg]);
    setInput('');
    setLoading(true);

    try {
      const response = await chatSessionRef.current.sendMessage({ message: userMsg.text });
      const modelMsg: ChatMessage = {
        id: (Date.now() + 1).toString(),
        role: 'model',
        text: response.text,
        timestamp: new Date()
      };
      setMessages(prev => [...prev, modelMsg]);
    } catch (error) {
      console.error("AI Link Failure:", error);
      setMessages(prev => [...prev, {
        id: 'err',
        role: 'model',
        text: "Cloud Intelligence is currently recalibrating. Please check your local portal or contact your administrator.",
        timestamp: new Date()
      }]);
    } finally {
      setLoading(false);
    }
  };

  if (!user) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[70vh] p-6 text-center animate-fade-in">
        <div className="bg-slate-100 p-8 rounded-[2.5rem] mb-6 shadow-inner text-slate-400">
          <Bot size={64} />
        </div>
        <h2 className="text-2xl font-black text-slate-800 tracking-tighter mb-2">Identify Required</h2>
        <p className="text-slate-500 max-w-xs font-medium">Please authenticate via the Member Portal to link with Hub Intelligence.</p>
      </div>
    );
  }

  return (
    <div className="flex flex-col h-[calc(100vh-140px)] md:h-[calc(100vh-120px)] bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden animate-fade-in">
      {/* Dynamic Header */}
      <div className="bg-slate-900 p-5 flex items-center justify-between shadow-lg relative overflow-hidden">
        <div className="absolute top-0 right-0 w-32 h-32 bg-brand-500/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
        <div className="flex items-center gap-4 relative z-10">
          <div className="bg-brand-600 p-2.5 rounded-2xl shadow-lg ring-4 ring-slate-800">
            <Sparkles className="text-white" size={24} />
          </div>
          <div>
            <h2 className="text-white font-black tracking-tight text-lg leading-none mb-1">Hub Intelligence</h2>
            <div className="flex items-center gap-2">
               <span className="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
               <p className="text-brand-400 text-[10px] font-black uppercase tracking-widest">Gemini 3 Flash Active</p>
            </div>
          </div>
        </div>
      </div>

      {/* Stream Area */}
      <div className="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50/50">
        {messages.map((msg) => (
          <div key={msg.id} className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}>
            <div className={`flex max-w-[85%] ${msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'}`}>
              <div className={`flex-shrink-0 h-10 w-10 rounded-2xl flex items-center justify-center mx-3 shadow-sm ${msg.role === 'user' ? 'bg-white text-brand-600' : 'bg-slate-900 text-white'}`}>
                {msg.role === 'user' ? <User size={20} /> : <Bot size={20} />}
              </div>
              <div
                className={`p-4 rounded-[1.5rem] text-sm font-medium shadow-sm leading-relaxed ${
                  msg.role === 'user'
                    ? 'bg-brand-600 text-white rounded-tr-none'
                    : 'bg-white text-slate-800 border border-slate-100 rounded-tl-none'
                }`}
              >
                {msg.text}
                <div className={`text-[9px] mt-2 font-black uppercase tracking-widest ${msg.role === 'user' ? 'text-brand-200' : 'text-slate-400'}`}>
                  {msg.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                </div>
              </div>
            </div>
          </div>
        ))}
        {loading && (
          <div className="flex justify-start animate-pulse">
             <div className="flex items-center gap-3">
               <div className="h-10 w-10 rounded-2xl bg-slate-200"></div>
               <div className="bg-white p-5 rounded-[1.5rem] rounded-tl-none border border-slate-100 w-24 flex gap-1.5">
                  <div className="w-1.5 h-1.5 bg-slate-300 rounded-full animate-bounce"></div>
                  <div className="w-1.5 h-1.5 bg-slate-300 rounded-full animate-bounce delay-75"></div>
                  <div className="w-1.5 h-1.5 bg-slate-300 rounded-full animate-bounce delay-150"></div>
               </div>
             </div>
          </div>
        )}
        <div ref={messagesEndRef} />
      </div>

      {/* Control Area */}
      <form onSubmit={handleSend} className="p-5 bg-white border-t border-slate-100">
        <div className="relative flex items-center group">
          <input
            type="text"
            value={input}
            onChange={(e) => setInput(e.target.value)}
            placeholder="Ask about fines, wallets, or Hub protocols..."
            className="w-full pl-6 pr-14 py-4 bg-slate-50 border-2 border-slate-100 rounded-full focus:ring-4 focus:ring-brand-100 focus:border-brand-500 focus:bg-white outline-none transition-all font-semibold text-slate-700"
          />
          <button
            type="submit"
            disabled={!input.trim() || loading}
            className="absolute right-2 p-3 bg-brand-600 text-white rounded-full hover:bg-slate-900 disabled:opacity-30 transition-all shadow-lg active:scale-90"
          >
            <Send size={20} />
          </button>
        </div>
        <div className="mt-3 flex items-center justify-center gap-2 opacity-30">
           <AlertCircle size={10}/>
           <span className="text-[8px] font-black uppercase tracking-widest">Organizational Data Privacy Active</span>
        </div>
      </form>
    </div>
  );
}
