import { GoogleGenAI } from "@google/genai";
import { Member, Transaction, AccessLog } from "../types";

// NOTE: In a production build, process.env.API_KEY is injected by the bundler.
const apiKey = process.env.API_KEY || ""; 

const ai = new GoogleGenAI({ apiKey });

/**
 * Creates a chat session for the AI Concierge
 * Uses Gemini 2.5 Flash with 0 thinking budget for efficient free-tier usage.
 */
export const createAssistantSession = (
  member: Member,
  transactions: Transaction[]
) => {
  const systemInstruction = `You are the Vanguard OnePass AI Concierge.
  CURRENT CONTEXT:
  Name: ${member.name} (ID: ${member.id})
  Balance: ₦${member.walletBalance}
  Fines: ₦${member.outstandingFines}
  
  Be helpful, concise, and professional.`;

  return ai.chats.create({
    model: 'gemini-2.5-flash',
    config: { 
      systemInstruction,
      thinkingConfig: { thinkingBudget: 0 } // Disable thinking to save tokens/quota
    },
  });
};

/**
 * Generates a proactive "Daily Briefing" for the member dashboard.
 */
export const generateMemberInsights = async (member: Member, transactions: Transaction[]): Promise<string> => {
  try {
    const prompt = `
      Analyze this member's status and recent transactions (last 3).
      Member: ${member.name}, Wallet: ${member.walletBalance}, Fines: ${member.outstandingFines}.
      Transactions: ${JSON.stringify(transactions.slice(0, 3))}
      
      Generate a 1-sentence proactive insight. 
      Examples:
      - "Your balance is low for tomorrow's lunch, consider a top-up."
      - "Great job! You have no outstanding fines."
      - "You received a reward recently, keep it up!"
      - "Reminder: You have a fine of ${member.outstandingFines} pending."
      
      Output ONLY the sentence.
    `;

    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash',
      contents: prompt,
      config: {
        thinkingConfig: { thinkingBudget: 0 }
      }
    });

    return response.text || "Welcome back to Vanguard OnePass.";
  } catch (e) {
    return "Welcome back to Vanguard OnePass.";
  }
};

/**
 * Admin Analyst: Answers natural language queries about system data.
 */
export const queryAdminAnalyst = async (
  query: string, 
  members: Member[], 
  stats: any
): Promise<string> => {
  try {
    // We send a summarized version of data to fit context window
    const dataContext = {
      totalMembers: members.length,
      totalFines: stats.totalFines,
      membersSample: members.map(m => ({ 
        id: m.id, 
        name: m.name, 
        role: m.role, 
        balance: m.walletBalance, 
        fines: m.outstandingFines,
        status: m.status
      }))
    };

    const prompt = `
      You are the Vanguard System Admin AI Analyst.
      User Query: "${query}"
      
      System Data:
      ${JSON.stringify(dataContext, null, 2)}
      
      Instructions:
      1. Answer the query based strictly on the provided data.
      2. If asking for a list, provide it.
      3. If asking for specific stats (e.g., who has fines > X), calculate it from the list.
      4. Be concise and authoritative.
    `;

    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash',
      contents: prompt,
      config: {
        thinkingConfig: { thinkingBudget: 0 }
      }
    });

    return response.text || "I could not process that query.";
  } catch (e) {
    console.error(e);
    return "Analyst unavailable.";
  }
};
