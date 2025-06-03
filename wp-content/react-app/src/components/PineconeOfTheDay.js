import { useEffect, useState } from "react";

function PineConeOfTheDay() {
  const [pineconeUrl, setPineconeUrl] = useState("");

  useEffect(() => {
    fetch("/wp-json/kuku/v1/pinecone")
      .then((res) => res.json())
      .then((data) => {
        console.log("Pinecone URL:", data);
        setPineconeUrl(data);
      });
  }, []);

  if (!pineconeUrl) return <p>Loading...</p>;

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
