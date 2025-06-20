import React, { useEffect, useState } from "react";

// Define the expected row structure
type SheetRow = Record<string, string | number | null | undefined>;

// Props: Accepts a REST API endpoint to fetch data from
type Props = {
  endpoint: string;
};

const SheetDataTable: React.FC<Props> = ({ endpoint }) => {
  // State: rows holds the parsed sheet data
  const [rows, setRows] = useState<SheetRow[]>([]);
  const [loading, setLoading] = useState(true);

  // Fetch data on mount or when endpoint changes
  useEffect(() => {
    console.log("🧪 Fetching from endpoint:", endpoint);

    fetch(endpoint)
      .then((res) => res.json())
      .then((data: SheetRow[]) => {
        console.log("✅ Data loaded", data);
        setRows(data);
        setLoading(false);
      })
      .catch((err) => {
        console.error("❌ Failed to fetch:", err);
        setRows([]);
        setLoading(false);
      });
  }, [endpoint]);

  // Handle loading or empty state
  if (loading) return <p>Loading projections...</p>;
  if (!rows.length) return <p>No data found.</p>;

  // Extract column headers from the first row
  const headers = Object.keys(rows[0]);

  return (
    <div>
      <style>
        {`
        .sheet-table-wrapper {
          position: relative;
          width: 90vw;
          left: 50%;
          transform: translateX(-50%);
          overflow-x: auto;
          padding: 1rem 0;
        }

        .styled-sheet-table {
          width: 100%;
          border-collapse: collapse;
          table-layout: auto;
        }

        .styled-sheet-table thead th {
          position: sticky;
          top: 0;
          background: white;
          z-index: 100;
        }

        .styled-sheet-table tbody tr:nth-child(odd) {
          background-color: #f7faff;
        }

        .styled-sheet-table th:first-child,
        .styled-sheet-table td:first-child {
          position: sticky;
          left: 0;
          background: white;
          z-index: 30;
        }
      `}
      </style>

      <div className="sheet-table-wrapper">
        <table className="styled-sheet-table" border={1} cellPadding={6}>
          <thead>
            <tr>
              {headers.map((header, i) => (
                <th key={header}>{header}</th>
              ))}
            </tr>
          </thead>
          <tbody>
            {rows.map((row, idx) => (
              <tr key={idx}>
                {headers.map((header, i) => {
                  let value = row[header] ?? "";
                  if (i === 0) {
                    // Prepend row number to first column (Player)
                    value = `${idx + 1}. ${value}`;
                  }
                  return <td key={header}>{value}</td>;
                })}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default SheetDataTable;
