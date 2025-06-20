import React, { useEffect, useState } from "react";

type SheetRow = Record<string, string | number | null | undefined>;

type Props = {
  endpoint: string;
};

const SheetDataTable: React.FC<Props> = ({ endpoint }) => {
  const [rows, setRows] = useState<SheetRow[]>([]);
  const [loading, setLoading] = useState(true);

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

  if (loading) return <p>Loading projections...</p>;
  if (!rows.length) return <p>No data found.</p>;

  const headers = Object.keys(rows[0]);

  return (
    <div style={{ overflowX: "auto" }}>
      <table
        style={{ borderCollapse: "collapse", width: "100%" }}
        border={1}
        cellPadding={6}
      >
        <thead>
          <tr>
            <th>#</th>
            {headers.map((header) => (
              <th key={header}>{header}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row, idx) => (
            <tr key={idx}>
              <td>{idx + 1}</td>
              {headers.map((header) => (
                <td key={header}>{row[header] ?? ""}</td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default SheetDataTable;
