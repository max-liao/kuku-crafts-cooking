import React from "react";
import ReactDOM, { createRoot } from "react-dom/client";
import App from "./App";
import SheetDataTable from "./components/SheetDataTable";
import "./index.css";

// TODO: Move this to a separate file

const rootElement = document.getElementById("kuku-react-root");
if (rootElement) {
  const root = ReactDOM.createRoot(rootElement);
  root.render(
    <React.StrictMode>
      <App />
    </React.StrictMode>
  );
}

document.querySelectorAll('[data-component="SheetDataTable"]').forEach((el) => {
  console.log("🎯 Found SheetDataTable mount", el);

  const endpoint =
    el.getAttribute("data-sheet-endpoint") ??
    "/wp-json/google_sheets_plugin/v1/get-sheet/";
  const root = createRoot(el);
  root.render(
    <React.StrictMode>
      <SheetDataTable endpoint={endpoint} />
    </React.StrictMode>
  );
});

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
// reportWebVitals(console.log);
