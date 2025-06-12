import { useEffect, useState } from "react";

function PineConeOfTheDay() {
  const [pineconeUrl, setPineconeUrl] = useState("");
  const [error, setError] = useState(false);

  useEffect(() => {
    const apiUrl = `${window.location.origin}/wp-json/kuku/v1/pinecone`;

    fetch(apiUrl)
      .then((res) => {
        if (!res.ok) throw new Error("API response error");
        return res.json();
      })
      .then((data) => {
        console.log("🌲 Pinecone URL:", data);
        setPineconeUrl(data);
      })
      .catch((err) => {
        console.error("❌ Failed to fetch pinecone:", err);
        setError(true);
      });
  }, []);

  if (error) return <p>Could not load today's pine cone 🌧️</p>;
  if (!pineconeUrl) return <p>Loading Pine Cone of the Day...</p>;

  return (
    <div>
      <h2>Pine Cone of the Day 🌲</h2>
      <img
        src={pineconeUrl}
        alt="Pine Cone of the Day"
        style={{ width: "300px", borderRadius: "8px" }}
      />
      <p>Last updated: {new Date().toLocaleDateString()}</p>
    </div>
  );
}

export default PineConeOfTheDay;
