/** project-root/public/subjects/scripts.js */

fetch('summary.json')
  .then(res => res.json())
  .then(data => {
    const el = document.getElementById('summary');
    el.innerHTML = `
      <p><strong>Total backups:</strong> ${data.total}</p>
      <p><strong>Latest backup:</strong> ${data.latest}</p>
      <p><strong>Size estimate:</strong> ${data.sizeEstimate}</p>
    `;
  });