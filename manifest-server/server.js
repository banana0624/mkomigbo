// project-root/server.js

const express = require('express');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = 3000;

// Serve manifest JSON
app.get('/manifest', (req, res) => {
  const manifestPath = path.join(__dirname, 'manifest.json');
  fs.readFile(manifestPath, 'utf8', (err, data) => {
    if (err) {
      return res.status(500).json({ error: 'Manifest not found' });
    }
    res.setHeader('Content-Type', 'application/json');
    res.send(data);
  });
});

app.listen(PORT, () => {
  console.log(`Manifest server running at http://localhost:${PORT}`);
});