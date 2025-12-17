import { GoogleGenAI } from "@google/genai";
import { Member, Transaction } from "../types";

// NOTE: In a production build, process.env.API_KEY is injected by the bundler.
// Ensure your build system (Vite/Webpack) defines this variable.
const apiKey = process.env.API_KEY || ""; 

const ai = new GoogleGenAI({ apiKey });

/**
 * Creates a chat session initialized with the member's specific context.
 * This allows the AI to answer questions like "Why was I fined?" or "What's my balance?"
 */
export const createAssistantSession = (
  member: Member,
  transactions: Transaction[]
) => {
  const systemInstruction = `You are the Vanguard OnePass AI Concierge.
  You are an intelligent assistant integrated into the Vanguard mobile app.
  
  YOUR GOAL:
  Assist the member with their account, explain club rules, and provide insights into their wallet and fines.
  
  CURRENT MEMBER CONTEXT:
  Name: ${member.name} (ID: ${member.id})
  Role: ${member.role}
  Status: ${member.status}
  Wallet Balance: ₦${member.walletBalance.toLocaleString()}
  Outstanding Fines: ₦${member.outstandingFines.toLocaleString()}
  Reward Points: ${member.rewardPoints}
  
  RECENT TRANSACTIONS (Last 5):
  ${JSON.stringify(transactions.slice(0, 5), null, 2)}
  
  RULES:
  1. Be concise, professional, and helpful. Keep answers short (mobile-friendly).
  2. If the user asks about their balance, tell them the exact amount from the context.
  3. If the user asks about fines, analyze the 'Recent Transactions' or 'Outstanding Fines' to explain.
  4. Access Control Rule: Resumption time is 08:30. Entry after this time incurs an automatic fine (usually ₦5,000).
  5. If the user wants to withdraw, instruct them to use the 'Request Withdrawal' button on the Member Dashboard.
  6. If the user is 'Suspended' or 'Blocked', advise them to contact Admin immediately.
  
  Tone: Friendly, efficient, authoritative but polite.`;

  return ai.chats.create({
    model: 'gemini-2.5-flash',
    config: {
      systemInstruction,
      temperature: 0.7,
    },
  });
};
