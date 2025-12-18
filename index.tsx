
import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App.tsx';

console.log("Vanguard OnePass: Initiating Boot Sequence...");

const rootElement = document.getElementById('root');

if (!rootElement) {
  console.error("Vanguard OnePass: Critical Error - Root element not found.");
} else {
  try {
    const root = ReactDOM.createRoot(rootElement);
    root.render(
      <React.StrictMode>
        <App />
      </React.StrictMode>
    );
    console.log("Vanguard OnePass: UI Mounted Successfully.");
  } catch (err) {
    console.error("Vanguard OnePass: Mount Failure", err);
    rootElement.innerHTML = `<div style="padding: 20px; color: red; font-family: sans-serif;"><h2>Boot Failure</h2><p>${err instanceof Error ? err.message : String(err)}</p></div>`;
  }
}
