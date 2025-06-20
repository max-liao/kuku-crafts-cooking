import { useEffect, useState } from "react";

function PineConeOfTheDay() {
  // Store the pine cone image URL from the API
  const [pineconeUrl, setPineconeUrl] = useState("");

  // Track if the API call failed
  const [error, setError] = useState(false);

  useEffect(() => {
    // Define the custom WP REST API endpoint exposed by my plugin
    const apiUrl = `${window.location.origin}/wp-json/kuku/v1/pinecone`;

    // Fetch the daily pine cone image URL
    fetch(apiUrl)
      .then((res) => {
        if (!res.ok) throw new Error("API response error");
        return res.json();
      })
      .then((data) => {
        console.log("🌲 Pinecone URL:", data);
        setPineconeUrl(data); // Update state with the fetched image
      })
      .catch((err) => {
        console.error("❌ Failed to fetch pinecone:", err);
        setError(true); // Set error state for graceful fallback
      });
  }, []); // Only run once on initial mount

  // Error state UI
  if (error) return <p>Could not load today's pine cone 🌧️</p>;

  // Loading state UI
  if (!pineconeUrl) return <p>Loading Pine Cone of the Day...</p>;

  // Main UI once data is loaded
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
