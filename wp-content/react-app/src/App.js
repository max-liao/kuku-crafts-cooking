import { useEffect, useState } from 'react';
import PineConeOfTheDay from './components/PineconeOfTheDay.js';

function App() {
  const [posts, setPosts] = useState([]);

  useEffect(() => {
    fetch('http://kuku-crafts-cooking.local/wp-json/wp/v2/posts')
      .then((res) => {
        if (!res.ok) {
          throw new Error(`HTTP error! Status: ${res.status}`);
        }
        return res.json();
      })
      .then((data) => setPosts(data))
      .catch((err) => console.error('Error fetching posts:', err));
  }, []);

  return (
    <div style={{ padding: '2rem' }}>
      <h1>My Dog Blog 🐶</h1>
      {posts.length === 0 && <p>Loading...</p>}
      {posts.map((post) => (
        <div key={post.id} style={{ marginBottom: '2rem' }}>
          <h2 dangerouslySetInnerHTML={{ __html: post.title.rendered }} />
          <div dangerouslySetInnerHTML={{ __html: post.content.rendered }} />
        </div>
      ))}
      <PineConeOfTheDay />
    </div>
  );
}

export default App;
