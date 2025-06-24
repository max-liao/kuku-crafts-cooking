import { useEffect, useState } from "react";

/**
 * A React component that fetches a random pine cone image from a WordPress REST API
 * and displays it in the page.
 *
 * @returns {JSX.Element} The component to be rendered
 */
function PineConeOfTheDay() {
  // Store the pine cone image URL from the API
  const [pineconeUrl, setPineconeUrl] = useState("");
  // Track if the API call failed
  const [error, setError] = useState(false);

  useEffect(() => {
    // Define the custom WP REST API endpoint exposed by my plugin
    const apiUrl = `${window.location.origin}/wp-json/kuku/v1/pinecone`;

    // Fetch the pine cone image URL from the WordPress REST API
    fetch(apiUrl)
      .then((res) => {
        // If the response is not OK, throw an error
        if (!res.ok) throw new Error("API response error");
        // Otherwise, parse the JSON response
        return res.json();
      })
      .then((data) => {
        // Log the result and update the state
        console.log("🌲 Pinecone URL:", data);
        setPineconeUrl(data); // Update state with the fetched image
      })
      .catch((err) => {
        // Log the error and update the state
        console.error("Failed to fetch pinecone:", err);
        setError(true); // Set error state for graceful fallback
      });
  }, []); // Only run once on initial mount

  return (
    <div>
      <h2>Pine Cone of the Day</h2>

      {/* If image URL is present, attempt to show image */}
      {pineconeUrl && !error && (
        <img
          src={pineconeUrl}
          alt="Pine Cone of the Day"
          style={{ width: "300px", borderRadius: "8px" }}
          // If the image fails to load, set error to true
          onError={() => setError(true)}
        />
      )}

      {/* If there was an error, show fallback message + image link */}
      {error && pineconeUrl && (
        <p>
          Could not load today's pine cone{" "}
          <a href={pineconeUrl} target="_blank" rel="noopener noreferrer">
            View image directly
          </a>
        </p>
      )}

      {/* If there is no URL and no error, show loading message */}
      {!pineconeUrl && !error && <p>Loading Pine Cone of the Day...</p>}

      <p>Last updated: {new Date().toLocaleDateString()}</p>
    </div>
  );
}

export default PineConeOfTheDay;
