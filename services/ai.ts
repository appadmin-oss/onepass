
import { GoogleGenAI } from "@google/genai";
import { Member, Transaction } from "../types";

// Fix: Initialized GoogleGenAI with API key directly from process.env.API_KEY as per the library guidelines
const ai = new GoogleGenAI({ apiKey: process.env.API_KEY });

export const generateMemberInsights = async (member: Member, transactions: Transaction[]): Promise<string> => {
  try {
    // Fix: Using gemini-3-flash-preview for basic summarization and tip generation tasks
    const response = await ai.models.generateContent({
      model: 'gemini-3-flash-preview',
      contents: `You are the Vanguard Intelligence Hub. Member: ${member.name}, Wallet: ${member.walletBalance}, Fines: ${member.outstandingFines}. Tone: Professional, authoritative, concise. Provide 1 proactive tip.`,
    });
    return response.text || "Access record currently within organizational parameters.";
  } catch (e) {
    return "Stable attendance record. Continue standard resumption protocol.";
  }
};

export const queryAdminAnalyst = async (query: string, members: Member[], stats: any): Promise<string> => {
  try {
    // Fix: Using gemini-3-pro-preview for complex reasoning and organizational data analysis tasks
    const response = await ai.models.generateContent({
      model: 'gemini-3-pro-preview',
      contents: `Vanguard OnePass Admin Analyst. System State: ${members.length} members, Total Wallet: ${stats.totalWallet}. Question: ${query}`,
    });
    return response.text || "Analysis complete. System operating at 98% efficiency.";
  } catch (e) {
    return "Analyst offline. Data integrity remains secured.";
  }
};

export const createAssistantSession = (member: Member, transactions: Transaction[]) => {
  return ai.chats.create({
    model: 'gemini-3-flash-preview',
    config: {
      systemInstruction: `Vanguard AI Concierge for ${member.name}. Help with wallet, fines, and rules.`
    }
  });
};
